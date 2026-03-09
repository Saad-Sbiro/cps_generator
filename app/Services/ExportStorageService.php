<?php

namespace App\Services;

use App\Models\ExportDocument;
use App\Models\Projet;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportStorageService
{
    public function store(Projet $projet, string $type, string $localPath): ExportDocument
    {
        $disk = config('filesystems.exports', 'local');
        $filename = basename($localPath);
        $storagePath = "exports/{$projet->id}/{$filename}";

        $stream = fopen($localPath, 'r');

        if ($stream === false) {
            throw new RuntimeException('Impossible de lire le fichier généré.');
        }

        try {
            Storage::disk($disk)->put($storagePath, $stream);
        } finally {
            fclose($stream);

            if (file_exists($localPath)) {
                @unlink($localPath);
            }
        }

        return ExportDocument::create([
            'projet_id' => $projet->id,
            'type' => $type,
            'disk' => $disk,
            'filename' => $filename,
            'path' => $storagePath,
        ]);
    }

    public function download(ExportDocument $export): BinaryFileResponse|StreamedResponse
    {
        $mimeType = match ($export->type) {
            'BRD_XLSX' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        };

        if ($this->isLegacyLocalFile($export)) {
            if (!file_exists($export->path)) {
                abort(404, 'Fichier introuvable');
            }

            return response()->download($export->path, $export->filename, ['Content-Type' => $mimeType]);
        }

        $disk = $export->disk ?: 'local';

        if (!Storage::disk($disk)->exists($export->path)) {
            abort(404, 'Fichier introuvable');
        }

        return Storage::disk($disk)->download($export->path, $export->filename, ['Content-Type' => $mimeType]);
    }

    public function delete(ExportDocument $export): void
    {
        if ($this->isLegacyLocalFile($export)) {
            if (file_exists($export->path)) {
                @unlink($export->path);
            }

            return;
        }

        $disk = $export->disk ?: 'local';

        if (Storage::disk($disk)->exists($export->path)) {
            Storage::disk($disk)->delete($export->path);
        }
    }

    private function isLegacyLocalFile(ExportDocument $export): bool
    {
        if (($export->disk ?? 'local') !== 'local') {
            return false;
        }

        return preg_match('/^[A-Za-z]:[\\\\\/]/', $export->path) === 1
            || str_starts_with($export->path, DIRECTORY_SEPARATOR);
    }
}
