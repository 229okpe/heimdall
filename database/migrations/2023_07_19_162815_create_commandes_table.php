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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produit_id')->nullable();
            $table->string('order_id');	
            $table->string('status')->default('En cours');
            $table->unsignedBigInteger('user_id')->nullable();	
            $table->dateTime('date_created')->nullable();
            $table->integer('prix_total')->nullable();
            $table->text('details')->nullable();	
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
