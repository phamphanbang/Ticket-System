<?php

namespace App\Services;

use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardService
{
  public function getSummary(): array
  {
    $user = Auth::user();

    $inProgressStatus = [
      TicketStatus::IN_PROGRESS->value,
      TicketStatus::ASSIGNED->value,
      TicketStatus::PENDING->value,
    ];

    $completeStatus = [
      TicketStatus::COMPLETE->value,
      TicketStatus::ARCHIVED->value,
    ];

    // Admins see everything once under a combined role
    if ($user->role === UserRoles::ADMIN->value) {
      $baseQuery = Ticket::query();

      return [
        'as_admin' => [
          'total' => $baseQuery->count(),
          'new' => (clone $baseQuery)->where('status', TicketStatus::NEW->value)->count(),
          'in_progress' => (clone $baseQuery)->whereIn('status', $inProgressStatus)->count(),
          'complete' => (clone $baseQuery)->whereIn('status', $completeStatus)->count(),
        ],
      ];
    }

    // Holder stats
    $holderQuery = Ticket::where('holder_id', $user->id);

    // Staff stats (from audit logs)
    $staffQuery = Ticket::whereIn('id', function ($subquery) use ($user) {
      $subquery->select('ticket_id')->distinct()
        ->from('ticket_audit_logs')
        ->where('staff_id', $user->id);
    });

    return [
      'as_holder' => [
        'total' => $holderQuery->count(),
        'new' => (clone $holderQuery)->where('status', TicketStatus::NEW->value)->count(),
        'in_progress' => (clone $holderQuery)->whereIn('status', $inProgressStatus)->count(),
        'complete' => (clone $holderQuery)->whereIn('status', $completeStatus)->count(),
      ],
      'as_staff' => [
        'total' => $staffQuery->count(),
        'new' => (clone $staffQuery)->where('status', TicketStatus::NEW->value)->count(),
        'in_progress' => (clone $staffQuery)->whereIn('status', $inProgressStatus)->count(),
        'complete' => (clone $staffQuery)->whereIn('status', $completeStatus)->count(),
      ],
    ];
  }

  private function getStartDateFromRange(string $range): Carbon
  {
    return match ($range) {
      'last_24_hours' => Carbon::now()->subHours(24)->startOfDay(),
      'last_7_days' => Carbon::now()->subDays(6)->startOfDay(),
      'last_30_days' => Carbon::now()->subDays(29)->startOfDay(),
      'last_90_days' => Carbon::now()->subDays(89)->startOfDay(),
      default => Carbon::now()->subDays(6)->startOfDay(),
    };
  }

  public function getAdminStatsGroupedByDate(string $range = 'last_7_days') {
    $startDate = $this->getStartDateFromRange($range);

    $query = Ticket::select([
      DB::raw('DATE(created_at) as date'),
      DB::raw('COUNT(*) as total'),
      DB::raw("SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new"),
      DB::raw("SUM(CASE WHEN status IN ('in_progress', 'assigned', 'pending') THEN 1 ELSE 0 END) as in_progress"),
      DB::raw("SUM(CASE WHEN status IN ('complete', 'archived') THEN 1 ELSE 0 END) as complete"),
    ])
      ->where('created_at', '>=', $startDate);

    return $query->groupBy(DB::raw('DATE(created_at)'))
      ->orderBy('date')
      ->get()
      ->toArray();
  }

  public function getHolderStatsGroupedByDate(string $range = 'last_7_days'): array
  {
    $user = Auth::user();
    $startDate = $this->getStartDateFromRange($range);

    $query = Ticket::select([
      DB::raw('DATE(created_at) as date'),
      DB::raw('COUNT(*) as total'),
      DB::raw("SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new"),
      DB::raw("SUM(CASE WHEN status IN ('in_progress', 'assigned', 'pending') THEN 1 ELSE 0 END) as in_progress"),
      DB::raw("SUM(CASE WHEN status IN ('complete', 'archived') THEN 1 ELSE 0 END) as complete"),
    ])
      ->where('created_at', '>=', $startDate)
      ->where('holder_id', $user->id);

    return $query->groupBy(DB::raw('DATE(created_at)'))
      ->orderBy('date')
      ->get()
      ->toArray();
  }

  public function getStaffStatsGroupedByDate(string $range = 'last_7_days'): array
  {
    $user = Auth::user();
    $startDate = $this->getStartDateFromRange($range);

    $query = Ticket::select([
      DB::raw('DATE(created_at) as date'),
      DB::raw('COUNT(*) as total'),
      DB::raw("SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new"),
      DB::raw("SUM(CASE WHEN status IN ('in_progress', 'assigned', 'pending') THEN 1 ELSE 0 END) as in_progress"),
      DB::raw("SUM(CASE WHEN status IN ('complete', 'archived') THEN 1 ELSE 0 END) as complete"),
    ])
      ->where('created_at', '>=', $startDate)
      ->whereIn('id', function ($subquery) use ($user) {
        $subquery->select('ticket_id')
          ->distinct()
          ->from('ticket_audit_logs')
          ->where('staff_id', $user->id);
      });

    return $query->groupBy(DB::raw('DATE(created_at)'))
      ->orderBy('date')
      ->get()
      ->toArray();
  }

  public function getHolderResolutionAverage(string $range = 'last_7_days'): array
  {
    $user = Auth::user();
    $startDate = $this->getStartDateFromRange($range);
    $durations = Ticket::where('holder_id', $user->id)
      ->whereIn('status', ['complete', 'archived'])
      ->whereNotNull('updated_at')
      ->where('created_at', '>=', $startDate)
      ->get()
      ->map(fn($ticket) => Carbon::parse($ticket->updated_at)->diffInSeconds(Carbon::parse($ticket->created_at)))
      ->filter()
      ->toArray();

    $avg = count($durations) > 0 ? array_sum($durations) / count($durations) : 0;

    return [
      'avg_seconds' => round($avg),
      'avg_hms' => gmdate('H:i:s', (int) $avg),
    ];
  }

  public function getStaffResolutionAverage(string $range = ''): array
  {
    $user = Auth::user();
    $startDate = $this->getStartDateFromRange($range);
    $durations = DB::table('ticket_audit_logs as logs')
    ->select('logs.start_at', 'logs.end_at')
      ->where('logs.staff_id', $user->id)
      ->where('logs.start_at', '>=', $startDate)
      ->get()
      ->map(fn($row) => Carbon::parse($row->end_at)
      ->diffInSeconds(Carbon::parse($row->start_at)))
      ->filter()
      ->toArray();

    $avg = count($durations) > 0 ? array_sum($durations) / count($durations) : 0;

    return [
      'avg_seconds' => round($avg),
      'avg_hms' => gmdate('H:i:s', (int) $avg),
    ];
  }

  public function getAdminResolutionAverage(string $range = ''): array
  {
    $startDate = $this->getStartDateFromRange($range);
    $durations = Ticket::whereIn('status', ['complete', 'archived'])
      ->whereNotNull('updated_at')
      ->where('created_at', '>=', $startDate)
      ->get()
      ->map(fn($ticket) => Carbon::parse($ticket->updated_at)->diffInSeconds(Carbon::parse($ticket->created_at)))
      ->filter()
      ->toArray();

    $avg = count($durations) > 0 ? array_sum($durations) / count($durations) : 0;

    return [
      'avg_seconds' => round($avg),
      'avg_hms' => gmdate('H:i:s', (int) $avg),
    ];
  }
}
