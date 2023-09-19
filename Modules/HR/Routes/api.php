<?php

use App\Http\Controllers\ApkVersionController;
use App\Http\Controllers\HelperController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\HR\Http\Controllers\AbsenceController;
use Modules\HR\Http\Controllers\AttendanceController;
use Modules\HR\Http\Controllers\BonusController;
use Modules\HR\Http\Controllers\ContractController;
use Modules\HR\Http\Controllers\EmployerController;
use Modules\HR\Http\Controllers\InstallmentController;
use Modules\HR\Http\Controllers\InstallmentPaymentController;
use Modules\HR\Http\Controllers\OffDateController;
use Modules\HR\Http\Controllers\OffWeekDayController;
use Modules\HR\Http\Controllers\PenaltyController;
use Modules\HR\Http\Controllers\SalaryController;
use Modules\HR\Http\Controllers\LoanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

if (config('app.env') !== 'production') {
    Route::get('helpers/migrate_fresh', [HelperController::class, 'migrateFresh']);
    Route::get('helpers/seed', [HelperController::class, 'seed']);
}


Route::middleware('auth:api')->get('/hr', function (Request $request) {
    return $request->user();
});

Route::get('salaries/calculate', [SalaryController::class, 'calculate']);

Route::group(['middleware' => 'auth:sanctum'], function () {

    //Contracts
    Route::put('contracts/{contract}/deactivate', [ContractController::class, 'deactivate']);
    Route::get('contracts/{contract}/preview_dues', [ContractController::class, 'previewDues']);


    Route::post('off_dates/many', [OffDateController::class, 'storeMany']);
    Route::post('off_week_days/many', [OffWeekDayController::class, 'storeMany']);

    Route::apiResources([
        'penalties' => PenaltyController::class,
        'bonuses' => BonusController::class,
        'employers' => EmployerController::class,
        'contracts' => ContractController::class,
        'absences' => AbsenceController::class,
        'attendances' => AttendanceController::class,
        'off_dates' => OffDateController::class,
        'off_week_days' => OffWeekDayController::class,
        'loans' => LoanController::class,
        'installments' => InstallmentController::class,
        'installment_payments' => InstallmentPaymentController::class,

    ]);

    //Salaries
    Route::get('salaries', [SalaryController::class, 'index']);
    Route::post('salaries/pay_many', [SalaryController::class, 'payMany']);

    Route::post('salaries/{salary}/pay', [SalaryController::class, 'pay']);
    Route::get('salaries/{salary}', [SalaryController::class, 'show']);
    Route::delete('salaries/{salary}', [SalaryController::class, 'destroy']);

    Route::get('loans/loan_installments/{loan}', [LoanController::class, 'loanInstallments']);

});
