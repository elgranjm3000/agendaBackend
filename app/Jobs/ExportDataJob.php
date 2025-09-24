<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Services\ExportService;

class ExportDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $exportType,
        public string $format,
        public array $filters,
        public int $companyId,
        public int $userId
    ) {
        //
    }

    public function handle(ExportService $exportService): void
    {
        $filename = $exportService->export(
            $this->exportType,
            $this->format,
            $this->filters,
            $this->companyId
        );

        // Here you would typically notify the user that the export is ready
        // For example, send an email with the download link
    }
}
