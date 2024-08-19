<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && $this->isTokenExpired($token)) {
            return response()->json(['message' => 'Token has expired'], 401);
        }

        return $next($request);
    }

    /**
     * Check if the token is expired based on the Sanctum expiration settings.
     *
     * @param  \Laravel\Sanctum\PersonalAccessToken  $token
     * @return bool
     */
    protected function isTokenExpired($token): bool
    {
        $expirationMinutes = config('sanctum.expiration');

        // If no expiration is set, tokens do not expire
        if (is_null($expirationMinutes)) {
            return false;
        }

        // Calculate the expiration date
        $expirationDate = $token->created_at->addMinutes($expirationMinutes);

        // Return true if the token has expired
        return $expirationDate->isPast();
    }
}
