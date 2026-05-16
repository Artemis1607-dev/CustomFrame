<?php

use App\Middlewares\SessionMiddleware;
use Core\SessionWrapper;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\throwException;

/**
 * Testing the following conditions:
 * 
 * * Valid, Active, Obsolete Expired session
 * * Refresh and finish session
 */
class SessionTest extends TestCase
{
    public function testPursueOnInvalidSession()
    {
        $this->simulateSessionStates('invalid');

        $assert = SessionMiddleware::returnTrueIfSessionIsNotValid();

        $this->assertTrue($assert);
    }

    public function testPursueOnInactiveSession()
    {
        $this->simulateSessionStates('inactive');

        $assert = SessionMiddleware::returnTrueIfSessionIsNotActive();

        $this->assertTrue($assert);
    }

    public function testPursueOnObsoleteSession()
    {
        $this->simulateSessionStates('obsolete');

        $assert = SessionMiddleware::returnTrueIfSessionIsObsolete();

        $this->assertTrue($assert);
    }

    public function testPursueOnExpiredSession()
    {
        $this->simulateSessionStates('expired');

        $assert = SessionMiddleware::returnTrueIfSessionIsExpired();

        $this->assertTrue($assert);
    }

    public function testPursueOnFinishedSession()
    {
        KernelTest::loadDependencies();
        $session = new SessionWrapper();
        $_SERVER['HTTP_USER_AGENT'] = 'chrome';
        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
        $session->markSessionActive(0, 'admin');

        $session->finishSession();

        $this->assertEmpty($session->getId());
        $this->assertEmpty($_SESSION);
    }

    public function testPursueOnRefreshedSession()
    {
        KernelTest::loadDependencies();
        $session = new SessionWrapper();
        $_SERVER['HTTP_USER_AGENT'] = 'chrome';
        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
        $session->markSessionActive(0, 'admin');
        $assert = [
            // Identification
            'user_id' => 0,
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
            'role' => 'admin',
            'auth' => false,
            'auth_until' => 0,
        ];

        $old_id = $session->getId();
        $session->refreshSession();
        $new_id = $session->getId();

        $this->assertNotSame($old_id, $new_id);
        $this->assertSame($assert, $_SESSION);
    }

    public static function simulateSessionStates(string $state): void
    {
        switch ($state) {
            case 'invalid':
                $_SESSION = [];
                break;
            case 'inactive':
                $_SESSION = [
                    'obsolete_until' => time() - 10000,
                    'obsolete' => true,
                ];
                break;
            case 'obsolete':
                $_SESSION = [
                    'obsolete' => true
                ];
                break;
            case 'expired':
                $_SESSION = [
                    'last_activity' => time() - 10000,
                    'created_at' => time() - 10000
                ];
                break;
            default:
                throw new \InvalidArgumentException(
                    'Unexistant state', 500
                );
        }
    }
}