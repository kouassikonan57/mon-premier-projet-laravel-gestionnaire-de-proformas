<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->decimal('montant_paye', 10, 2)->default(0)->after('montant_a_payer');
            $table->decimal('reste_a_payer', 10, 2)->default(0)->after('montant_paye');
        });
    }

    public function down()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['montant_paye', 'reste_a_payer']);
        });
    }
};