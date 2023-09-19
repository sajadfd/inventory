<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{

    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->first()->id,
            'title' => fake('ar_SA')->paragraph(1),
            'body' => fake('ar_SA')->paragraph(3),
            'is_seen' => rand(0, 1),
            'type' => $this->faker->randomElement(NotificationType::getAllValues()),
            'created_by'=>1,
        ];
    }
}
