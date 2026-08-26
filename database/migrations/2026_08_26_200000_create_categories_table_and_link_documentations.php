<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 64);
            $table->string('slug', 64)->unique();
            $table->timestamps();
        });

        Schema::table('documentations', function (Blueprint $table) {
            $table->uuid('category_id')->nullable()->after('category');
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });

        // Backfill: turn each distinct legacy category string into a real category row.
        $names = DB::table('documentations')->whereNotNull('category')->distinct()->pluck('category');

        foreach ($names as $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }

            $id = (string) Str::uuid();
            DB::table('categories')->insert([
                'id' => $id,
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('documentations')->where('category', $name)->update(['category_id' => $id]);
        }

        Schema::table('documentations', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('documentations', function (Blueprint $table) {
            $table->string('category', 64)->nullable()->after('slug');
        });

        DB::table('documentations')
            ->join('categories', 'categories.id', '=', 'documentations.category_id')
            ->update(['documentations.category' => DB::raw('categories.name')]);

        Schema::table('documentations', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('categories');
    }
};
