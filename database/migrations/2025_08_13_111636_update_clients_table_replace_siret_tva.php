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
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['siret', 'tva_number']); // supprime les champs français
            $table->string('rccm')->nullable(); // ajoute RCCM
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('siret')->nullable();
            $table->string('tva_number')->nullable();
            $table->dropColumn('rccm');
        });
    }
};
