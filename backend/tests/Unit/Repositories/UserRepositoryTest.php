<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\Implementations\Eloquent\Users\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository(new User());
    }

    public function test_constructor_sets_model(): void
    {
        $repository = new UserRepository(new User());
        $this->assertInstanceOf(UserRepository::class, $repository);
    }

    public function test_get_permissions_returns_user_permissions(): void
    {
        $permission1 = Permission::create(['name' => 'view_users']);
        $permission2 = Permission::create(['name' => 'create_users']);

        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo([$permission1, $permission2]);

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole($role);

        $permissions = $this->userRepository->getPermissions($user);

        $this->assertCount(2, $permissions);
        $this->assertTrue($permissions->contains('name', 'view_users'));
        $this->assertTrue($permissions->contains('name', 'create_users'));
    }

    public function test_get_permissions_returns_empty_collection_for_non_user(): void
    {
        $mockAuthenticatable = $this->createMock(\Illuminate\Contracts\Auth\Authenticatable::class);

        $permissions = $this->userRepository->getPermissions($mockAuthenticatable);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $permissions);
        $this->assertTrue($permissions->isEmpty());
    }

    public function test_attempt_with_valid_credentials_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $token = $this->userRepository->attempt($credentials);

        $this->assertNotFalse($token);
        $this->assertIsString($token);
    }

    public function test_attempt_with_invalid_credentials_returns_false(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password')
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrong_password'
        ];

        $token = $this->userRepository->attempt($credentials);

        $this->assertFalse($token);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        JWTAuth::setToken($token);

        $result = $this->userRepository->logout();

        $this->assertTrue($result);
    }

    public function test_user_returns_authenticated_user(): void
    {
        $user = User::factory()->create();

        JWTAuth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $authenticatedUser = $this->userRepository->user();

        $this->assertInstanceOf(User::class, $authenticatedUser);
        $this->assertEquals($user->id, $authenticatedUser->id);
    }

    public function test_user_returns_null_when_no_token(): void
    {
        $authenticatedUser = $this->userRepository->user();
        $this->assertNull($authenticatedUser);
    }

    public function test_get_ttl_returns_integer(): void
    {
        $ttl = $this->userRepository->getTTL();

        $this->assertIsInt($ttl);
        $this->assertGreaterThan(0, $ttl);
    }

    public function test_parse_token_returns_user_with_valid_token(): void
    {
        $user = User::factory()->create();

        JWTAuth::shouldReceive('parseToken->authenticate')
            ->once()
            ->andReturn($user);

        $parsedUser = $this->userRepository->parseToken();

        $this->assertInstanceOf(User::class, $parsedUser);
        $this->assertEquals($user->id, $parsedUser->id);
    }

    public function test_parse_token_returns_null_with_invalid_token(): void
    {
        JWTAuth::setToken('invalid.token.here');

        $parsedUser = $this->userRepository->parseToken();

        $this->assertNull($parsedUser);
    }

    public function test_parse_token_handles_token_expired_exception(): void
    {
        JWTAuth::setToken('expired.token.simulation');

        $parsedUser = $this->userRepository->parseToken();

        $this->assertNull($parsedUser);
    }

    public function test_validate_token_returns_true_for_valid_token(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $isValid = $this->userRepository->validateToken($token);

        $this->assertTrue($isValid);
    }

    public function test_validate_token_returns_false_for_invalid_token(): void
    {
        $invalidToken = 'invalid.token.here';

        $isValid = $this->userRepository->validateToken($invalidToken);

        $this->assertFalse($isValid);
    }

    public function test_validate_token_returns_false_for_malformed_token(): void
    {
        $malformedToken = 'malformed_token_without_dots';

        $isValid = $this->userRepository->validateToken($malformedToken);

        $this->assertFalse($isValid);
    }

    public function test_validate_token_returns_false_for_token_with_invalid_base64(): void
    {
        $invalidBase64Token = 'invalid@base64.token@here.segment@';

        $isValid = $this->userRepository->validateToken($invalidBase64Token);

        $this->assertFalse($isValid);
    }

    public function test_repository_extends_base_repository(): void
    {
        $this->assertInstanceOf(
            \App\Repositories\Implementations\Eloquent\BaseRepository::class,
            $this->userRepository
        );
    }

    public function test_repository_implements_interface(): void
    {
        $this->assertInstanceOf(
            \App\Repositories\Contracts\Users\UserRepositoryInterface::class,
            $this->userRepository
        );
    }

    public function test_get_permissions_with_user_having_no_permissions(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $permissions = $this->userRepository->getPermissions($user);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $permissions);
        $this->assertTrue($permissions->isEmpty());
    }

    public function test_parse_token_handles_general_exception(): void
    {
        JWTAuth::shouldReceive('parseToken->authenticate')
            ->once()
            ->andThrow(new \Tymon\JWTAuth\Exceptions\TokenInvalidException('Wrong number of segments'));

        $result = $this->userRepository->parseToken();

        $this->assertNull($result);
    }
}
