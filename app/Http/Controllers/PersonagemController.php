<?php

namespace App\Http\Controllers;

use App\Models\Personagem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class PersonagemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Personagem::query();

        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->string('nome') . '%');
        }

        if ($request->filled('raca')) {
            $query->where('raca', 'like', '%' . $request->string('raca') . '%');
        }

        if ($request->filled('parentesco_divino')) {
            $query->where('parentesco_divino', 'like', '%' . $request->string('parentesco_divino') . '%');
        }

        return response()->json($query->orderBy('id')->get());
    }

    public function show(Personagem $personagem): JsonResponse
    {
        return response()->json($personagem);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate($this->rules());
        $caminho = $request->hasFile('imagem') ? $this->storePhoto($request->file('imagem')) : null;
        $dados['imagem'] = $caminho;

        try {
            $personagem = Personagem::create($dados);
        } catch (Throwable $exception) {
            $this->deletePhoto($caminho);
            throw $exception;
        }

        return response()->json($personagem, 201);
    }

    public function update(Request $request, Personagem $personagem): JsonResponse
    {
        $dados = $request->validate($this->rules($personagem->id, true));
        $antiga = $personagem->imagem;
        $caminho = null;

        if ($request->hasFile('imagem')) {
            $caminho = $this->storePhoto($request->file('imagem'));
            $dados['imagem'] = $caminho;
        }

        try {
            $personagem->update($dados);
        } catch (Throwable $exception) {
            $this->deletePhoto($caminho);
            throw $exception;
        }

        if (array_key_exists('imagem', $dados) && $antiga !== $personagem->imagem) {
            $this->deletePhoto($antiga);
        }

        return response()->json($personagem->fresh());
    }

    public function destroy(Personagem $personagem): JsonResponse
    {
        $caminho = $personagem->imagem;
        $personagem->delete();
        $this->deletePhoto($caminho);

        return response()->json([
            'mensagem' => 'Personagem removido com sucesso.',
        ]);
    }

    private function storePhoto(UploadedFile $photo): string
    {
        $path = $photo->store('personagens', 'public');

        if ($path === false) {
            throw new RuntimeException('Não foi possível armazenar a foto.');
        }

        return $path;
    }

    private function deletePhoto(?string $path): void
    {
        // Only delete files owned by this upload flow, never legacy labels or arbitrary paths.
        if ($path !== null && preg_match('~^personagens/[A-Za-z0-9]+\.(jpg|jpeg|png|webp)$~D', $path)) {
            if (! Storage::disk('public')->delete($path)) {
                throw new RuntimeException('Não foi possível excluir a foto.');
            }
        }
    }

    private function rules(?int $id = null, bool $sometimes = false): array
    {
        $prefix = $sometimes ? 'sometimes|' : '';

        return [
            'nome' => [$sometimes ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('personagens', 'nome')->ignore($id)],
            'descricao' => [$sometimes ? 'sometimes' : 'required', 'string'],
            'data_nascimento' => [$prefix . 'nullable', 'date_format:Y-m-d'],
            'idade' => [$prefix . 'nullable', 'integer', 'min:0', 'max:2147483647'],
            'poderes' => [$prefix . 'nullable', 'string'],
            'raca' => [$prefix . 'nullable', 'string', 'max:255'],
            'parentesco_divino' => [$prefix . 'nullable', 'string', 'max:255'],
            'imagem' => [$prefix . 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
