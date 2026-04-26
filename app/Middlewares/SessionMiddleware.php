<?php

namespace App\Middlewares;

use Core\{SessionWrapper, Request};
use App\Interfaces\Middleware;
use App\Exceptions\SessionException;

/**
 * Handles default php sessions.
 * 
 * This class is supposed to handle different session states under condition
 * of an already valid session cookie and session data. In other words, 
 * this middleware requires two things: 
 * a non-empty and a valid cookie a valid, an active, not hijacked, not expired
 * 
 * * Session cookie: non-empty and valid
 * * Session data: valid, active, not obsolete, not expired and not hijacked
 * 
 * Thus, this middleware requires a dedicated controller whose role would be
 * to create, on successful login, valid sessions including this data:
 * 
 * * user_id: associated id to the user
 * * role: associated role to the user
 * * ip_address: remote IP address
 * * user_agent: remote browser
 * * last_activity: expiration dynamic limit
 * * obsolete: obsolescence indicator
 * * obsolete_until: obsolescence static limit
 * * auth: authentication indicator
 * * auth_until: authentication static limit
 * * hijacked: security metadata
 */
class SessionMiddleware extends SessionWrapper
{
    public function filter(Request $request, \Closure $next)
    {
        try {
            return $this->handleSession($request, $next);
        } catch (SessionException $e) {
            view('login', [
                'error' => $e->getCode() . ' ' . $e->getMessage(),
            ])->sendResponse();
        }
    }

    protected function handleSession(Request $request, \Closure $next)
    {
        $this->start($this->config);
        // In case not Valid
        if ($this->returnTrueIfSessionIsNotValid()) {
            throw new SessionException('Session is invalid');
        }
        // In case not Active
        if ($this->returnTrueIfSessionIsNotActive()) {
            $this->finishSession();
            throw new SessionException('Session is inactive');
        }
        // In case Obsolete
        if ($this->returnTrueIfSessionIsObsolete()) {
            // Since auth is false in the obsolete state, only get requests are accepted
            return self::preventSessionHijacking($request, $next);
        }
        // In case Expired
        if ($this->returnTrueIfSessionIsExpired()) {
            $this->refreshSession();
            return self::preventSessionHijacking($request, $next);
        }
        // Normal flow
        $_SESSION['last_activity'] = time();
        return self::preventSessionHijacking($request, $next);
    }

    protected function returnTrueIfSessionIsNotValid(): bool
    {
        return empty($_SESSION);
    }

    protected function returnTrueIfSessionIsNotActive(): bool
    {
        return $_SESSION['obsolete_until'] <= time()
            && $_SESSION['obsolete'] === true;
    }

    protected function returnTrueIfSessionIsExpired(): bool
    {
        return $_SESSION['last_activity'] <= time() - $_ENV['DYNAMIC_LIFETIME'] * 3600
            || time() - $_SESSION['created_at'] > $_ENV['STATIC_LIFETIME'] * 3600;
    }

    protected function returnTrueIfSessionIsObsolete(): bool
    {
        return $_SESSION['obsolete'] === true;
    }

    protected static function preventSessionHijacking(Request $request, \Closure $next)
    {
        if ($_SESSION['ip_address'] === $_SERVER['REMOTE_ADDR']
            && $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT']
        ) {
            return $next($request);
        } else {
            // Handle in the AuthMiddleware
            $_SESSION['hijacked'] = true;
            // Optionally remove any trust
            $_SESSION['auth'] = false;
            $_SESSION['auth_until'] = null;
            return $next($request);
        }
    }
}