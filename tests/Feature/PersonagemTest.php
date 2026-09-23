<?php

namespace Tests\Feature;

use App\Models\Personagem;
use Database\Seeders\PersonagemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PersonagemTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_with_integer_age_and_date(): void
    {
        $response = $this->postJson('/api/personagens', [
            'nome' => 'Personagem de teste',
            'descricao' => 'Dados fictícios exclusivos do teste.',
            'idade' => 20,
            'data_nascimento' => '2000-02-29',
        ])->assertCreated()->assertJsonPath('idade', 20)
            ->assertJsonPath('data_nascimento', '2000-02-29');
        $id = $response->json('id');

        $this->getJson('/api/personagens?nome=Personagem')->assertOk()
            ->assertJsonCount(1)->assertJsonPath('0.idade', 20);
        $this->getJson("/api/personagens/$id")->assertOk()
            ->assertJsonPath('data_nascimento', '2000-02-29');
        $this->patchJson("/api/personagens/$id", ['descricao' => 'Atualizada'])
            ->assertOk()->assertJsonPath('idade', 20)
            ->assertJsonPath('data_nascimento', '2000-02-29');
        $this->putJson("/api/personagens/$id", ['idade' => 21, 'data_nascimento' => '2001-01-01'])
            ->assertOk()->assertJsonPath('idade', 21)
            ->assertJsonPath('data_nascimento', '2001-01-01');
        $this->patchJson("/api/personagens/$id", ['idade' => null, 'data_nascimento' => null])
            ->assertOk()->assertJsonPath('idade', null)->assertJsonPath('data_nascimento', null);
        $this->deleteJson("/api/personagens/$id")->assertOk();
        $this->getJson("/api/personagens/$id")->assertNotFound();
        $this->assertDatabaseMissing('personagens', ['id' => $id]);
    }

    public function test_unknown_age_and_birth_date_can_be_omitted(): void
    {
        $response = $this->postJson('/api/personagens', ['nome' => 'Teste', 'descricao' => 'Teste'])
            ->assertCreated();
        $this->getJson('/api/personagens/'.$response->json('id'))->assertOk()
            ->assertJsonPath('idade', null)->assertJsonPath('data_nascimento', null);
    }

    public function test_invalid_values_are_rejected_on_create_and_update(): void
    {
        $personagem = Personagem::create(['nome' => 'Teste', 'descricao' => 'Teste']);

        foreach ([
            ['idade' => 'Imortal'],
            ['idade' => -1],
            ['idade' => 12.5],
            ['idade' => 2147483648],
            ['data_nascimento' => '18 de agosto'],
            ['data_nascimento' => '2001-02-29'],
            ['data_nascimento' => '2000-01-01T12:00:00Z'],
        ] as $invalid) {
            $this->postJson('/api/personagens', array_merge([
                'nome' => 'Outro', 'descricao' => 'Teste',
            ], $invalid))->assertUnprocessable()->assertJsonValidationErrors(array_keys($invalid));
            $this->patchJson("/api/personagens/{$personagem->id}", $invalid)
                ->assertUnprocessable()->assertJsonValidationErrors(array_keys($invalid));
        }
    }

    public function test_seeder_preserves_sixty_characters_without_inventing_values(): void
    {
        $this->seed(PersonagemSeeder::class);
        $ids = Personagem::orderBy('id')->pluck('id', 'nome')->all();
        $this->seed(PersonagemSeeder::class);

        $this->assertDatabaseCount('personagens', 60);
        $this->assertSame($ids, Personagem::orderBy('id')->pluck('id', 'nome')->all());
        $this->assertSame(60, Personagem::whereNull('data_nascimento')->count());
        $this->assertSame(59, Personagem::whereNull('idade')->count());
        $this->assertSame(12, Personagem::where('nome', 'Perseus "Percy" Jackson')->firstOrFail()->idade);
        $this->getJson('/api/personagens')->assertOk()->assertJsonCount(60);
    }

    public function test_migration_converts_legacy_values_without_removing_characters(): void
    {
        $migration = require database_path('migrations/2026_09_22_000001_update_personagens_age_and_birth_date.php');
        $migration->down();

        foreach ([
            ['16 anos', '2000-02-29'],
            ['Imortal', 'Antiguidade'],
            ['Aproximadamente 12 anos', '18 de agosto'],
            ['0', '2001-02-29'],
            [null, null],
            ['12 anos no início da série e 16 anos em O Último Olimpiano', null],
        ] as $index => [$idade, $data]) {
            DB::table('personagens')->insert([
                'id' => $index + 1, 'nome' => "Teste $index", 'descricao' => 'Preservada',
                'idade' => $idade, 'data_nascimento' => $data,
            ]);
        }

        $before = DB::table('personagens')->orderBy('id')->get(['id', 'nome', 'descricao'])->toJson();
        $migration->up();
        $this->assertSame($before, DB::table('personagens')->orderBy('id')->get(['id', 'nome', 'descricao'])->toJson());
        $this->assertSame([16, null, null, 0, null, 12], Personagem::orderBy('id')->get()->pluck('idade')->all());
        $this->assertSame('2000-02-29', Personagem::findOrFail(1)->toArray()['data_nascimento']);
        $this->assertSame(5, Personagem::whereNull('data_nascimento')->count());
    }
}
