<?php

namespace Modules\HR\Enums;

enum PaymentMethodEnum: string
{
   case Yearly = "yearly";
   case Monthly = "monthly";
   case Weekly = "weekly";
   case Daily = "daily";
   public static function getAllValues()
   {
      return array_column(PaymentMethodEnum::cases(), 'value');
   }
}
