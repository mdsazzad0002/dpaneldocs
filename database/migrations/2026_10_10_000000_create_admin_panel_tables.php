<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admin panel: built-in roles and permissions (replacing spatie/laravel-permission),
 * settings (SMTP, AI), docs sync history, docs comments, review replies,
 * donations, and an outgoing mail log.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Remember who was an admin under spatie/laravel-permission, then drop its tables.
        $adminIds = [];
        if (Schema::hasTable('model_has_roles') && Schema::hasColumn('roles', 'guard_name')) {
            $adminIds = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'admin')
                ->pluck('model_has_roles.model_id')
                ->all();
        }

        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 60)->unique();
            $table->string('description')->nullable();
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('password')->constrained()->nullOnDelete();
        });

        $now = now();
        DB::table('roles')->insert([
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full access to everything, including staff and settings.',
                'permissions' => json_encode(['*']),
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Support agent',
                'slug' => 'support',
                'description' => 'Answers tickets, comments, reviews and email.',
                'permissions' => json_encode(['tickets.manage', 'reviews.manage', 'comments.manage', 'feedback.view', 'mail.send', 'ai.use']),
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Keeps the documentation in sync and reads feedback.',
                'permissions' => json_encode(['docs.sync', 'feedback.view', 'comments.manage']),
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        if ($adminIds !== []) {
            $adminRole = DB::table('roles')->where('slug', 'admin')->value('id');
            DB::table('users')->whereIn('id', $adminIds)->update(['role_id' => $adminRole]);
        }

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('doc_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source');
            $table->string('status', 20);
            $table->json('changed')->nullable();
            $table->json('skipped')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('page', 80)->index();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 80);
            $table->string('email')->nullable();
            $table->text('body');
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('is_staff')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('body');
            $table->timestamp('replied_at')->nullable()->after('approved_at');
        });

        Schema::create('donation_methods', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('bank');
            $table->string('label', 80);
            $table->string('bank_name', 120)->nullable();
            $table->string('account_name', 120)->nullable();
            $table->string('account_number', 80)->nullable();
            $table->string('branch', 120)->nullable();
            $table->string('routing_number', 40)->nullable();
            $table->string('swift_code', 20)->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('email')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->foreignId('donation_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_id', 120)->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_public')->default(true);
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('verified_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('outbound_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('to_email');
            $table->string('to_name', 120)->nullable();
            $table->string('subject');
            $table->text('body');
            $table->string('status', 20);
            $table->text('error')->nullable();
            $table->string('context', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_mails');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('donation_methods');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['reply', 'replied_at']);
        });

        Schema::dropIfExists('comments');
        Schema::dropIfExists('doc_syncs');
        Schema::dropIfExists('settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
