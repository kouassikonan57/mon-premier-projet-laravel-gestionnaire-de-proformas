<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajout de la colonne 'role' s'il elle n'existe pas
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user'); // admin ou user
            }

            // Ajout de la colonne 'filiale_id' avec clé étrangère
            if (!Schema::hasColumn('users', 'filiale_id')) {
                $table->foreignId('filiale_id')->nullable()->constrained();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'filiale_id')) {
                $table->dropForeign(['filiale_id']);
                $table->dropColumn('filiale_id');
            }

            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
