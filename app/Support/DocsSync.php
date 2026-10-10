<?php

namespace App\Support;

use App\Models\DocSync;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Copies the documentation Markdown from the dPanel repository into
 * resources/docs, either from a local checkout or straight from GitHub, and
 * records each run so the admin panel can show when the docs last changed.
 */
class DocsSync
{
    /**
     * Site page slug => file path in the dpanel repository.
     *
     * @return array<string, string>
     */
    public static function files(): array
    {
        return collect(config('site.docs', []))
            ->flatMap(fn ($pages) => array_keys($pages))
            ->mapWithKeys(fn ($slug) => [$slug => Docs::sourceFile($slug)])
            ->all();
    }

    public static function fromGitHub(?User $user = null, ?string $branch = null): DocSync
    {
        $branch ??= config('site.docs_branch', 'main');
        $base = self::rawBaseUrl($branch);

        return self::run("GitHub ({$branch})", $user, function (string $file) use ($base) {
            $response = Http::timeout(20)->retry(2, 500, fn ($e) => $e instanceof ConnectionException, throw: false)->get($base.'/'.$file);

            return $response->successful() ? $response->body() : null;
        });
    }

    public static function fromDirectory(string $source, ?User $user = null): DocSync
    {
        $source = rtrim($source, '/');

        return self::run($source, $user, fn (string $file) => is_file($source.'/'.$file) ? (string) file_get_contents($source.'/'.$file) : null);
    }

    /**
     * @param  callable(string): ?string  $fetch  Returns the file contents, or null when missing.
     */
    private static function run(string $source, ?User $user, callable $fetch): DocSync
    {
        $changed = [];
        $skipped = [];
        $message = null;

        try {
            foreach (self::files() as $slug => $file) {
                $contents = $fetch($file);

                if ($contents === null || trim($contents) === '') {
                    $skipped[] = $slug;

                    continue;
                }

                $path = Docs::path($slug);

                if (! is_file($path) || hash_file('sha256', $path) !== hash('sha256', $contents)) {
                    File::put($path, $contents);
                    $changed[] = $slug;
                }
            }
        } catch (Throwable $e) {
            report($e);
            $message = $e->getMessage();
        }

        $status = match (true) {
            $message !== null => 'failed',
            $skipped !== [] && count($skipped) === count(self::files()) => 'failed',
            $skipped !== [] => 'partial',
            default => 'success',
        };

        return DocSync::create([
            'user_id' => $user?->id,
            'source' => $source,
            'status' => $status,
            'changed' => $changed,
            'skipped' => $skipped,
            'message' => $message ?? ($status === 'failed' ? 'No documentation files could be downloaded.' : null),
        ]);
    }

    public static function rawBaseUrl(string $branch): string
    {
        $repository = trim((string) parse_url((string) config('site.repositories.panel'), PHP_URL_PATH), '/');

        return "https://raw.githubusercontent.com/{$repository}/{$branch}";
    }
}
