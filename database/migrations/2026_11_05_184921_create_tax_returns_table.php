<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    // builds the table
    {
        Schema::create('tax_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('regime')->default('tot');
            $table->string('period');                       // e.g. 2026-10
            $table->decimal('income', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->boolean('is_nil')->default(false);
            $table->decimal('penalty', 15, 2)->default(0);
            $table->string('status')->default('submitted'); // submitted, processing, accepted
            $table->string('acknowledgement_no')->nullable();
            $table->timestamp('filed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'regime', 'period']); //stops DB from storing 2 returns for the same user and month
        });
    }

    public function down()
    // Deletes table and runs on rollback
    {
        Schema::dropIfExists('tax_returns');
    }
};