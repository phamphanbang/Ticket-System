<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostAttachmentRequest;
use App\Models\Attachment;
use App\Services\AttachmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
  use ApiResponse;
  public function __construct(
    protected AttachmentService $attachmentService
  ) {}

  public function getTicketAttachments(string $id)
  {
    $attachments = $this->attachmentService->getTicketAttachments($id);

    return $this->success(
      $attachments,
      'Ticket attachments retrieved successfully'
    );
  }

  public function deleteAttachment(Request $request, string $attachmentId)
  {
    $this->attachmentService->deleteAttachment($attachmentId);
    return $this->success(
      __('messages.model_deleted', ['model' => 'Attachment'])
    );
  }

  public function uploadAttachment(PostAttachmentRequest $request, string $ticketId)
  {
    $validated = $request->validated();
    $attachment = $this->attachmentService->uploadAttachment($validated, $ticketId);
    return $this->success(
      $attachment,
      'Attachment uploaded successfully'
    );
  }

  public function show(Attachment $attachment)
  {
    $path = $attachment->file_path . '/' . $attachment->file_name;

    if (!Storage::exists($path)) {
      return $this->error('File not found', 404);
    }

    $file = Storage::get($path);
    $mime = Storage::mimeType($path);

    return Response::make($file, 200, [
      'Content-Type' => $mime,
      'Content-Disposition' => 'inline; filename="' . basename($attachment->file_name) . '"',
    ]);
  }

  public function download(Request $request, string $attachmentId)
  {
    $attachment = $this->attachmentService->download($attachmentId);

    return Storage::download(
      $attachment->file_path,
      $attachment->file_name,
      ['Content-Type' => $attachment->content_type]
    );
  }
}
