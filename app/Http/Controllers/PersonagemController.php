<?php

namespace App\Http\Controllers;

use App\Models\Personagem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $personagem = Personagem::create($dados);

        return response()->json($personagem, 201);
    }

    public function update(Request $request, Personagem $personagem): JsonResponse
    {
        $dados = $request->validate($this->rules($personagem->id, true));
        $personagem->update($dados);

        return response()->json($personagem->fresh());
    }

    public function destroy(Personagem $personagem): JsonResponse
    {
        $personagem->delete();

        return response()->json([
            'mensagem' => 'Personagem removido com sucesso.',
        ]);
    }

    private function rules(?int $id = null, bool $sometimes = false): array
    {
        $prefix = $sometimes ? 'sometimes|' : '';

        return [
            'nome' => [$sometimes ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('personagens', 'nome')->ignore($id)],
            'descricao' => [$sometimes ? 'sometimes' : 'required', 'string'],
            'data_nascimento' => [$prefix . 'nullable', 'string', 'max:255'],
            'idade' => [$prefix . 'nullable', 'string', 'max:255'],
            'poderes' => [$prefix . 'nullable', 'string'],
            'raca' => [$prefix . 'nullable', 'string', 'max:255'],
            'parentesco_divino' => [$prefix . 'nullable', 'string', 'max:255'],
            'imagem' => [$prefix . 'nullable', 'string', 'max:2048'],
        ];
    }
}
