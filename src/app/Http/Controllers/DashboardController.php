<?php  

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DashboardController {
  use ApiResponse;
  public function __construct(
    protected DashboardService $dashboardService
  ){}

  public function index(Request $request)
  {
    $range = $request->get('range');
    return $this->success(
      [
        'summary' => $this->dashboardService->getSummary(),
        'grouped' => $this->dashboardService->getStatsGroupedByDate($range)
      ]
    );
  }
}