<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Enums\UserType;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake('ar_SA')->unique()->userName(),
            'code' => 'IQ',
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'password' => Hash::make('12345678'), // password
            'type' => $this->faker->randomElement(UserType::getAllValues())
        ];
    }

    public function inventoryAdmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => UserType::INVENTORY_ADMIN,
            ];
        });
    }

    public function customer()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => UserType::Customer,
            ];
        });
    }

    public function driver()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => UserType::Driver,
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {

            $permissions = Role::where('name', $user->type->value)->first()?->getPermissionNames();
            $user->givePermissionTo($permissions ?: []);
            if ($user->type === UserType::Customer) {
                $customer = Customer::factory()->for($user)->create(['phone' => $user->phone]);
                $user->profile()->create([
                    'first_name' => explode(' ', $customer->name)[0],
                    'last_name' => explode(' ', $customer->name, 2)[1],
                    'created_by' => 1,
                ]);
            } else if ($user->type === UserType::Driver) {
                $driver = Driver::factory()->for($user)->create(['phone' => $user->phone]);
                $user->profile()->create([
                    'first_name' => explode(' ', $driver->name,)[0],
                    'last_name' => explode(' ', $driver->name, 2)[1],
                    'created_by' => 1,
                ]);
            } else {
                $user->profile()->create([
                    'created_by' => 1,
                ]);
            }


        });
    }
}
