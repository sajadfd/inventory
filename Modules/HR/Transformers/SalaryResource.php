<?php

namespace Modules\HR\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            ...parent::toArray($request),
            'contract' => ContractResource::make($this->whenLoaded('contract')),
            'absences' => AbsenceResource::collection($this->whenLoaded('absences')),
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
            'off_dates' => OffDateResource::collection($this->whenLoaded('offDates')),
            'off_week_days' => OffWeekDayResource::collection($this->whenLoaded('offWeekDays')),
            'bonuses' => BonusResource::collection($this->whenLoaded('bonuses')),
            'penalties' => PenaltyResource::collection($this->whenLoaded('penalties')),
        ];
    }
}
