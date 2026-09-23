<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Personagem extends Model
{
    use HasFactory;

    protected $table = 'personagens';

    protected $appends = ['imagem_url'];

    public function getImagemUrlAttribute(): ?string
    {
        return $this->imagem ? Storage::disk('public')->url($this->imagem) : null;
    }

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
