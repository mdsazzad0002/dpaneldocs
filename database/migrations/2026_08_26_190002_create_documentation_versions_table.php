<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('documentation_id')->constrained('documentations')->cascadeOnDelete();
            $table->string('version', 32);
            $table->text('changelog')->nullable();
            $table->string('file_path', 255);
            $table->string('file_name', 191);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedBigInteger('downloads')->default(0);
            $table->timestamps();

            $table->unique(['documentation_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentation_versions');
    }
};
