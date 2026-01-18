<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectOrchidTheme
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only inject for HTML responses
        if ($response instanceof \Illuminate\Http\Response && 
            str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            
            $themeLink = '<link rel="stylesheet" href="' . asset('css/orchid-theme.css') . '">';
            
            $content = $response->getContent();
            $content = str_replace('</head>', $themeLink . '</head>', $content);
            
            $response->setContent($content);
        }

        return $response;
    }
}
