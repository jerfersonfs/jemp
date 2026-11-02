<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id'); // Define como Primary Key e Auto Increment
            $table->string('customer_name', 150);
            $table->string('customer_document', 20)->unique('uq_customers_document');
            $table->string('customer_contact', 30)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->string('customer_payment_terms');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
