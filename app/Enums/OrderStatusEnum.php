<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case ConfirmedByCustomer = 'confirmed_by_customer';
    case ConfirmedByAdmin = 'confirmed_by_admin';

    //If Inventory Sale
    case AwaitingCar = 'awaiting_car';
    case ProcessingCar = 'processing_car';

    //If Store Sale without delivery
    case ReadyToDeliver = 'ready_to_deliver';

    //If store Sale with delivery
    case AssignedToDriver = 'assigned_to_driver';
    case DepartedWithDriver = 'departed_with_driver';

    //If canceled
    case CanceledByCustomer = 'canceled_by_customer';
    case CanceledByAdmin = 'canceled_by_admin';

    //Finished
    case Finished = 'finished';

    public static function getAllValues(): array
    {
        return array_column(OrderStatusEnum::cases(), 'value');
    }
}
