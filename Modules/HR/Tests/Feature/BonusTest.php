<?php
declare(strict_types=1);

namespace Modules\HR\Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Modules\HR\Entities\Bonus;
use Modules\HR\Entities\Contract;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class, TestCase::class);

beforeEach(function () {
    Sanctum::actingAs(User::first());
    \Date::setTestNow('2023-06-01 10:00:00');
});

test('store bonus', function () {
    $contract = Contract::factory()->sample1()->createOne();
    $res = post('api/bonuses', [
        'contract_id'=>$contract->id,
        'price'=>1000,
        'date'=>'2023-06-01 12:00',
        'notes'=>'hi'
    ]);
    $res->assertStatus(200);
});
