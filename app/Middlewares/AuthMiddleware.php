<?php

namespace App\Middlewares;

use App\Exceptions\SessionException;
use Core\Request;
use Core\SessionWrapper;

class AuthMiddleware extends SessionWrapper
{
    
    public function filter(Request $request, \Closure $next, string $role)
    {
        try {
            return $this->handleAuth($request, $next, $role);
        } catch (SessionException $e) {
            view('login', [
                'error' => $e->getCode() . ' ' . $e->getMessage(),
            ])->sendResponse();
        }
    }

    protected function handleAuth(Request $request, \Closure $next, string $role)
    {
        // Role checking
        if ($_SESSION['role'] !== $role) {
            throw new SessionException('Unsuficient permissions', 403);
        }
        // Mark the session as Unauthorized
        if ($_SESSION['auth'] === false) {
            if (in_array($request->method, ['get'])) {
                $request->attributes['auth'] = false;
            } else {
                throw new SessionException('Re-authentication required', 403);
            }
        } elseif ($_SESSION['auth_until'] < time()) {
            $_SESSION['auth'] = false;
        }
        return $next($request);
    }
}