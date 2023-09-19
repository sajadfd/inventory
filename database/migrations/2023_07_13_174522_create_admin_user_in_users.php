<?php

use App\Enums\RoleEnum;
use App\Enums\UserType;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
//        if (env('APP_ENV') === 'local') {
        if (User::query()->where('username', 'admin')->doesntExist()) {
            User::disableAuditing();
            Profile::disableAuditing();

            \Illuminate\Support\Facades\Artisan::call('sync:roles');
            /** @var User $user */
            $user = User::query()->create([
                'username' => 'admin',
                'password' => '12345678',
                'phone' => '07801234567',
                'type' => UserType::SuperAdmin,
                'options' => "{}",
                'code' => "IQ"
            ]);;

//            auth()->login($user);

            $user->profile()->create([
                'first_name' => 'admin',
                'last_name' => 'admin',
                'national_identification_number' => '',
                'created_by'=>1,
            ]);
            $user->assignRole(RoleEnum::SuperAdmin);
        }
//        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_user_in_users');
    }
};
