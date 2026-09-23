<?php

namespace Tests\Feature;

use App\Models\Personagem;
use Database\Seeders\PersonagemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class PersonagemPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_photo_is_stored_returned_replaced_and_deleted(): void
    {
        $response = $this->post('/api/personagens', [
            'nome' => 'Teste', 'descricao' => 'Teste',
            'imagem' => UploadedFile::fake()->image('foto.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated();
        $id = $response->json('id');
        $old = $response->json('imagem');
        Storage::disk('public')->assertExists($old);
        $response->assertJsonPath('imagem_url', Storage::disk('public')->url($old));
        $this->getJson("/api/personagens/$id")->assertOk()
            ->assertJsonPath('imagem_url', Storage::disk('public')->url($old));
        $this->getJson('/api/personagens')->assertOk()
            ->assertJsonPath('0.imagem', $old);

        $this->patchJson("/api/personagens/$id", ['idade' => 20])
            ->assertOk()->assertJsonPath('imagem', $old);
        Storage::disk('public')->assertExists($old);

        // PHP multipart uploads use POST with Laravel method spoofing.
        $response = $this->post("/api/personagens/$id", [
            '_method' => 'PUT', 'imagem' => UploadedFile::fake()->image('nova.png'),
        ], ['Accept' => 'application/json'])->assertOk();
        $new = $response->json('imagem');
        $this->assertNotSame($old, $new);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($new);

        $this->deleteJson("/api/personagens/$id")->assertOk();
        Storage::disk('public')->assertMissing($new);
        $this->assertDatabaseMissing('personagens', ['id' => $id]);
    }

    public function test_null_removes_photo_and_characters_can_have_no_photo(): void
    {
        $response = $this->postJson('/api/personagens', ['nome' => 'Teste', 'descricao' => 'Teste'])
            ->assertCreated()->assertJsonPath('imagem', null)->assertJsonPath('imagem_url', null);
        $id = $response->json('id');
        $path = UploadedFile::fake()->image('foto.jpg')->store('personagens', 'public');
        Personagem::findOrFail($id)->update(['imagem' => $path]);

        $this->patchJson("/api/personagens/$id", ['imagem' => null])
            ->assertOk()->assertJsonPath('imagem', null)->assertJsonPath('imagem_url', null);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_invalid_uploads_do_not_change_existing_photo(): void
    {
        $path = UploadedFile::fake()->image('foto.jpg')->store('personagens', 'public');
        $personagem = Personagem::create(['nome' => 'Teste', 'descricao' => 'Teste', 'imagem' => $path]);

        foreach ([
            UploadedFile::fake()->create('documento.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->createWithContent('falsa.jpg', 'Isto não é uma imagem.'),
            UploadedFile::fake()->image('foto.gif'),
            UploadedFile::fake()->image('grande.jpg')->size(5121),
            'personagens/arquivo.jpg',
        ] as $invalid) {
            $this->post('/api/personagens', [
                'nome' => 'Outro', 'descricao' => 'Teste', 'imagem' => $invalid,
            ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('imagem');
            $this->post("/api/personagens/{$personagem->id}", [
                '_method' => 'PATCH', 'imagem' => $invalid,
            ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('imagem');
            $this->assertSame($path, $personagem->fresh()->imagem);
            Storage::disk('public')->assertExists($path);
        }

        $this->assertCount(1, Storage::disk('public')->allFiles('personagens'));
        $this->assertDatabaseCount('personagens', 1);
    }

    public function test_failed_database_save_cleans_up_new_upload(): void
    {
        Personagem::creating(function () {
            throw new RuntimeException('Falha simulada ao salvar.');
        });

        try {
            $this->post('/api/personagens', [
                'nome' => 'Teste', 'descricao' => 'Teste',
                'imagem' => UploadedFile::fake()->image('foto.jpg'),
            ], ['Accept' => 'application/json'])->assertStatus(500);
            $this->assertSame([], Storage::disk('public')->allFiles('personagens'));
            $this->assertDatabaseCount('personagens', 0);
        } finally {
            Personagem::flushEventListeners();
        }
    }

    public function test_failed_update_keeps_old_photo_and_removes_new_upload(): void
    {
        $path = UploadedFile::fake()->image('foto.jpg')->store('personagens', 'public');
        $personagem = Personagem::create(['nome' => 'Teste', 'descricao' => 'Teste', 'imagem' => $path]);
        Personagem::updating(function () {
            throw new RuntimeException('Falha simulada ao atualizar.');
        });

        try {
            $this->post("/api/personagens/{$personagem->id}", [
                '_method' => 'PUT', 'imagem' => UploadedFile::fake()->image('nova.png'),
            ], ['Accept' => 'application/json'])->assertStatus(500);
            $this->assertSame($path, $personagem->fresh()->imagem);
            Storage::disk('public')->assertExists($path);
            $this->assertCount(1, Storage::disk('public')->allFiles('personagens'));
        } finally {
            Personagem::flushEventListeners();
        }
    }

    public function test_seeder_and_legacy_migration_preserve_uploaded_photos(): void
    {
        $this->seed(PersonagemSeeder::class);
        $personagem = Personagem::firstOrFail();
        $path = UploadedFile::fake()->image('foto.jpg')->store('personagens', 'public');
        $personagem->update(['imagem' => $path]);
        $legacy = Personagem::where('id', '!=', $personagem->id)->firstOrFail();
        $legacy->update(['imagem' => 'Annabeth Chase']);

        $migration = require database_path('migrations/2026_09_22_000002_clear_legacy_personagem_image_labels.php');
        $migration->up();
        $this->assertNull($legacy->fresh()->imagem);
        $this->seed(PersonagemSeeder::class);

        $this->assertDatabaseCount('personagens', 60);
        $this->assertSame($path, $personagem->fresh()->imagem);
        Storage::disk('public')->assertExists($path);
    }
}
