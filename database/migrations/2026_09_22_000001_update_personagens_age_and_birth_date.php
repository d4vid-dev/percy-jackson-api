<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize legacy text before changing types, preserving IDs and other fields.
        DB::table('personagens')->orderBy('id')->chunkById(100, function ($personagens) {
            foreach ($personagens as $personagem) {
                $idade = $personagem->idade;

                if ($idade === '12 anos no início da série e 16 anos em O Último Olimpiano') {
                    $idade = 12;
                } elseif (is_string($idade) && preg_match('/^(\d+) anos$/D', $idade, $matches)) {
                    $idade = $matches[1];
                }

                $idade = filter_var($idade, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 0, 'max_range' => 2147483647],
                ]);
                $data = $personagem->data_nascimento;
                $date = is_string($data) ? DateTimeImmutable::createFromFormat('!Y-m-d', $data) : false;

                DB::table('personagens')->where('id', $personagem->id)->update([
                    'idade' => $idade === false ? null : $idade,
                    'data_nascimento' => $date && $date->format('Y-m-d') === $data ? $data : null,
                ]);
            }
        });

        Schema::table('personagens', function (Blueprint $table) {
            $table->integer('idade')->nullable()->change();
            $table->date('data_nascimento')->nullable()->change();
        });
    }

    public function down(): void
    {
        // The original descriptive text cannot be reconstructed from integer/date values.
        Schema::table('personagens', function (Blueprint $table) {
            $table->string('idade')->nullable()->change();
            $table->string('data_nascimento')->nullable()->change();
        });
    }
};
