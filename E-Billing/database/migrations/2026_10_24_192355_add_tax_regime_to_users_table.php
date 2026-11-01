<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tax_regime')->nullable()->after('is_admin'); 
            // the line above adds tax_regime column to the users table (string means it holds text)and stores tot and self-assessment 
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tax_regime');
        });
    }
};