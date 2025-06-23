<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DashboardController
{
  use ApiResponse;
  public function __construct(
    protected DashboardService $dashboardService
  ) {}

  // public function index(Request $request)
  // {
  //   $range = $request->get('range') ?? 'last_7_days';
  //   return $this->success(
  //     [
  //       'summary' => $this->dashboardService->getSummary(),
  //       'grouped' => $this->dashboardService->getStatsGroupedByDate($range)
  //     ]
  //   );
  // }

  public function summary(Request $request)
  {
    return $this->success(
      $this->dashboardService->getSummary()
    );
  }

  public function statsUserGroupedByDate(Request $request)
  {
    $range = $request->get('range') ?? 'last_7_days';
    return $this->success([
      'as_holder' => [
        'stat' => $this->dashboardService->getHolderStatsGroupedByDate($range),
        'avg' => $this->dashboardService->getHolderResolutionAverage()
      ],
      'as_staff' => [
        'stat' => $this->dashboardService->getStaffStatsGroupedByDate($range),
        'avg' => $this->dashboardService->getStaffResolutionAverage()
      ]
    ]);
  }

  public function statsAdminGroupedByDate(Request $request)
  {
    $range = $request->get('range') ?? 'last_7_days';
    return $this->success([
      'stat' => $this->dashboardService->getAdminStatsGroupedByDate($range),
      'avg' => $this->dashboardService->getAdminResolutionAverage()
    ]);
  }
}
