<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Permission;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if ($user->permissions()->exists()) {
                return;
            }

            if ($user->role?->role_name === 'Super Admin') {
                $this->ensurePermissionCatalog();
                $user->permissions()->sync(Permission::pluck('permission_id')->all());

                return;
            }

            $permissionIds = $user->role?->permissions()
                ->pluck('permissions.permission_id')
                ->all() ?? [];

            $user->permissions()->sync($permissionIds);
        });
    }

    private function ensurePermissionCatalog(): void
    {
        if (Permission::query()->exists()) {
            return;
        }

        app(RolePermissionSeeder::class)->run();
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => null,
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'is_verified' => true,
            'must_change_password' => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
        ]);
    }
}
