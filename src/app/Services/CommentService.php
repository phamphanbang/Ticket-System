<?php

namespace App\Services;

use App\Constants\PaginateConstant;
use App\Constants\UserRoles;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Validators\TicketValidator;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    $query = TicketComment::with('attachments','user')->where('ticket_id', $ticketId);

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

    $comment = DB::transaction(function () use ($data) {
      $comment = TicketComment::create($data);

      if (!empty($data['attachments'])) {
        foreach ($data['attachments'] as $file) {
          $this->saveAttachment($file, $comment);
        }
      }

      return $comment;
    });
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
          $this->saveAttachment($file, $comment);
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

  private function saveAttachment(UploadedFile $file, TicketComment $comment): Attachment
  {
    $fileName = $file->getClientOriginalName();
    $fileExtension = $file->getClientOriginalExtension();
    $contentType = $file->getMimeType();
    $fileSize = $file->getSize();

    $filePath = $file->store("comments/{$comment->id}");
    return $comment->attachments()->create([
      'file_name' => $fileName,
      'file_extension' => $fileExtension,
      'file_path' => $filePath,
      'file_size' => $fileSize,
      'content_type' => $contentType
    ]);
  }

  public function deleteAttachment(string $attachmentId): bool
  {
    $attachment = Attachment::with('comment')->findOrFail($attachmentId);
    $comment = $attachment->comment;

    if ($comment->user_id !== auth()->id() && auth()->user()->role !== UserRoles::ADMIN->value) {
      throw new Exception(
        'You are not authorized to delete this attachment',
        Response::HTTP_FORBIDDEN
      );
    }

    return DB::transaction(function () use ($attachment) {
      if (Storage::exists($attachment->file_path)) {
        Storage::delete($attachment->file_path);
      }
      return $attachment->delete();
    });
  }

  public function download(string $attachmentId)
  {
    $attachment = Attachment::with('comment.ticket')->findOrFail($attachmentId);
    $comment = $attachment->comment;
    $ticket = $comment->ticket;

    $this->checkAuthorization($ticket);

    if (!Storage::exists($attachment->file_path)) {
      throw new Exception('File not found', Response::HTTP_NOT_FOUND);
    }

    return $attachment;
  }


  public function checkAuthorization(Ticket $ticket)
  {
    TicketValidator::checkTicketExists($ticket);
    $user = auth()->user();
    $isAuthorized = $ticket->logs()
      ->where(function ($query) use ($user) {
        $query->where('holder_id', $user->id)
          ->orWhere('staff_id', $user->id);
      })
      ->exists();

    if (!$isAuthorized && $user->role !== UserRoles::ADMIN->value) {
      throw new Exception(
        'You are not authorized to view this comment',
        Response::HTTP_FORBIDDEN
      );
    }
  }
}
