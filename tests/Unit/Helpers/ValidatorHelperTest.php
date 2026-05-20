<?php

declare(strict_types=1);

namespace Gymfit\Tests\Unit\Helpers;

use Gymfit\Exceptions\ValidationException;
use Gymfit\Helpers\ValidatorHelper;
use PHPUnit\Framework\TestCase;

class ValidatorHelperTest extends TestCase
{
    public function testRequiredPasses(): void
    {
        $v = ValidatorHelper::validate(['name' => 'Juan', 'email' => 'juan@test.com']);
        $v->required('name', 'email');
        $this->assertFalse($v->hasErrors());
    }

    public function testRequiredFails(): void
    {
        $v = ValidatorHelper::validate(['name' => '']);
        $v->required('name');
        $this->assertTrue($v->hasErrors());
    }

    public function testEmailValid(): void
    {
        $v = ValidatorHelper::validate(['email' => 'user@example.com']);
        $v->email('email');
        $this->assertFalse($v->hasErrors());
    }

    public function testEmailInvalid(): void
    {
        $v = ValidatorHelper::validate(['email' => 'not-an-email']);
        $v->email('email');
        $this->assertTrue($v->hasErrors());
    }

    public function testMinLength(): void
    {
        $v = ValidatorHelper::validate(['pass' => '123']);
        $v->minLength('pass', 6);
        $this->assertTrue($v->hasErrors());

        $v2 = ValidatorHelper::validate(['pass' => '123456']);
        $v2->minLength('pass', 6);
        $this->assertFalse($v2->hasErrors());
    }

    public function testInArray(): void
    {
        $v = ValidatorHelper::validate(['rol' => 'entrenador']);
        $v->inArray('rol', ['entrenador', 'cliente']);
        $this->assertFalse($v->hasErrors());

        $v2 = ValidatorHelper::validate(['rol' => 'admin']);
        $v2->inArray('rol', ['entrenador', 'cliente']);
        $this->assertTrue($v2->hasErrors());
    }

    public function testSanitize(): void
    {
        $v = ValidatorHelper::validate(['name' => '  <script>alert("xss")</script>  ']);
        $this->assertEquals('alert("xss")', $v->sanitize('name'));
    }

    public function testThrowIf(): void
    {
        $this->expectException(ValidationException::class);
        $v = ValidatorHelper::validate(['name' => '']);
        $v->required('name')->throwIf();
    }

    public function testMultipleErrors(): void
    {
        $v = ValidatorHelper::validate(['name' => '', 'email' => 'bad']);
        $v->required('name', 'email')->email('email');
        $this->assertCount(2, $v->getErrors()['name']);
        $this->assertCount(1, $v->getErrors()['email']);
    }

    public function testGetDefault(): void
    {
        $v = ValidatorHelper::validate(['name' => 'Juan']);
        $this->assertEquals('Juan', $v->get('name'));
        $this->assertNull($v->get('missing'));
        $this->assertEquals('default', $v->get('missing', 'default'));
    }
}
