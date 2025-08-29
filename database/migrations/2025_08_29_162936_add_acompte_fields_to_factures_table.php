<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->string('bon_commande')->nullable()->after('description');
            $table->decimal('acompte_pourcentage', 5, 2)->default(0)->after('remise');
            $table->decimal('acompte_montant', 15, 2)->default(0)->after('acompte_pourcentage');
            $table->decimal('montant_a_payer', 15, 2)->default(0)->after('acompte_montant');
        });
    }

    public function down()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['bon_commande', 'acompte_pourcentage', 'acompte_montant', 'montant_a_payer']);
        });
    }
};