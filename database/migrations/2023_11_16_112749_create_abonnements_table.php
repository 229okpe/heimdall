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
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->text('details');
            $table->unsignedBigInteger('produit_id')->nullable();	
           $table->string('nomClient');
            $table->string('emailClient');
            $table->string('dateExpiration');
            $table->string('attribue')->default('false');
            $table->foreign('produit_id')->references('id')->on('produits')->onDelete('cascade');
   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};
