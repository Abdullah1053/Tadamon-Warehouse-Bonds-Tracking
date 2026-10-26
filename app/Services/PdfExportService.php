<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class PdfExportService
{
    /**
     * Common paths to headless browser executables on Windows and Linux.
     */
    protected array $candidateBinaries = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        '/usr/bin/google-chrome',
        '/usr/bin/google-chrome-stable',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
    ];

    /**
     * Generate PDF from an HTML string and return the absolute path to the generated PDF.
     *
     * @param string $html Full HTML markup to render
     * @param string $preferredEngine 'auto', 'chrome', or 'dompdf'
     * @return string Path to temporary PDF file
     * @throws \RuntimeException
     */
    public function generatePdfFromHtml(string $html, string $preferredEngine = 'auto'): string
    {
        $tempPdfPath = tempnam(sys_get_temp_dir(), 'audit_report_') . '.pdf';

        if ($preferredEngine !== 'dompdf') {
            $browserBinary = $this->findBrowserBinary();
            if ($browserBinary) {
                try {
                    $this->renderWithHeadlessBrowser($browserBinary, $html, $tempPdfPath);
                    if (file_exists($tempPdfPath) && filesize($tempPdfPath) > 500) {
                        return $tempPdfPath;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Headless browser PDF generation failed, falling back to DomPDF: ' . $e->getMessage());
                }
            }
        }

        // Fallback or explicit DomPDF rendering
        return $this->renderWithDomPdf($html, $tempPdfPath);
    }

    /**
     * Locate an available Chromium / Edge / Chrome binary.
     */
    protected function findBrowserBinary(): ?string
    {
        foreach ($this->candidateBinaries as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }
        return null;
    }

    /**
     * Render HTML to PDF using Chrome/Edge headless CLI.
     */
    protected function renderWithHeadlessBrowser(string $binaryPath, string $html, string $outputPath): void
    {
        $tempHtmlPath = tempnam(sys_get_temp_dir(), 'audit_html_') . '.html';
        file_put_contents($tempHtmlPath, $html);

        $fileUrl = 'file:///' . str_replace('\\', '/', $tempHtmlPath);

        // Arguments for modern Chromium engines (Chrome 110+, Edge)
        $process = new Process([
            $binaryPath,
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--no-pdf-header-footer',
            '--run-all-compositor-stages-before-draw',
            '--print-to-pdf=' . $outputPath,
            $fileUrl,
        ]);

        $process->setTimeout(30);
        $process->run();

        // Clean up temp HTML file
        @unlink($tempHtmlPath);

        if (!$process->isSuccessful() && !file_exists($outputPath)) {
            throw new \RuntimeException("Browser process failed: " . $process->getErrorOutput());
        }
    }

    /**
     * Render HTML to PDF using DomPDF.
     */
    protected function renderWithDomPdf(string $html, string $outputPath): string
    {
        $domPdf = Pdf::loadHTML($html);
        $domPdf->setPaper('a4', 'portrait');
        $domPdf->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);
        
        $output = $domPdf->output();
        file_put_contents($outputPath, $output);

        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException("DomPDF failed to generate valid output file.");
        }

        return $outputPath;
    }
}
