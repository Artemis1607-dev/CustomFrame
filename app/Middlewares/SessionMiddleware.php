<?php

namespace App\Middlewares;

use Core\{SessionWrapper, Request};
use App\Exceptions\SessionException;

/**
 * Handles default php sessions.
 * 
 * This class is supposed to handle different session states under condition
 * of an already valid session cookie and session data. In other words, it
 * is responsible to handle the following conditions:
 * 
 * * Session cookie: non-empty and valid
 * * Session data: valid, active, not obsolete, not expired and not hijacked
 * 
 * Moreover, this middleware requires an AuthController, whose role is to
 * to create, on successful login, active sessions including this session data:
 * 
 * * user_id: associated id to the user's database entry
 * * role: associated role to the user's database entry
 * * ip_address: remote IP-address
 * * user_agent: remote user-agent
 * * last_activity: dynamic expiration limit
 * * created_at: static expiration limit
 * * obsolete: obsolescence indicator
 * * obsolete_until: obsolescence static limit
 * * auth: authentication indicator
 * * auth_until: authentication static limit
 * * hijacked: metadata of hijacked state
 */
class SessionMiddleware extends SessionWrapper
{
    /**
     * @param Request $request
     *        Passed as request from \Core\Router.
     * @param \Closure $next
     *        Required in middleware chaining.
     * @return \Closure $next
     *         In fact, $next changes dynamically depending on
     *         the middlewares assigned to the matched route.
     */
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

    /**
     * Handles different session states. Following states are implemented
     * and validated in the respective order:
     * 
     * * (In)valid
     * * (In)active -> (Not) Hijacked
     * * (Not) Obsolete -> (Not) Hijacked
     * * (Not) Expired -> (Not) Hijacked
     * 
     * @param Request $request
     *        Passed as request from \Core\Router.
     * @param \Closure $next
     *        Required in middleware chaining.
     * @throws SessionException
     * @return \Closure $next
     *         In fact, $next changes dynamically depending on
     *         the middlewares assigned to the matched route.
     */
    protected function handleSession(Request $request, \Closure $next)
    {
        $this->start($this->config);
        // Validate session
        if ($this->returnTrueIfSessionIsNotValid()) {
            throw new SessionException('Re-authentication required', 401);
        }
        if ($this->returnTrueIfSessionIsNotActive()) {
            $this->finishSession();
            throw new SessionException('Re-authentication required', 403);
        }
        if ($this->returnTrueIfSessionIsObsolete()) {
            return $this->preventSessionHijacking($request, $next);
        }
        if ($this->returnTrueIfSessionIsExpired()) {
            $this->refreshSession();
            return $this->preventSessionHijacking($request, $next);
        }
        // Normal flow
        $_SESSION['last_activity'] = time();
        return $this->preventSessionHijacking($request, $next);
    }

    /**
     * Rejects request in case the session is invalid.
     * 
     * Valid state concerns the session id and the session data.
     * Futhermore, it is mandatory to use an existant session id with
     * the respectively associated data. For instance, let's take two cases:
     * 
     * * In case a user initializes a session with the dedicated AuthController,
     * the session id and the session data is considered as valid since the
     * id is existant and points to non-empty data.
     * * In case a user modified or provided a custom session id, it wouldn't
     * be recognized by the server since the id is unexistant and as a 
     * consequence points to empty data.
     */
    protected function returnTrueIfSessionIsNotValid(): bool
    {
        return empty($_SESSION);
    }

    /**
     * Finishes the session in case it is inactive.
     * 
     * In the current implementation, active state is considered
     * as a combination of not obsolete and not expired states.
     * It is essentially used to satisfy the "grace period" described 
     * in the link below.
     * 
     * @link www.php.net/manual/en/features.session.security.management.php#features.session.security.management.non-adaptive-session
     */
    protected function returnTrueIfSessionIsNotActive(): bool
    {
        return $_SESSION['obsolete_until'] <= time()
            && $_SESSION['obsolete'] === true;
    }

    /**
     * Refreshes the session in case it is expired
     * 
     * By default, a session has to follow certain expiration rules.
     * That said, we dispose of a static and a dynamic metadata, which
     * defines the expiration of an ongoing session. Basically, the obsolete
     * flag gets updated with each succesful request and the created_at gets
     * updated only on a successful relogin, however both are used to ensure
     * that the session is always up-to-date. Additionally, to learn more 
     * about session refreshing, follow the class below:
     * 
     * @see Core\SessionWrapper
     */
    protected function returnTrueIfSessionIsExpired(): bool
    {
        return $_SESSION['last_activity'] <= time() - $_ENV['DYNAMIC_LIFETIME'] * 3600
            || time() - $_SESSION['created_at'] > $_ENV['STATIC_LIFETIME'] * 3600;
    }

    /**
     * Accepts a request only in case the request method is get.
     * 
     * Note that before the session reaches this condition, the
     * obsolete_until has to be at the most 1 minute old. Otherwise,
     * the session would be finished and the request rejected.
     * 
     * * Request method: GET
     * * obsolete_until: not expired
     */
    protected function returnTrueIfSessionIsObsolete(): bool
    {
        return $_SESSION['obsolete'] === true;
    }

    /**
     * Provides an additional security layer based on the hijacking state.
     * 
     * Note that in case hijacking is detected, the session turns unauthenticated
     * and hijacked at once. In practice, it would require the user to relogin
     * unless he wills to use GET routes within the expiration limitations.
     * Considering the inconsistance of certain network interfaces, instead of
     * force quit on IP or UA anomalies, it is more user-friendly to notify about
     * the potential attack and suggest to finish the ongoing session and relogin. 
     * 
     * @param Request $request
     *        Passed as request from \Core\Router.
     * @param \Closure $next
     *        Required in middleware chaining.
     * @return \Closure $next
     *         In fact, $next changes dynamically depending on
     *         the middlewares assigned to the matched route.
     */
    protected function preventSessionHijacking(Request $request, \Closure $next)
    {
        if ($_SESSION['ip_address'] === $_SERVER['REMOTE_ADDR']
            && $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT']
        ) {
            return $next($request);
        } else {
            $this->markSessionHijacked();
            // Pursue the chaining
            return $next($request);
        }
    }
}