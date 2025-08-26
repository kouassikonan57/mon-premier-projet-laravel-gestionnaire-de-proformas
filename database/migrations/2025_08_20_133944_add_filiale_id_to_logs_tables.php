<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilialeIdToLogsTables extends Migration
{
    public function up()
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('filiale_id')->nullable()->constrained()->onDelete('set null');
        });

        Schema::table('action_logs', function (Blueprint $table) {
            $table->foreignId('filiale_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['filiale_id']);
            $table->dropColumn('filiale_id');
        });

        Schema::table('action_logs', function (Blueprint $table) {
            $table->dropForeign(['filiale_id']);
            $table->dropColumn('filiale_id');
        });
    }
}