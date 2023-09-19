<?php

namespace Modules\HR\Enums;

enum TrackByEnum : string
{
   case Absences = "absences";
   case Attendances = "attendances";

   public static function getAllValues(): array
   {
        return array_column(TrackByEnum::cases() , 'value');
   }
}
