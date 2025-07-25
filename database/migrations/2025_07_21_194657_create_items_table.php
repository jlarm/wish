<?php

use App\Models\User;
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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignIdFor(User::class)->constrained('users');
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('link')->nullable();
            $table->integer('price')->nullable();
            $table->string('store')->nullable();
            $table->boolean('purchased')->default(false);
            $table->unsignedBigInteger('purchased_by')->nullable();
            $table->date('purchased_date')->nullable();
            $table->boolean('delivered')->default(false);
            $table->date('delivered_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
