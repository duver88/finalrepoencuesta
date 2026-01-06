<?php

namespace App\Http\Middleware;

use App\Models\Vote;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateVote
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $surveyId = $request->route('slug');

        if (!$surveyId) {
            return $next($request);
        }

        $userAgent = $request->header('User-Agent');

        // Permitir bots de redes sociales (para previews y anuncios)
        $allowedBots = [
            'facebookexternalhit',      // Bot principal de Facebook
            'FacebookExternalHit',      // Variante con mayúsculas
            'facebookcatalog',          // Bot de catálogo de Facebook
            'Facebot',                  // Otro bot de Facebook
            'meta-externalagent',       // Meta external agent
            'WhatsApp',                 // WhatsApp bot
            'Twitterbot',               // Twitter bot
            'LinkedInBot',              // LinkedIn bot
            'TelegramBot',              // Telegram bot
            'Slackbot',                 // Slack bot
            'Discordbot',               // Discord bot
            'Google-InspectionTool',    // Google inspection
            'Googlebot',                // Google crawler
            'Pinterest',                // Pinterest bot
            'Instagrambot',             // Instagram bot
        ];

        $isAllowedBot = false;
        foreach ($allowedBots as $allowedBot) {
            if (stripos($userAgent, $allowedBot) !== false) {
                $isAllowedBot = true;
                break;
            }
        }

        // Si no es un bot permitido, verificar bots maliciosos
        if (!$isAllowedBot) {
            // Detectar bots maliciosos por User-Agent
            $botPatterns = [
                'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python-requests',
                'postman', 'insomnia', 'http', 'scrape', 'harvest'
            ];

            foreach ($botPatterns as $pattern) {
                if (stripos($userAgent, $pattern) !== false) {
                    abort(403, 'Acceso denegado.');
                }
            }

            // Verificar User-Agent vacío (sospechoso)
            if (empty($userAgent)) {
                abort(403, 'Acceso denegado.');
            }
        }

        // Verificar honeypot (campo oculto que los bots llenan)
        if ($request->filled('website') || $request->filled('url_field')) {
            abort(403, 'Acceso denegado.');
        }

        return $next($request);
    }
}
