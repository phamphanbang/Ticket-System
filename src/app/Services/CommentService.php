<?php

namespace App\Services;

use App\Constants\PaginateConstant;
use App\Constants\UserRoles;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Traits\HasPagination;
use App\Validators\TicketValidator;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class CommentService
{
  public function __construct(
    protected TicketService $ticketService
  ) {
    // Constructor to inject TicketMailService dependency
  }

  public function index(string $ticketId, array $filters = [])
  {
    $query = TicketComment::where('ticket_id', $ticketId);

    $perPage = $filters['limit'] ?? PaginateConstant::DEFAULT_PER_PAGE->value;
    $page = $filters['page'] ?? PaginateConstant::DEFAULT_PAGE->value;

    $paginator = $query->paginate($perPage, ['*'], 'page', $page);

    return [
      'data' => $paginator->items(),
      'pagination' => [
        'page' => $paginator->currentPage(),
        'perPage' => $paginator->perPage(),
        'total' => $paginator->total(),
      ]
    ];
  }

  public function show(string $ticketId, string $id): TicketComment
  {
    $comment = TicketComment::where('id', $id)
      ->where('ticket_id', $ticketId)
      ->firstOrFail();

    $ticket = $comment->ticket;
    TicketValidator::checkTicketExists($ticket);
    $this->checkAuthorization($ticket);

    return $comment;
  }

  public function store(string $ticketId, array $data): TicketComment
  {
    $ticket = Ticket::where('id', $ticketId)->first();
    TicketValidator::checkTicketExists($ticket);
    $this->checkAuthorization($ticket);

    $comment = TicketComment::create($data);
    return $comment;
  }

  public function update(string $id, array $data): TicketComment
  {
    $comment = TicketComment::where('id', $id)->firstOrFail();

    if ($comment->user_id !== auth()->id() && auth()->user()->role !== UserRoles::ADMIN->value) {
      throw new Exception(
        'You are not authorized to update this comment',
        Response::HTTP_FORBIDDEN
      );
    }

    $comment->update($data);
    return $comment->fresh();
  }

  public function destroy(string $id): bool
  {
    $comment = TicketComment::where('id', $id)->firstOrFail();

    if ($comment->user_id !== auth()->id() && auth()->user()->role !== UserRoles::ADMIN->value) {
      throw new Exception(
        'You are not authorized to delete this comment',
        Response::HTTP_FORBIDDEN
      );
    }

    $comment->delete();
    return true;
  }


  public function checkAuthorization(Ticket $ticket)
  {
    TicketValidator::checkTicketExists($ticket);
    $userId = auth()->id();
    $isAuthorized = $ticket->logs()
      ->where(function ($query) use ($userId) {
        $query->where('holder_id', $userId)
          ->orWhere('staff_id', $userId);
      })
      ->exists();

    if (!$isAuthorized) {
      throw new Exception(
        'You are not authorized to view this comment',
        Response::HTTP_FORBIDDEN
      );
    }
  }
}
