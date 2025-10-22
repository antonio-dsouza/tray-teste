<?php

namespace Tests\Unit\DTOs\Auth;

use App\DTOs\Auth\LoginData;
use Tests\TestCase;

class LoginDataTest extends TestCase
{
    public function test_from_array_creates_login_data_instance(): void
    {
        $data = [
            'email' => 'user@example.com',
            'password' => 'secret123'
        ];

        $loginData = LoginData::fromArray($data);

        $this->assertInstanceOf(LoginData::class, $loginData);
        $this->assertEquals('user@example.com', $loginData->email);
        $this->assertEquals('secret123', $loginData->password);
    }

    public function test_from_array_trims_email_whitespace(): void
    {
        $data = [
            'email' => '  user@example.com  ',
            'password' => 'secret123'
        ];

        $loginData = LoginData::fromArray($data);

        $this->assertEquals('user@example.com', $loginData->email);
    }

    public function test_from_array_handles_missing_email(): void
    {
        $data = [
            'password' => 'secret123'
        ];

        $loginData = LoginData::fromArray($data);

        $this->assertEquals('', $loginData->email);
        $this->assertEquals('secret123', $loginData->password);
    }

    public function test_from_array_handles_missing_password(): void
    {
        $data = [
            'email' => 'user@example.com'
        ];

        $loginData = LoginData::fromArray($data);

        $this->assertEquals('user@example.com', $loginData->email);
        $this->assertEquals('', $loginData->password);
    }

    public function test_from_array_handles_empty_data(): void
    {
        $loginData = LoginData::fromArray([]);

        $this->assertEquals('', $loginData->email);
        $this->assertEquals('', $loginData->password);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $loginData = new LoginData('user@example.com', 'secret123');

        $array = $loginData->toArray();

        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('password', $array);
        $this->assertEquals('user@example.com', $array['email']);
        $this->assertEquals('secret123', $array['password']);
    }

    public function test_readonly_properties_cannot_be_modified(): void
    {
        $loginData = new LoginData('user@example.com', 'secret123');

        $this->assertEquals('user@example.com', $loginData->email);
        $this->assertEquals('secret123', $loginData->password);

        $reflection = new \ReflectionClass($loginData);
        $emailProperty = $reflection->getProperty('email');
        $passwordProperty = $reflection->getProperty('password');

        $this->assertTrue($emailProperty->isReadOnly());
        $this->assertTrue($passwordProperty->isReadOnly());
    }

    public function test_prepare_data_processes_null_values(): void
    {
        $data = [
            'email' => null,
            'password' => null
        ];

        $loginData = LoginData::fromArray($data);

        $this->assertEquals('', $loginData->email);
        $this->assertEquals('', $loginData->password);
    }

    public function test_prepare_data_processes_numeric_values(): void
    {
        $data = [
            'email' => 123,
            'password' => 456
        ];

        $loginData = LoginData::fromArray($data);

        $this->assertEquals('123', $loginData->email);
        $this->assertEquals('456', $loginData->password);
    }
}
