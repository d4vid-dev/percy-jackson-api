<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Old values were character names, not uploaded files.
        DB::table('personagens')->whereNotNull('imagem')
            ->where('imagem', 'not like', '%/%')
            ->update(['imagem' => null]);
    }

    public function down(): void
    {
        // Labels cannot be recovered; uploaded paths must remain intact.
    }
};
