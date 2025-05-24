<?php

namespace Vherbaut\LivewireDebugbar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileWatcherController
{
    public function getWatchedFiles(): JsonResponse
    {
        if (!config('livewire-debugbar.enabled') || !config('livewire-debugbar.hot_reload.enabled')) {
            return response()->json([]);
        }

        $files = [];
        $watchPaths = config('livewire-debugbar.hot_reload.watch_paths', []);
        $watchExtensions = config('livewire-debugbar.hot_reload.watch_extensions', ['.php', '.blade.php']);
        $ignorePatterns = config('livewire-debugbar.hot_reload.ignore_patterns', []);

        foreach ($watchPaths as $path) {
            $fullPath = base_path($path);
            if (File::isDirectory($fullPath)) {
                $files = array_merge($files, $this->getFilesFromDirectory($fullPath, $path, $watchExtensions, $ignorePatterns));
            }
        }

        return response()->json($files);
    }

    protected function getFilesFromDirectory(string $fullPath, string $relativePath, array $extensions, array $ignorePatterns): array
    {
        $files = [];

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $filePath = $file->getPathname();
                $relativeFilePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath);
                $relativeFilePath = str_replace('\\', '/', $relativeFilePath);

                // Vérifier les extensions
                if (!$this->hasValidExtension($filePath, $extensions)) {
                    continue;
                }

                // Vérifier les patterns à ignorer
                if ($this->shouldIgnoreFile($relativeFilePath, $ignorePatterns)) {
                    continue;
                }

                $files[] = [
                    'path' => $relativeFilePath,
                    'hash' => md5_file($filePath),
                    'mtime' => $file->getMTime(),
                    'size' => $file->getSize(),
                    'type' => $this->getFileType($filePath),
                    'component_name' => $this->getComponentName($filePath),
                    'view_name' => $this->getViewName($filePath),
                ];
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs de lecture de fichiers
        }

        return $files;
    }

    protected function hasValidExtension(string $filePath, array $extensions): bool
    {
        foreach ($extensions as $extension) {
            if (str_ends_with($filePath, $extension)) {
                return true;
            }
        }

        return false;
    }

    protected function shouldIgnoreFile(string $filePath, array $ignorePatterns): bool
    {
        foreach ($ignorePatterns as $pattern) {
            if (Str::is($pattern, $filePath)) {
                return true;
            }
        }

        return false;
    }

    protected function getFileType(string $filePath): string
    {
        if (str_contains($filePath, 'app/Livewire') || str_contains($filePath, 'app/Http/Livewire')) {
            return 'livewire-component';
        }

        if (str_contains($filePath, 'resources/views/livewire') && str_ends_with($filePath, '.blade.php')) {
            return 'livewire-view';
        }

        if (str_ends_with($filePath, '.blade.php')) {
            return 'blade-view';
        }

        if (str_ends_with($filePath, '.php')) {
            return 'php-file';
        }

        return 'unknown';
    }

    protected function getComponentName(string $filePath): ?string
    {
        if ($this->getFileType($filePath) !== 'livewire-component') {
            return null;
        }

        try {
            $content = File::get($filePath);

            // Extraire le nom de classe
            if (preg_match('/class\s+(\w+)\s+extends\s+Component/', $content, $matches)) {
                return $this->convertToKebabCase($matches[1]);
            }

            // Fallback: utiliser le nom de fichier
            $fileName = pathinfo($filePath, PATHINFO_FILENAME);
            return $this->convertToKebabCase($fileName);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getViewName(string $filePath): ?string
    {
        if (!str_contains($filePath, 'resources/views/')) {
            return null;
        }

        $viewPath = str_replace([
            'resources/views/',
            '.blade.php'
        ], '', $filePath);

        return str_replace('/', '.', $viewPath);
    }

    protected function convertToKebabCase(string $string): string
    {
        return Str::kebab($string);
    }
}
