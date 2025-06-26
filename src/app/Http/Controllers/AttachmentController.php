<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostAttachmentRequest;
use App\Models\Attachment;
use App\Services\AttachmentService;
use App\Services\FileConversionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
  use ApiResponse;
  public function __construct(
    protected AttachmentService $attachmentService,
    protected FileConversionService $fileConversionService
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

  public function show(Request $request, string $attachmentId)
  {
    $attachment = $this->attachmentService->getAttachmentById($attachmentId);
    $fullPath = $attachment->file_path . '/' . $attachment->file_name;

    if (!Storage::exists($fullPath)) {
      return $this->error('File not found', 404);
    }

    $extension = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));

    if ($this->fileConversionService->shouldConvertToPdf($extension)) {
      $pdfPath = $this->fileConversionService->getPdfPath($fullPath);

      if (!Storage::exists($pdfPath)) {
        $pdfPath = $this->fileConversionService->convertToPdf($fullPath);

        if (!$pdfPath || !Storage::exists($pdfPath)) {
          return $this->error('PDF conversion failed', 500);
        }
      }

      return response()->file(Storage::path($pdfPath), [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . pathinfo($attachment->file_name, PATHINFO_FILENAME) . '.pdf"',
      ]);
    }

    // Non-docx/xlsx: return original file as stream
    $mimeType = Storage::mimeType($fullPath);
    $stream = Storage::readStream($fullPath);

    if (!$stream) {
      return $this->error('Unable to read file', 500);
    }

    return response()->stream(function () use ($stream) {
      fpassthru($stream);
      fclose($stream);
    }, 200, [
      'Content-Type' => $mimeType,
      'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
    ]);
  }

  public function download(Request $request, string $attachmentId)
  {
    $attachment = $this->attachmentService->getAttachmentById($attachmentId);

    return Storage::disk('local')->download(
      $attachment->file_path . '/' . $attachment->file_name,
      $attachment->file_name,
      ['Content-Type' => $attachment->content_type]
    );
  }
}
