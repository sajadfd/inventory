<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\NotificaionResource;
use App\Models\Notification;
use App\Http\Requests\StoreNotificationRequest;
use App\Services\PaginatorService;

class NotificationController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Notification::class, 'notification');
    }


    public function unseenCount()
    {
        return ApiResponse::success(auth()->user()->notifications()->where('is_seen', false)->count());
    }

    public function index()
    {
        $response = ApiResponse::success(PaginatorService::from(auth()->user()->notifications()->orderBy('created_at','desc'), NotificaionResource::class));
        auth()->user()->notifications()->update(['is_seen' => true]);
        return $response;
    }

    public function store(StoreNotificationRequest $request)
    {
        $notification = Notification::create($request->validated());
        if ($request->boolean('push')) {
            $notification->pushNotification();
        }
        return ApiResponse::success(NotificaionResource::make($notification));
    }

    public function show(Notification $notification)
    {
        return ApiResponse::success(NotificaionResource::make($notification));
    }

    public function destroy(Notification $notification)
    {
        return ApiResponse::success($notification->delete());
    }

}
