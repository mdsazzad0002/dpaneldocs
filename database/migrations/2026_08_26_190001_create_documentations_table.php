<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 191);
            $table->string('slug', 191)->unique();
            $table->string('category', 64)->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('status', 16)->default('pending'); // pending, published, rejected
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentations');
    }
};
