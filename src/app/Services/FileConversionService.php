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
  public function shouldConvertToPdf(string $extension): bool
  {
    return in_array(strtolower($extension), $this->supportedExtensions);
  }

  public function getPdfPath(string $fullPath): string
  {
    $pattern = '/\.(' . implode('|', array_map('preg_quote', $this->supportedExtensions)) . ')$/i';
    return preg_replace($pattern, '.pdf', $fullPath);
  }
  public function convertToPdf(string $path): ?string
  {
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    if (!$this->shouldConvertToPdf($extension)) {
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
    $pdfPath = $this->getPdfPath($path);
    Storage::put($pdfPath, $response->body());

    return $pdfPath;
  }
}
