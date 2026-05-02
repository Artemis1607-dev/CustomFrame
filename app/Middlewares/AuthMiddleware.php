<?php

namespace App\Middlewares;

use App\Exceptions\SessionException;
use Core\Request;
use Core\SessionWrapper;

/**
 * Provides an additional layer of session security.
 * 
 * Note that this middleware requires an active session.
 */
class AuthMiddleware extends SessionWrapper
{
    /**
     * @param Request $request
     *        Passed as request from \Core\Router.
     * @param \Closure $next
     *        Required in middleware chaining.
     * @param string $role
     *        Passed as a middleware parameter.
     * @return \Closure $next
     *         In fact, $next changes dynamically depending on
     *         the middlewares assigned to the matched route.
     */
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

    /**
     * Validates session role and auth-flag.
     * 
     * Once the session is evaluated as active and/or hijacked,
     * certain routes can be protected with role-checking or auth-flag
     * validation. That said, this method will check for a defined role
     * parameter extracted from "AuthMiddleware:role" and validate the
     * auth and auth_until session flags to ensure correct handling of
     * re-authentication or hijacking conditions.
     * 
     * @throws SessionException
     */
    protected function handleAuth(Request $request, \Closure $next, string $role)
    {
        if ($_SESSION['role'] !== $role) {
            throw new SessionException('Unsuficient permissions', 403);
        }

        if ($_SESSION['auth'] === false) {
            if (in_array($request->method, ['get'])) {
                $request->attributes['auth'] = false;
            } else {
                throw new SessionException('Re-authentication required', 403);
            }
        } elseif ($_SESSION['auth_until'] < time()) {
            $_SESSION['auth'] = false;
        }
        // Pursue the chaining
        return $next($request);
    }
}