<?php

declare(strict_types=1);

namespace Tests\Installer\Security;

use Installer\Security\AccessControl;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AccessControl IP resolution with trusted proxies.
 *
 * These tests validate that getClientIp() only reads proxy headers
 * when REMOTE_ADDR is in the trusted proxies list.
 */
class AccessControlTest extends TestCase
{
    private string $storagePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storagePath = sys_get_temp_dir() . '/installer_test_' . uniqid();
        mkdir($this->storagePath, 0755, true);

        // Clean server vars
        unset(
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_X_FORWARDED_FOR'],
            $_SERVER['HTTP_X_REAL_IP'],
            $_SERVER['HTTP_CLIENT_IP'],
            $_SERVER['HTTP_CF_CONNECTING_IP'],
        );

        // Clear trusted proxies env
        putenv('INSTALLER_TRUSTED_PROXIES');
    }

    protected function tearDown(): void
    {
        // Clean up storage
        $files = glob($this->storagePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        if (is_dir($this->storagePath)) {
            rmdir($this->storagePath);
        }

        parent::tearDown();
    }

    public function test_returns_remote_addr_when_no_proxy_headers(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.50';

        $ac = new AccessControl($this->storagePath);
        $ip = $this->invokeGetClientIp($ac);

        $this->assertSame('203.0.113.50', $ip);
    }

    public function test_ignores_proxy_headers_when_remote_addr_not_trusted(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.50';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';
        $_SERVER['HTTP_X_REAL_IP'] = '10.0.0.2';

        // No trusted proxies configured — secure by default
        $ac = new AccessControl($this->storagePath);
        $ip = $this->invokeGetClientIp($ac);

        $this->assertSame('203.0.113.50', $ip, 'Should use REMOTE_ADDR when not behind trusted proxy');
    }

    public function test_reads_xff_when_remote_addr_is_trusted(): void
    {
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.99, 10.0.0.1';

        putenv('INSTALLER_TRUSTED_PROXIES=10.0.0.1,10.0.0.2');

        $ac = new AccessControl($this->storagePath);
        $ip = $this->invokeGetClientIp($ac);

        $this->assertSame('203.0.113.99', $ip, 'Should read first IP from X-Forwarded-For when behind trusted proxy');
    }

    public function test_reads_cf_connecting_ip_when_trusted(): void
    {
        $_SERVER['REMOTE_ADDR'] = '172.16.0.1';
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '198.51.100.25';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.25, 172.16.0.1';

        putenv('INSTALLER_TRUSTED_PROXIES=172.16.0.1');

        $ac = new AccessControl($this->storagePath);
        $ip = $this->invokeGetClientIp($ac);

        $this->assertSame('198.51.100.25', $ip, 'CF-Connecting-IP should have priority when trusted');
    }

    public function test_reads_x_real_ip_when_trusted(): void
    {
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $_SERVER['HTTP_X_REAL_IP'] = '192.0.2.50';

        putenv('INSTALLER_TRUSTED_PROXIES=10.0.0.1');

        $ac = new AccessControl($this->storagePath);
        $ip = $this->invokeGetClientIp($ac);

        $this->assertSame('192.0.2.50', $ip);
    }

    public function test_spoofing_blocked_when_not_trusted(): void
    {
        // Attacker at 203.0.113.50 sends fake headers
        $_SERVER['REMOTE_ADDR'] = '203.0.113.50';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '127.0.0.1';

        // Only 10.0.0.1 is trusted
        putenv('INSTALLER_TRUSTED_PROXIES=10.0.0.1');

        $ac = new AccessControl($this->storagePath);
        $ip = $this->invokeGetClientIp($ac);

        $this->assertSame('203.0.113.50', $ip, 'Must use REMOTE_ADDR when sender is not a trusted proxy');
    }

    public function test_default_no_trusted_proxies_is_secure(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.50';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';

        // No INSTALLER_TRUSTED_PROXIES set at all
        $ac = new AccessControl($this->storagePath);
        $ip = $this->invokeGetClientIp($ac);

        $this->assertSame('203.0.113.50', $ip, 'Default (no config) must be secure: REMOTE_ADDR only');
    }

    public function test_falls_back_to_zero_ip_when_no_remote_addr(): void
    {
        // No REMOTE_ADDR set (CLI / unusual environment)
        $ac = new AccessControl($this->storagePath);
        $ip = $this->invokeGetClientIp($ac);

        $this->assertSame('0.0.0.0', $ip);
    }

    /**
     * Use reflection to call private getClientIp()
     */
    private function invokeGetClientIp(AccessControl $ac): string
    {
        $method = new \ReflectionMethod(AccessControl::class, 'getClientIp');
        $method->setAccessible(true);

        return $method->invoke($ac);
    }
}
