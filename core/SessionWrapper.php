<?php

namespace Core;

use Josantonius\Session\Session;

/**
 * Dedicated parent class of session middlewares.
 * 
 * Based on the work of Josantonius, this class is supplying
 * the middlewares with the necessary methods to control php
 * default sessions. Note that certain session data can
 * eventually turn null; interpret this as "unusable data"
 * according to the current session state.
 * 
 * @link https://github.com/josantonius/php-session
 */
class SessionWrapper extends Session
{
    /** Holds secure session configuration. */
    protected array $config;

    /** 
     * Assigns configuration based on the recommended ini settings.
     * 
     * @link https://www.php.net/manual/en/session.security.ini.php
     */
    protected function __construct()
    {
        $this->config = [
            // Cookies
            'name' => 'session_id',
            'cache_limiter' => 'nocache',
            'referer_check' => '',
            'cookie_path' => '/',
            'cookie_lifetime' => $_ENV['COOKIE_LIFETIME'] * 3600,
            'cookie_httponly' => 1,
            'cookie_secure' => 0,
            'cookie_samesite' => 'Lax',
            'use_cookies' => 1,
            'use_only_cookies' => 1,
            'use_strict_mode' => 1,
            'use_trans_sid' => 0,
            // Garbage collector
            'gc_maxlifetime' => $_ENV['GC_LIFETIME'] * 3600,
            'gc_probability' => 10,
            'gc_divisor' => 100,
        ];
    }
    
    /**
     * (Re)authenticates a session.
     * 
     * To prevent session injection, id regeneration is highly advised.
     * Moreover, this method is intended to be used by an AuthController 
     * in order to (re)authenticate a legitimate user.
     */
    protected function markSessionActive(int $id, string $role): void
    {
        $this->start($this->config);
        // Prevent injection
        session_regenerate_id(true);
        // Mark current session as Active
        $_SESSION = [
            // Identification
            'user_id' => $id,
            // Expiration
            'last_activity' => time(),
            'created_at' => time(),
            'obsolete_until' => null,
            'obsolete' => false,
            // Hijacking
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'hijacked' => false,
            // Auth
            'role' => $role,
            'auth' => true,
            'auth_until' => time() + $_ENV['AUTH_LIFETIME'] * 3600,
        ];
    }

    /**
     * Refreshes the ongoing session.
     * 
     * Note that so as to satisfy the "grace period" requirement,
     * this method marks the old session as obsolete and the new one
     * as unauthenticated. Thus, this concept was well-implemented by
     * the repository below:
     * 
     * @link https://github.com/tedivm/phpsessionmanager/blob/master/Session.class.php
     */
    protected function refreshSession(): void
    {
        // Mark current session as Obsolete
        $this->markSessionObsolete();
		// Create new session without destroying the old one
		session_regenerate_id();
		// Grab current session ID and close both sessions to allow other scripts to use them
		$new_session_id = session_id();
		session_commit();
		// Set session ID to the new one, and start it back up again
		session_id($new_session_id);
		$this->start($this->config);
		// Adjust the new session data
        $this->markSessionUnauthorized();
    }
    
    /**
     * Finishes the ongoing session.
     * 
     * Basically, forcefully destroys the ongoing session  and its cookie.
     */
    protected function finishSession(): void
    {
        $this->throwExceptionIfSessionWasNotStarted();
        // Delete instantly this session (keep in mind the network factor)
        session_unset();
        session_destroy();
        // Unset session cookie
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $_ENV['PRODUCTION'] ?? true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /** Marks a session as obsolete. */
    protected function markSessionObsolete(): void
    {
        $this->throwExceptionIfSessionWasNotStarted();

        $_SESSION['last_activity'] = null;
        $_SESSION['created_at'] = null;

        $_SESSION['obsolete_until'] = time() + $_ENV['OBSOLETE_LIFETIME'] * 60;
        $_SESSION['obsolete'] = true;

        $_SESSION['auth'] = false;
        $_SESSION['auth_until'] = null;
    }

    /** Marks a session as unauthorized. */
    protected function markSessionUnauthorized(): void
    {
        $this->throwExceptionIfSessionWasNotStarted();

        $_SESSION['last_activity'] = time();
        $_SESSION['created_at'] = time();

		$_SESSION['obsolete_until'] = null;
        $_SESSION['obsolete'] = false;

        $_SESSION['auth'] = false;
        $_SESSION['auth_until'] = null;
    }

    /** Marks a session as hijacked. */
    protected function markSessionHijacked(): void
    {
        $this->throwExceptionIfSessionWasNotStarted();

        $_SESSION['hijacked'] = true;
        $_SESSION['auth'] = false;
        $_SESSION['auth_until'] = null;
    }

    /** 
     * Prevents an unstarted session.
     * 
     * @throws \RuntimeException
     */
    protected function throwExceptionIfSessionWasNotStarted(): void
    {
        if (session_status() !== 2) {
            throw new \RuntimeException('Session not started', 500);
        }
    }
}