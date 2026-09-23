<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personagem extends Model
{
    use HasFactory;

    protected $table = 'personagens';

    protected function casts(): array
    {
        return [
            'idade' => 'integer',
            'data_nascimento' => 'date:Y-m-d',
        ];
    }

    protected $fillable = [
        'nome',
        'descricao',
        'data_nascimento',
        'idade',
        'poderes',
        'raca',
        'parentesco_divino',
        'imagem',
    ];
}
