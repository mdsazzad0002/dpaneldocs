<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The documentation is now static Markdown (resources/docs), so the old
 * posts/versions/categories CMS tables and its permissions are removed. The
 * only data the site keeps is what visitors send in: reviews, support
 * tickets (with their replies), and "was this page helpful?" feedback.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('documentation_versions');
        Schema::dropIfExists('documentations');
        Schema::dropIfExists('categories');

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->whereIn('name', [
                'manage_documentation', 'manage_versions', 'manage_categories', 'manage_users', 'manage_roles',
            ])->delete();
            DB::table('roles')->whereIn('name', ['editor', 'contributor'])->delete();
        }

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('email');
            $table->string('company', 120)->nullable();
            $table->unsignedTinyInteger('rating');
            $table->string('title', 120);
            $table->text('body');
            $table->string('status', 20)->default('pending')->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 16)->unique();
            $table->string('access_token', 64);
            $table->string('name', 80);
            $table->string('email')->index();
            $table->string('category', 30);
            $table->string('priority', 20)->default('normal');
            $table->string('subject', 160);
            $table->text('message');
            $table->string('dpanel_version', 40)->nullable();
            $table->string('server_os', 80)->nullable();
            $table->string('status', 20)->default('open')->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name', 80);
            $table->boolean('is_staff')->default(false);
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('page_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('page', 80)->index();
            $table->boolean('helpful');
            $table->string('comment', 1000)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_feedback');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('reviews');
    }
};
