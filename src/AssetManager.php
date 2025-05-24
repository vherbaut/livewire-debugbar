<?php

namespace Vherbaut\LivewireDebugbar;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetManager
{
    private const BASE_PATH = __DIR__ . '/../resources/dist';

    /**
     * Bootstrap the asset manager.
     */
    public static function boot(): void
    {
        $instance = new static();

        $instance->registerBladeDirectives();
        $instance->registerAssetRoutes();
    }

    /**
     * Register Blade directives for the debugbar.
     */
    public function registerBladeDirectives(): void
    {
        Blade::directive('livewireDebugbarStyles', function () {
            return "<?php echo app('" . AssetManager::class . "')->styles(); ?>";
        });

        Blade::directive('livewireDebugbarScripts', function () {
            return "<?php echo app('" . AssetManager::class . "')->scripts(); ?>";
        });
    }

    /**
     * Register routes for serving assets.
     */
    public function registerAssetRoutes(): void
    {
        // Route pour le CSS
        Route::get('/livewire-debugbar/app.css', function (): Response|BinaryFileResponse {
            return $this->pretendResponseIsFile(self::BASE_PATH . '/app.css', 'text/css');
        });

        // Route pour le JavaScript
        Route::get('/livewire-debugbar/app.js', function (): Response|BinaryFileResponse {
            return $this->pretendResponseIsFile(self::BASE_PATH . '/app.js', 'text/javascript');
        });

        // Route pour le manifest (si existe)
        if (file_exists(self::BASE_PATH . '/.vite/manifest.json')) {
            $manifest = $this->getManifest();
            foreach ($manifest as $key => $value) {
                if (is_array($value) && isset($value['file']) && str_starts_with($value['file'], 'assets/')) {
                    $path = '/livewire-debugbar/' . $value['file'];
                    $filePath = self::BASE_PATH . '/' . $value['file'];

                    Route::get($path, function () use ($filePath): Response|BinaryFileResponse {
                        $contentType = $this->getContentType($filePath);
                        return $this->pretendResponseIsFile($filePath, $contentType);
                    });
                }
            }
        }
    }

    /**
     * Generate script tags for the debugbar.
     */
    public function scripts(array $options = []): string
    {
        $nonce = isset($options['nonce']) ? ' nonce="' . $options['nonce'] . '"' : '';
        $hash = $this->getAssetHash('app.js');

        return sprintf(
            '<script src="/livewire-debugbar/app.js?v=%s" defer%s></script>',
            $hash,
            $nonce
        );
    }

    /**
     * Generate style tags for the debugbar.
     */
    public function styles(array $options = []): string
    {
        $nonce = isset($options['nonce']) ? ' nonce="' . $options['nonce'] . '"' : '';
        $hash = $this->getAssetHash('app.css');

        return sprintf(
            '<link rel="stylesheet" href="/livewire-debugbar/app.css?v=%s"%s>',
            $hash,
            $nonce
        );
    }

    /**
     * Get asset hash for cache busting.
     */
    private function getAssetHash(string $filename): string
    {
        $filePath = self::BASE_PATH . '/' . $filename;

        if (file_exists($filePath)) {
            return md5_file($filePath);
        }

        // Fallback pour le développement
        return time();
    }

    /**
     * Get the manifest file contents.
     */
    private function getManifest(): array
    {
        $manifestPath = self::BASE_PATH . '/.vite/manifest.json';

        if (!file_exists($manifestPath)) {
            return [];
        }

        $manifestContent = file_get_contents($manifestPath);

        if ($manifestContent === false) {
            throw new RuntimeException("Unable to read manifest file: {$manifestPath}");
        }

        $manifest = json_decode($manifestContent, true);

        if ($manifest === null) {
            throw new RuntimeException("Invalid JSON in manifest file: {$manifestPath}");
        }

        return $manifest;
    }

    /**
     * Create a response for a file with proper caching headers.
     */
    public function pretendResponseIsFile(
        string $file,
        string $contentType = 'text/javascript'
    ): Response|BinaryFileResponse {
        // Fallback si le fichier n'existe pas (développement)
        if (!file_exists($file)) {
            return $this->getFallbackResponse($file, $contentType);
        }

        $lastModified = filemtime($file);

        return $this->cachedFileResponse(
            $file,
            $contentType,
            $lastModified,
            fn (array $headers) => response()->file($file, $headers)
        );
    }

    /**
     * Get fallback response for missing assets.
     */
    private function getFallbackResponse(string $file, string $contentType): Response
    {
        if (str_ends_with($file, '.js')) {
            return response('// Assets not compiled. Run: npm run build', 200)
                ->header('Content-Type', 'text/javascript');
        }

        if (str_ends_with($file, '.css')) {
            return response('/* Assets not compiled. Run: npm run build */', 200)
                ->header('Content-Type', 'text/css');
        }

        return response('File not found', 404);
    }

    /**
     * Create a cached response for a file.
     */
    protected function cachedFileResponse(
        string $filename,
        string $contentType,
        int $lastModified,
        Closure $downloadCallback
    ): Response|BinaryFileResponse {
        $expires = strtotime('+1 year');
        if ($expires === false) {
            $expires = time() + 31536000;
        }

        $cacheControl = 'public, max-age=31536000';

        if ($this->matchesCache($lastModified)) {
            return response('', 304, [
                'Expires' => $this->httpDate($expires),
                'Cache-Control' => $cacheControl,
            ]);
        }

        $headers = [
            'Content-Type' => $contentType,
            'Expires' => $this->httpDate($expires),
            'Cache-Control' => $cacheControl,
            'Last-Modified' => $this->httpDate($lastModified),
        ];

        return $downloadCallback($headers);
    }

    /**
     * Check if the request matches the cache.
     */
    protected function matchesCache(int $lastModified): bool
    {
        $ifModifiedSince = app(Request::class)->header('if-modified-since');

        if ($ifModifiedSince === null) {
            return false;
        }

        $modifiedSinceTime = strtotime($ifModifiedSince);
        return $modifiedSinceTime !== false && $modifiedSinceTime === $lastModified;
    }

    /**
     * Format a timestamp for HTTP headers.
     */
    protected function httpDate(int $timestamp): string
    {
        return sprintf('%s GMT', gmdate('D, d M Y H:i:s', $timestamp));
    }

    /**
     * Get the content type based on file extension.
     */
    private function getContentType(string $filePath): string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        return match ($extension) {
            'css' => 'text/css',
            'js' => 'text/javascript',
            'map' => 'application/json',
            default => 'application/octet-stream',
        };
    }
}
