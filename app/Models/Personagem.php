<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personagem extends Model
{
    use HasFactory;

    protected $table = 'personagens';

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
