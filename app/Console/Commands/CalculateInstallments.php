<?php

namespace App\Console\Commands;

use Modules\HR\Services\PenaltyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\HR\Entities\Installment;

class CalculateInstallments extends Command
{
    protected $signature = 'calculate:installments';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(PenaltyService $penaltyService)
    {
        $installments = Installment::whereNull('penalty_id')
            ->where('due_date', '>', Carbon::now())
            ->with('loan.contract.employer')->get();
        if ($installments)
            foreach ($installments as $installment) {
                $penaltyService->createPenaltyAndUpdateInstallment($installment);
            }
    }
}
