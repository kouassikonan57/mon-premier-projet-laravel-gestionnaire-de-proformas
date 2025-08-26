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
        Schema::table('proformas', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    
    public function down()
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
