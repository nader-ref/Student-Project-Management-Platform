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
        Schema::create('projectrequests', function (Blueprint $table) {
            $table->id();
            $table->integer('projectid');
            $table->integer('count');
            $table->string('nameone');
            $table->integer('oneid');
            $table->string('nametwo')->nullable();
            $table->integer('twoid')->nullable();
            $table->string('namethree')->nullable();
            $table->integer('threeid')->nullable();
            $table->boolean('accepted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projectrequests');
    }
};
