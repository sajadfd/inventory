<?php 

namespace Modules\HR\Enums;

enum SalaryCurrencyEnum : string
{
   case Usd = "usd"; 
   case Iqd = "iqd";

   public static function getAllValues(){
      return array_column(SalaryCurrencyEnum::cases(), 'value');
   } 
}