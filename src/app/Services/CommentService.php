<?php

namespace App\Services;

use App\Constants\PaginateConstant;
use App\Constants\UserRoles;
use App\Events\CommentCreated;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Validators\TicketValidator;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CommentService
{
  public function __construct(
    protected TicketService $ticketService,
    protected AttachmentService $attachmentService
  ) {
    // Constructor to inject TicketMailService dependency
  }

  public function index(string $ticketId, array $filters = [])
  {
    $query = TicketComment::with('attachments', 'user')->where('ticket_id', $ticketId);

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
    TicketValidator::checkAuthorization($ticket);

    return $comment;
  }

  public function store(string $ticketId, array $data): TicketComment
  {
    $ticket = Ticket::where('id', $ticketId)->first();
    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkAuthorization($ticket);

    $comment = DB::transaction(function () use ($data, $ticket) {
      $comment = TicketComment::create($data);

      if (!empty($data['attachments'])) {
        foreach ($data['attachments'] as $file) {
          $this->attachmentService->saveAttachment($file, $comment->id, $ticket->id);
        }
      }

      return $comment;
    });
    event(new CommentCreated($comment));
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
    $comment = DB::transaction(function () use ($comment, $data) {
      $comment->update($data);

      if (!empty($data['attachments'])) {
        foreach ($data['attachments'] as $file) {
          $this->attachmentService->saveAttachment($file, $comment->id, $comment->ticket->id);
        }
      }

      return $comment;
    });

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
}
