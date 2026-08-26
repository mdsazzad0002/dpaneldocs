<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Documentation;
use App\Services\DocumentationAiGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GenerateDocumentationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        private readonly string $topic,
        private readonly string $submittedBy,
    ) {}

    public function handle(DocumentationAiGenerator $generator): void
    {
        try {
            $draft = $generator->generate($this->topic);
        } catch (Throwable $e) {
            Log::warning('AI documentation generation failed', [
                'topic' => $this->topic,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $category = Category::findOrCreateByName($draft['category']);

        Documentation::create([
            'id' => (string) Str::uuid(),
            'title' => $draft['title'],
            'slug' => Documentation::uniqueSlug($draft['title']),
            'category_id' => $category->id,
            'excerpt' => $draft['excerpt'] ?: null,
            'content' => $draft['content'],
            'status' => 'pending',
            'submitted_by' => $this->submittedBy,
            'views' => 0,
        ]);
    }
}
