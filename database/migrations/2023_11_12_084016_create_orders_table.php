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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('address_book_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->enum('status' , ['pending' , 'ready_to_picked' , 'ready_to_shipped' , 'delivered']);
            $table->enum('is_approved' , [0,1])->default(0);
            $table->unsignedDouble('total')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
