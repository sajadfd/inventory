<?php

namespace App\Enums;

enum CartEnum:string {
   case Open= "open";
   case ConfirmedByUser="confirmed-by-user";
   case confirmedByAdmin="confirmed-by-admin";
   case ProcessedByUser="processed-by-user";
   case ProcessedByAdmin="processed-by-admin";
   case Finished="finished";
   case CanceledByUser="canceled-by-user";
   case CanceledByAdmin="canceled-by-admin";


}// /CartEnum
