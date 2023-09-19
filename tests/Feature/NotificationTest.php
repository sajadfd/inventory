<?php

use App\Enums\NotificationType;
use App\Http\Resources\NotificaionResource;
use App\Models\User;
use App\Models\Notification;
use Database\Factories\NotificationFactory;
use Database\Seeders\NotificationSeeder;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

beforeEach(function () {
    $user = User::first();
    Sanctum::actingAs($user);
    seed([NotificationSeeder::class]);
});

test('index works', function () {
    get('api/notifications')
        ->assertStatus(200)
        ->assertJsonPath('data.total', auth()->user()->notifications()->count());
});
test('show works', function () {
    $notification = NotificationFactory::new()->createOne();
    get('api/notifications/' . $notification->id)
        ->assertStatus(200);
});

test('create works', function (array $payload) {
    $user = User::find($payload['user_id']);
    $oldCount = $user->notifications()->count();
    post('api/notifications', $payload)
        ->assertStatus(200)
        ->assertJsonPath('data.created_by', auth()->user()->id)
        ->assertJsonPath('data.user_id', $user->id);

    expect($user->notifications()->count())->toBe($oldCount + 1);
})->with([
    fn() => NotificationFactory::new()->makeOne()->toArray(),
]);


test('notification type enum', function () {
    $notification = Notification::create([
        'title' => '123',
        'user_id' => 1,
        'body' => '123456',
        'type' => NotificationType::ProductDepletion,
    ]);


    expect(true)->toBe(true);
});
