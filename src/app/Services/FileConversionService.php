<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileConversionService
{
  protected array $supportedExtensions = [
    'docx',
    'doc',
    'xlsx',
    'xls',
    'pptx',
    'ppt',
    'txt',
    'csv',
  ];
  public function convertToPdf(string $path): ?string
  {
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    if (!in_array($extension, $this->supportedExtensions)) {
      Log::warning("File extension '{$extension}' is not supported for PDF conversion.");
      return null;
    }

    $filename = basename($path);
    $diskPath = Storage::path($path);

    $handle = @fopen($diskPath, 'r');
    if (!$handle) {
      Log::error("Unable to open file for Gotenberg: $diskPath");
      return null;
    }

    $response = Http::attach('files', $handle, $filename)
      ->post('http://gotenberg:3000/forms/libreoffice/convert');

    fclose($handle);

    if (!$response->ok()) {
      Log::error('Gotenberg conversion failed', ['body' => $response->body()]);
      return null;
    }

    // Save the PDF
    $pdfPath = preg_replace('/\.(docx|xlsx|doc|xls|pptx|ppt|txt|csv)$/i', '.pdf', $path);
    Storage::put($pdfPath, $response->body());

    return $pdfPath;
  }
}
