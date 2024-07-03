<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CheckTokenExpiration
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->user()->currentAccessToken();

        if ($token && $this->checkTokenExpiration($token)) {
            return response()->json(['message' => 'Token has expired'], 401);
        }

        return $next($request);
    }

    protected function checkTokenExpiration($token)
    {
        $expirationMinutes = config('sanctum.expiration');

        if (is_null($expirationMinutes)) {
            return false; // Tokens do not expire
        }

        $expirationDate = $token->created_at->addMinutes($expirationMinutes);

        return $expirationDate->isPast();
    }
}
