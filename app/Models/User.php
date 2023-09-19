<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\NotificationType;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements Auditable
{
    use HasApiTokens, HasFactory, HasRoles, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'type' => UserType::class,
        'options' => 'array'
    ];

    public function cart()
    {
        return $this->hasOne(Cart::class, 'user_id');
    }

    public function scopeOption($query, $id, $value)
    {
        $option = $query->where('id', $id)->first();
        try {
            return json_decode($option->options)->$value;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function Password(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                return Hash::make($value);
            }
        );
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    //Methods

    public function notify($title, $body, NotificationType $type, $target = null, $url = ""): bool
    {
        return Notification::send($title, $body, $type, $target, $url, $this);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

}
