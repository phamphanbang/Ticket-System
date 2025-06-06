<?php

namespace App\Http\Controllers;

use App\Services\AttachmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
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
