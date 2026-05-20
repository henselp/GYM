<?php

declare(strict_types=1);

namespace Gymfit\Tests\Unit\Services;

use Gymfit\Services\SecurityService;
use Gymfit\Logger\Logger;
use PHPUnit\Framework\TestCase;

class SecurityServiceTest extends TestCase
{
    private SecurityService $securityService;

    protected function setUp(): void
    {
        $this->securityService = new SecurityService(
            $this->createMock(Logger::class)
        );
    }

    public function testValidatePasswordStrengthTooShort(): void
    {
        $error = $this->securityService->validatePasswordStrength('Ab1!');
        $this->assertNotNull($error);
    }

    public function testValidatePasswordStrengthNoUppercase(): void
    {
        $error = $this->securityService->validatePasswordStrength('abcdef1!@');
        $this->assertNotNull($error);
    }

    public function testValidatePasswordStrengthNoNumber(): void
    {
        $error = $this->securityService->validatePasswordStrength('Abcdefgh!');
        $this->assertNotNull($error);
    }

    public function testValidatePasswordStrengthNoSpecial(): void
    {
        $error = $this->securityService->validatePasswordStrength('Abcdefg1');
        $this->assertNotNull($error);
    }

    public function testValidatePasswordStrengthValid(): void
    {
        $error = $this->securityService->validatePasswordStrength('Abcdef1!@');
        $this->assertNull($error);
    }

    public function testValidateEmail(): void
    {
        $this->assertTrue($this->securityService->validateEmail('user@example.com'));
        $this->assertFalse($this->securityService->validateEmail('not-an-email'));
        $this->assertFalse($this->securityService->validateEmail(''));
    }

    public function testSanitizeInput(): void
    {
        $input = [
            'name' => '  <script>alert("x")</script>  ',
            'email' => '  TEST@EXAMPLE.COM  ',
            'age' => 25,
        ];

        $result = $this->securityService->sanitizeInput($input);

        $this->assertStringNotContainsString('<script>', $result['name']);
        $this->assertStringNotContainsString('  ', $result['name']);
        $this->assertEquals(25, $result['age']);
    }
}
