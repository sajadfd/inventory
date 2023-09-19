<?php

namespace App\Enums;

enum NotificationType
{
    case ProductDepletion;
    case CustomerRegistered;
    case OrderToDeliver;
    case OrderConfirmed;
    case OrderRequested;
    case OrderCanceled;

    case OrderFinished;
    case Other;

    case SalaryIssued;

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'name');
    }
}
