<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('checkout_request_id')->nullable()->after('reference');
            $table->string('mpesa_receipt')->nullable()->after('checkout_request_id');
            $table->string('status')->default('manual')->after('mpesa_receipt');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['checkout_request_id', 'mpesa_receipt', 'status']);
        });
    }
};