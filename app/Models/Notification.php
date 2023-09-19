<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationType;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperNotification
 */
class Notification extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_id', 'title', 'body', 'image', 'is_seen', 'url', 'type'];

    protected $casts = [
        'is_seen' => 'boolean',
        'user_id' => 'integer',
        'type' => NotificationType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    //Method
    public function pushNotification(): bool
    {
        if (config('app.env') === 'testing' && !isset($GLOBALS['enablePushNotificationsInTests'])) {
            return true;
        }

        $serverToken = config('app.firebase_cloud_messaging_server_key');
        $ids = $this->user->tokens()->where('name', '!=', '')->pluck('name');
        $payload = [
            "registration_ids" => $ids,
            "notification" => [
                "title" => $this->title,
                "body" => $this->body,
                "sound" => "default" // required for sound on ios
            ],
        ];

        $headers = [
            'Authorization' => 'key=' . $serverToken,
            'Content-Type' => 'application/json',
        ];
        $http = Http::withHeaders($headers)->post('https://fcm.googleapis.com/fcm/send', $payload);
        \Log::debug($http->body());
        return $http->successful();
    }


    public static function send($title, $body, NotificationType $type, $target, $url, User $user): bool
    {
        $notification = new Notification();
        $notification->title = $title;
        $notification->body = $body;
        $notification->type = $type;
        $notification->target = $target;
        $notification->url = $url;
        $notification->user_id = $user->id;
        $notification->created_by = auth()->id() ?: 0;
        $notification->save();
        return $notification->pushNotification();
    }

}
