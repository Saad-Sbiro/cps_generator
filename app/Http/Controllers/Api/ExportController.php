<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExportDocument;
use App\Models\Projet;
use App\Services\BrdExportService;
use App\Services\CpsExportService;
use App\Services\RcExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(
        private CpsExportService $cpsService,
        private RcExportService  $rcService,
        private BrdExportService $brdService,
    ) {}

    public function exportCps(Projet $projet): JsonResponse
    {
        $path     = $this->cpsService->generate($projet);
        $filename = basename($path);

        $doc = ExportDocument::create([
            'projet_id' => $projet->id,
            'type'      => 'CPS_DOCX',
            'filename'  => $filename,
            'path'      => $path,
        ]);

        return response()->json([
            'message'      => 'CPS généré avec succès',
            'filename'     => $filename,
            'download_url' => route('exports.download', $doc->id, false),
            'export'       => $doc,
        ]);
    }

    public function exportRc(Projet $projet): JsonResponse
    {
        $path     = $this->rcService->generate($projet);
        $filename = basename($path);

        $doc = ExportDocument::create([
            'projet_id' => $projet->id,
            'type'      => 'RC_DOCX',
            'filename'  => $filename,
            'path'      => $path,
        ]);

        return response()->json([
            'message'      => 'RC généré avec succès',
            'filename'     => $filename,
            'download_url' => route('exports.download', $doc->id, false),
            'export'       => $doc,
        ]);
    }

    public function exportBrd(Projet $projet): JsonResponse
    {
        $path     = $this->brdService->generate($projet);
        $filename = basename($path);

        $doc = ExportDocument::create([
            'projet_id' => $projet->id,
            'type'      => 'BRD_XLSX',
            'filename'  => $filename,
            'path'      => $path,
        ]);

        return response()->json([
            'message'      => 'BRD Excel généré avec succès',
            'filename'     => $filename,
            'download_url' => route('exports.download', $doc->id, false),
            'export'       => $doc,
        ]);
    }

    public function download(ExportDocument $export): BinaryFileResponse
    {
        if (!file_exists($export->path)) {
            abort(404, 'Fichier introuvable');
        }

        $mimeType = match ($export->type) {
            'BRD_XLSX' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        };

        return Response::download($export->path, $export->filename, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function listExports(Projet $projet): JsonResponse
    {
        return response()->json($projet->exports()->latest()->get());
    }

    public function destroy(ExportDocument $export): JsonResponse
    {
        if (file_exists($export->path)) {
            unlink($export->path);
        }
        $export->delete();

        return response()->json(['message' => 'Fichier supprimé avec succès']);
    }
}
