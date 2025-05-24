<?php

namespace Vherbaut\LivewireDebugbar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Vherbaut\LivewireDebugbar\DebugbarCollector;

class DebugbarMiddleware
{
    protected DebugbarCollector $collector;

    public function __construct(DebugbarCollector $collector)
    {
        $this->collector = $collector;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Injecter la debugbar seulement pour les réponses HTML appropriées
        if ($this->shouldInjectDebugbar($request, $response)) {
            $this->injectDebugbar($response);
        }

        return $response;
    }

    protected function shouldInjectDebugbar(Request $request, $response): bool
    {
        // Vérifier si la debugbar est activée
        if (!config('livewire-debugbar.enabled', false)) {
            return false;
        }

        // Vérifier si on est en mode debug
        if (!config('app.debug', false)) {
            return false;
        }

        // Vérifier que c'est une réponse HTTP appropriée
        if (!$response instanceof Response) {
            return false;
        }

        // Vérifier le type de contenu
        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return false;
        }

        // Éviter les requêtes AJAX et API
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return false;
        }

        // Éviter les requêtes Livewire
        if ($request->hasHeader('X-Livewire')) {
            return false;
        }

        // Vérifier que la réponse contient du HTML valide
        $content = $response->getContent();
        if (!str_contains($content, '</body>')) {
            return false;
        }

        return true;
    }

    protected function injectDebugbar($response): void
    {
        $content = $response->getContent();
        $debugbarHtml = $this->collector->render();

        if (empty($debugbarHtml)) {
            return;
        }

        // Injecter avant la fermeture du body
        $content = str_replace('</body>', $debugbarHtml . '</body>', $content);

        $response->setContent($content);
    }
}
