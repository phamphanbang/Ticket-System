<?php

namespace App\Services;

use App\Constants\UserRoles;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardService
{
  public function getSummary(): array
  {
    $query = Ticket::query();
    $user = Auth::user();
    if($user->role !== UserRoles::ADMIN->value) {
      $query->where('holder_id',$user->id)
        ->orWhere('staff_id',$user->id);
    }
    return [
      'total' => (clone $query)->count(),
      'new' => (clone $query)->where('status', 'open')->count(),
      'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
      'completed' => (clone $query)->where('status', 'completed')->count(),
    ];
  }

  public function getStatsGroupedByDate(string $range = 'last_7_days'): array
  {
    $startDate = match ($range) {
      'today' => Carbon::today(),
      'last_7_days' => Carbon::now()->subDays(6)->startOfDay(),
      'last_month' => Carbon::now()->subMonth()->startOfMonth(),
      'this_month' => Carbon::now()->startOfMonth(),
      default => Carbon::now()->subDays(6)->startOfDay(),
    };

    return Ticket::select([
      DB::raw('DATE(created_at) as date'),
      DB::raw('COUNT(*) as total'),
      DB::raw("SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open"),
      DB::raw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress"),
      DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
    ])
      ->where('created_at', '>=', $startDate)
      ->groupBy(DB::raw('DATE(created_at)'))
      ->orderBy('date')
      ->get()
      ->toArray();
  }
}
