<?php

namespace Core;

use Josantonius\Session\Session;

/**
 * Dedicated parent class of session middlewares.
 * 
 * Based on the work of Josantonius, this class is supplying
 * the middlewares with the necessary methods to control php
 * default sessions.
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
    public function __construct()
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
     * Refreshes the ongoing session.
     * 
     * Note that so as to satisfy the "grace period" requirement,
     * this method marks the old session as obsolete and the new one
     * as unauthenticated. In order to preserve copyright, this concept 
     * was implemented in the repository below:
     * 
     * @link https://github.com/tedivm/phpsessionmanager/blob/master/Session.class.php
     */
    public function refreshSession(): void
    {
        $this->throwExceptionIfSessionWasNotStarted();
        // Mark current session as Obsolete
        $this->markSessionObsolete();
		// Create new session without destroying the old one
		$this->regenerateId();
		// Grab current session ID and close both sessions to allow other scripts to use them
		$new_session_id = $this->getId();
		session_commit();
		// Set session ID to the new one, and start it back up again
		$this->setId($new_session_id);
		$this->start($this->config);
		// Adjust the new session data
        $this->markSessionUnauthorized();
    }
    
    /**
     * Finishes the ongoing session.
     * 
     * Basically destroys the ongoing session and its cookie.
     */
    public function finishSession(): void
    {
        $this->throwExceptionIfSessionWasNotStarted();
        // Delete instantly this session (keep in mind the network factor)
        $this->clear();
        $this->destroy();
        // Unset session cookie
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $_ENV['PRODUCTION'] ?? true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * (Re)authenticates a session.
     * 
     * To prevent session injection, id regeneration is highly advised.
     * Moreover, this method is intended to be used by an AuthController 
     * in order to (re)authenticate a legitimate user.
     */
    public function markSessionActive(int $id, string $role): void
    {
        $this->start($this->config);
        // Prevent injection
        $this->regenerateId(true);
        // Mark current session as Active
        $_SESSION = [
            // Identification
            'user_id' => $id,
            // Expiration
            'last_activity' => time(),
            'created_at' => time(),
            'obsolete_until' => 0,
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

    /** Marks a session as obsolete. */
    protected function markSessionObsolete(): void
    {
        $this->throwExceptionIfSessionWasNotStarted();

        $_SESSION['last_activity'] = 0;
        $_SESSION['created_at'] = 0;

        $_SESSION['obsolete_until'] = time() + $_ENV['OBSOLETE_LIFETIME'] * 60;
        $_SESSION['obsolete'] = true;

        $_SESSION['auth'] = false;
        $_SESSION['auth_until'] = 0;
    }

    /** Marks a session as unauthorized. */
    protected function markSessionUnauthorized(): void
    {
        $this->throwExceptionIfSessionWasNotStarted();

        $_SESSION['last_activity'] = time();
        $_SESSION['created_at'] = time();

		$_SESSION['obsolete_until'] = 0;
        $_SESSION['obsolete'] = false;

        $_SESSION['auth'] = false;
        $_SESSION['auth_until'] = 0;
    }

    /** Marks a session as hijacked. */
    protected function markSessionHijacked(): void
    {
        $this->throwExceptionIfSessionWasNotStarted();

        $_SESSION['hijacked'] = true;
        $_SESSION['auth'] = false;
        $_SESSION['auth_until'] = 0;
    }

    /** 
     * Prevents an unstarted session.
     * 
     * @throws \RuntimeException
     */
    protected function throwExceptionIfSessionWasNotStarted(): void
    {
        if (!$this->isStarted()) {
            throw new \RuntimeException('Session not started', 500);
        }
    }
}