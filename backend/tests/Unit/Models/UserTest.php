<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:' . base64_encode('a16bytesecretkey')]);
    }

    public function test_user_implements_jwt_subject_interface(): void
    {
        $user = User::factory()->make();

        $this->assertInstanceOf(JWTSubject::class, $user);
    }

    public function test_user_has_fillable_attributes(): void
    {
        $fillable = ['name', 'email', 'password'];
        $user = new User();

        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_user_has_hidden_attributes(): void
    {
        $hidden = ['password', 'remember_token'];
        $user = new User();

        $this->assertEquals($hidden, $user->getHidden());
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-text-password'
        ]);

        $this->assertTrue(Hash::check('plain-text-password', $user->password));
    }

    public function test_email_verified_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }

    public function test_get_jwt_identifier_returns_user_key(): void
    {
        $user = User::factory()->create();

        $this->assertEquals($user->getKey(), $user->getJWTIdentifier());
    }

    public function test_get_jwt_custom_claims_returns_roles_and_permissions(): void
    {
        $role = Role::create(['name' => 'admin']);
        $permission = Permission::create(['name' => 'manage-users']);
        $role->givePermissionTo($permission);

        $user = User::factory()->create();
        $user->assignRole($role);

        $claims = $user->getJWTCustomClaims();

        $this->assertArrayHasKey('roles', $claims);
        $this->assertArrayHasKey('permissions', $claims);
        $this->assertContains('admin', $claims['roles']);
        $this->assertContains('manage-users', $claims['permissions']);
    }

    public function test_user_can_have_roles(): void
    {
        $role = Role::create(['name' => 'seller']);
        $user = User::factory()->create();

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('seller'));
        $this->assertEquals(1, $user->roles()->count());
    }

    public function test_user_can_have_permissions(): void
    {
        $permission = Permission::create(['name' => 'create-sales']);
        $user = User::factory()->create();

        $user->givePermissionTo($permission);

        $this->assertTrue($user->hasPermissionTo('create-sales'));
        $this->assertEquals(1, $user->permissions()->count());
    }

    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertNotNull($user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_factory_can_create_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }
}
