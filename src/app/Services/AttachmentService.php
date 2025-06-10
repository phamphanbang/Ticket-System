<?php

namespace App\Services;

use App\Constants\UserRoles;
use App\Events\AttachmentCreated;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Validators\TicketValidator;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AttachmentService
{
  public function __construct(
  ) {}

  public function saveAttachment(UploadedFile $file, string $comment_id, string $ticket_id)
  {
    $fileName = $file->getClientOriginalName();
    $fileExtension = $file->getClientOriginalExtension();
    $contentType = $file->getMimeType();
    $fileSize = $file->getSize();

    $filePath = $file->store("tickets/{$ticket_id}");
    $attachment = Attachment::create([
      'ticket_id' => $ticket_id,
      'comment_id' => $comment_id,
      'file_name' => $fileName,
      'file_extension' => $fileExtension,
      'file_path' => $filePath,
      'file_size' => $fileSize,
      'content_type' => $contentType
    ]);
    event(new AttachmentCreated($attachment));
    return $attachment;
  }

  public function deleteAttachment(string $attachmentId): bool
  {
    $attachment = Attachment::with('comment', 'ticket')->findOrFail($attachmentId);
    $comment = $attachment->comment;
    $ticket = $attachment->ticket;
    if (
      Auth::user()->id !== $comment->user_id &&
      Auth::user()->role !== UserRoles::ADMIN->value &&
      Auth::user()->id !== $ticket->holder_id
    ) {
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
    $attachment = Attachment::with('comment','ticket')->findOrFail($attachmentId);
    $comment = $attachment->comment;
    $ticket = $comment->ticket;

    TicketValidator::checkAuthorization($ticket);

    if (!Storage::exists($attachment->file_path)) {
      throw new Exception('File not found', Response::HTTP_NOT_FOUND);
    }

    return $attachment;
  }

  public function getTicketAttachments(string $id): array
  {
    $ticket = Ticket::findOrFail($id);

    $attachments = $ticket->attachments()->get()->toArray();

    return $attachments;
  }
}
