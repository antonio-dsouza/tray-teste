<?php

namespace Tests\Unit\Services;

use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\InvalidTokenException;
use App\Models\User;
use App\Repositories\Contracts\Users\UserRepositoryInterface;
use App\Services\Auth\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->userRepository);
    }

    public function test_login_with_valid_credentials_returns_token_and_user_data(): void
    {
        $credentials = ['email' => 'test@example.com', 'password' => 'password'];
        $token = 'fake-jwt-token';
        $user = User::factory()->make(['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com']);

        $this->userRepository
            ->shouldReceive('attempt')
            ->once()
            ->with($credentials)
            ->andReturn($token);

        $this->userRepository
            ->shouldReceive('getTTL')
            ->once()
            ->andReturn(60);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $result = $this->authService->login($credentials);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($token, $result['access_token']);
        $this->assertEquals('bearer', $result['token_type']);
        $this->assertEquals(3600, $result['expires_in']);
        $this->assertEquals($user->id, $result['user']['id']);
        $this->assertEquals($user->name, $result['user']['name']);
        $this->assertEquals($user->email, $result['user']['email']);
    }

    public function test_login_with_invalid_credentials_throws_exception(): void
    {
        $credentials = ['email' => 'test@example.com', 'password' => 'wrong-password'];

        $this->userRepository
            ->shouldReceive('attempt')
            ->once()
            ->with($credentials)
            ->andReturn(false);

        $this->expectException(InvalidCredentialsException::class);

        $this->authService->login($credentials);
    }

    public function test_login_includes_user_roles_and_permissions(): void
    {
        $credentials = ['email' => 'test@example.com', 'password' => 'password'];
        $token = 'fake-jwt-token';

        $user = User::factory()->create(['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com']);
        $role = Role::create(['name' => 'admin']);
        $permission = Permission::create(['name' => 'manage-users']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->userRepository
            ->shouldReceive('attempt')
            ->once()
            ->with($credentials)
            ->andReturn($token);

        $this->userRepository
            ->shouldReceive('getTTL')
            ->once()
            ->andReturn(60);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $result = $this->authService->login($credentials);

        $this->assertContains('admin', $result['user']['roles']);
        $this->assertContains('manage-users', $result['user']['permissions']);
    }

    public function test_user_returns_authenticated_user_data(): void
    {
        $user = User::factory()->create(['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com']);

        $this->userRepository
            ->shouldReceive('user')
            ->once()
            ->andReturn($user);

        $result = $this->authService->user();

        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($user->id, $result['user']['id']);
        $this->assertEquals($user->name, $result['user']['name']);
        $this->assertEquals($user->email, $result['user']['email']);
    }

    public function test_user_throws_exception_when_no_authenticated_user(): void
    {
        $this->userRepository
            ->shouldReceive('user')
            ->once()
            ->andReturn(null);

        $this->expectException(InvalidTokenException::class);

        $this->authService->user();
    }

    public function test_user_includes_roles_and_permissions(): void
    {
        $user = User::factory()->create(['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com']);
        $role = Role::create(['name' => 'seller']);
        $permission = Permission::create(['name' => 'create-sales']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->userRepository
            ->shouldReceive('user')
            ->once()
            ->andReturn($user);

        $result = $this->authService->user();

        $this->assertContains('seller', $result['user']['roles']);
        $this->assertContains('create-sales', $result['user']['permissions']);
    }

    public function test_logout_calls_repository_logout(): void
    {
        $this->userRepository
            ->shouldReceive('logout')
            ->once()
            ->andReturn(true);

        $result = $this->authService->logout();

        $this->assertTrue($result);
    }

    public function test_logout_returns_false_when_repository_fails(): void
    {
        $this->userRepository
            ->shouldReceive('logout')
            ->once()
            ->andReturn(false);

        $result = $this->authService->logout();

        $this->assertFalse($result);
    }
}
