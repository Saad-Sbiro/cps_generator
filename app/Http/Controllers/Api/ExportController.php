<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExportDocument;
use App\Models\Projet;
use App\Services\BrdExportService;
use App\Services\CpsExportService;
use App\Services\ExportStorageService;
use App\Services\RcExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        private CpsExportService $cpsService,
        private RcExportService $rcService,
        private BrdExportService $brdService,
        private ExportStorageService $storageService,
    ) {}

    public function exportCps(Projet $projet): JsonResponse
    {
        Gate::authorize('view', $projet);

        return $this->createExportResponse(
            $projet,
            'CPS_DOCX',
            $this->cpsService->generate($projet),
            'CPS généré avec succès',
        );
    }

    public function exportRc(Projet $projet): JsonResponse
    {
        Gate::authorize('view', $projet);

        return $this->createExportResponse(
            $projet,
            'RC_DOCX',
            $this->rcService->generate($projet),
            'RC généré avec succès',
        );
    }

    public function exportBrd(Projet $projet): JsonResponse
    {
        Gate::authorize('view', $projet);

        return $this->createExportResponse(
            $projet,
            'BRD_XLSX',
            $this->brdService->generate($projet),
            'BRD Excel généré avec succès',
        );
    }

    public function download(ExportDocument $export): BinaryFileResponse|StreamedResponse
    {
        return $this->storageService->download($export);
    }

    public function listExports(Projet $projet): JsonResponse
    {
        Gate::authorize('view', $projet);

        return response()->json($projet->exports()->latest()->get());
    }

    public function destroy(ExportDocument $export): JsonResponse
    {
        // Verify the user can access the project this export belongs to
        $projet = $export->projet;
        if ($projet) {
            Gate::authorize('update', $projet);
        }

        $this->storageService->delete($export);
        $export->delete();

        return response()->json(['message' => 'Fichier supprimé avec succès']);
    }

    private function createExportResponse(
        Projet $projet,
        string $type,
        string $localPath,
        string $message,
    ): JsonResponse {
        $doc = $this->storageService->store($projet, $type, $localPath);

        return response()->json([
            'message' => $message,
            'filename' => $doc->filename,
            'download_url' => route('exports.download', $doc->id, false),
            'export' => $doc,
        ]);
    }
}
