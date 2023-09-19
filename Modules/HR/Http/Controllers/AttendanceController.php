<?php

namespace Modules\HR\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Modules\HR\Entities\Attendance;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Http\Requests\StoreAttendanceRequest;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Transformers\AttendanceResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AttendanceController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Attendance::class, 'attendance');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Attendance::query()->with('contract.employer'), AttendanceResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder->allowedFilters([
                'contract.employer.name',
                AllowedFilter::exact('contract.id'),
                AllowedFilter::exact('contract.employer.id'),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('start_date', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('end_date', '<=', $value);
                })
            ]);
        }));
    }

    public function store(StoreAttendanceRequest $request)
    {
        $data = $request->validated();
        $attendance = Attendance::query()->create($data);
        $attendance->load('contract.employer');
        return ApiResponse::success(AttendanceResource::make($attendance));
    }

    public function show(Attendance $attendance)
    {
        $attendance->load('contract.employer');
        return ApiResponse::success(AttendanceResource::make($attendance));
    }

    public function update(StoreAttendanceRequest $request, Attendance $attendance)
    {
        $data = $request->validated();
        $attendance->update($data);
        $attendance->load('contract.employer');
        return ApiResponse::success(AttendanceResource::make($attendance));
    }

    public function destroy(Attendance $attendance)
    {
        if ($attendance->salaries()->exists()) {
            throw ValidationException::withMessages([__('Record is used, cannot delete')]);
        }
        return ApiResponse::success($attendance->delete());
    }
}
