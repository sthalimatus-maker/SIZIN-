<?php

namespace App\Services;

use Illuminate\Support\Collection;

class LeaveReportPdf
{
    private const PAGE_WIDTH = 842;
    private const PAGE_HEIGHT = 595;
    private const LEFT = 52;
    private const COLUMNS = [65, 115, 52, 100, 200, 75, 65, 65];
    private const HEADERS = ['Tanggal', 'Nama siswa', 'Kelas', 'Jenis izin', 'Tujuan', 'Status', 'Keluar', 'Kembali'];

    public function generate(Collection $requests, array $filters): string
    {
        $pages = [];
        $content = $this->newPage($filters);
        $top = 119;

        foreach ($requests as $request) {
            $cells = [
                $request->leave_date->format('d-m-Y'),
                $request->student->name,
                $request->student->kelas ?? '-',
                $request->type,
                $request->destination,
                ucfirst($request->status),
                $request->actual_exit_at?->format('H:i') ?? '-',
                $request->actual_return_at?->format('H:i') ?? '-',
            ];
            $wrapped = [];
            $lineCount = 1;
            foreach ($cells as $index => $cell) {
                $wrapped[$index] = $this->wrap((string) $cell, self::COLUMNS[$index] - 10);
                $lineCount = max($lineCount, count($wrapped[$index]));
            }
            $rowHeight = max(23, $lineCount * 10 + 8);

            if ($top + $rowHeight > self::PAGE_HEIGHT - 35) {
                $pages[] = $content;
                $content = $this->newPage($filters);
                $top = 119;
            }

            $content .= $this->rect(self::LEFT, $top, array_sum(self::COLUMNS), $rowHeight, '1 1 1');
            $x = self::LEFT;
            foreach (self::COLUMNS as $index => $width) {
                $content .= $this->line($x, $top, $x, $top + $rowHeight);
                foreach ($wrapped[$index] as $lineIndex => $line) {
                    $content .= $this->text($x + 5, $top + 13 + ($lineIndex * 10), $line, 'F1', 8);
                }
                $x += $width;
            }
            $content .= $this->line($x, $top, $x, $top + $rowHeight);
            $content .= $this->line(self::LEFT, $top + $rowHeight, $x, $top + $rowHeight);
            $top += $rowHeight;
        }

        if ($requests->isEmpty()) {
            $content .= $this->text(self::LEFT + 8, 137, 'Tidak ada data pada filter yang dipilih.', 'F1', 9);
        }
        $pages[] = $content;

        return $this->assemble($pages);
    }

    private function newPage(array $filters): string
    {
        $content = $this->text(self::LEFT, 35, 'SMA NEGERI 1 BATU AMPAR', 'F2', 15);
        $content .= $this->text(self::LEFT, 53, 'LAPORAN IZIN KELUAR MASUK SISWA', 'F2', 11);
        $content .= $this->text(self::LEFT, 69, 'Dicetak: '.now()->format('d-m-Y H:i'), 'F1', 8);

        $filterLabel = 'Filter: ' . (($filters['status'] ?? 'semua') === 'semua' ? 'Semua status' : ucfirst($filters['status']));
        if (! empty($filters['from']) || ! empty($filters['to'])) {
            $filterLabel .= ' | Tanggal: '.($filters['from'] ?? 'awal').' s.d. '.($filters['to'] ?? 'akhir');
        }
        $content .= $this->text(self::LEFT, 82, $filterLabel, 'F1', 8);

        $x = self::LEFT;
        $headerTop = 94;
        foreach (self::COLUMNS as $index => $width) {
            $content .= $this->rect($x, $headerTop, $width, 25, '0.88 0.92 0.97');
            $content .= $this->line($x, $headerTop, $x, $headerTop + 25);
            $content .= $this->text($x + 5, $headerTop + 16, self::HEADERS[$index], 'F2', 8);
            $x += $width;
        }
        $content .= $this->line($x, $headerTop, $x, $headerTop + 25);
        $content .= $this->line(self::LEFT, $headerTop + 25, $x, $headerTop + 25);

        return $content;
    }

    /** @return list<string> */
    private function wrap(string $value, int $width): array
    {
        $words = preg_split('/\s+/u', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($words === []) return ['-'];

        $limit = max(8, (int) floor($width / 4.5));
        $lines = [];
        $line = '';
        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;
            if (mb_strlen($candidate) > $limit && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }
        if ($line !== '') $lines[] = $line;

        return $lines;
    }

    private function text(float $x, float $top, string $value, string $font, int $size): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        $encoded = $encoded === false ? $value : $encoded;
        $escaped = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $encoded);
        $baseline = self::PAGE_HEIGHT - $top;

        return "BT /{$font} {$size} Tf 1 0 0 1 {$x} {$baseline} Tm ({$escaped}) Tj ET\n";
    }

    private function rect(float $x, float $top, float $width, float $height, string $color): string
    {
        $y = self::PAGE_HEIGHT - $top - $height;

        return "{$color} rg {$x} {$y} {$width} {$height} re f\n";
    }

    private function line(float $x1, float $top1, float $x2, float $top2): string
    {
        $y1 = self::PAGE_HEIGHT - $top1;
        $y2 = self::PAGE_HEIGHT - $top2;

        return "0.72 G 0.5 w {$x1} {$y1} m {$x2} {$y2} l S\n";
    }

    /** @param list<string> $pages */
    private function assemble(array $pages): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];
        $kids = [];
        foreach ($pages as $index => $pageContent) {
            $pageId = 5 + ($index * 2);
            $contentId = $pageId + 1;
            $kids[] = "{$pageId} 0 R";
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ".self::PAGE_WIDTH.' '.self::PAGE_HEIGHT."] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents {$contentId} 0 R >>";
            $objects[$contentId] = '<< /Length '.strlen($pageContent)." >>\nstream\n{$pageContent}endstream";
        }
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($pages).' >>';

        ksort($objects);
        $pdf = "%PDF-1.4\n%âãÏÓ\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$id])."\n";
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}
