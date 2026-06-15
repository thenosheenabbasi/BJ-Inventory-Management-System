<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $fillable = [
        'repair_job_id',
        'code',
        'payload',
        'format',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function repairJob()
    {
        return $this->belongsTo(RepairJob::class);
    }

    public function svgMarkup(int $size = 156): string
    {
        $payload = $this->payload ?: $this->code;
        $hash = hash('sha256', $payload);
        $cells = 21;
        $cellSize = $size / $cells;
        $rects = [];

        $addFinder = function (int $x, int $y) use (&$rects): void {
            for ($row = 0; $row < 7; $row++) {
                for ($col = 0; $col < 7; $col++) {
                    $outer = $row === 0 || $row === 6 || $col === 0 || $col === 6;
                    $inner = $row >= 2 && $row <= 4 && $col >= 2 && $col <= 4;

                    if ($outer || $inner) {
                        $rects[] = [$x + $col, $y + $row];
                    }
                }
            }
        };

        $addFinder(0, 0);
        $addFinder(14, 0);
        $addFinder(0, 14);

        for ($row = 0; $row < $cells; $row++) {
            for ($col = 0; $col < $cells; $col++) {
                $inFinder = ($row < 7 && $col < 7)
                    || ($row < 7 && $col >= 14)
                    || ($row >= 14 && $col < 7);

                if ($inFinder) {
                    continue;
                }

                $index = ($row * $cells + $col) % strlen($hash);
                $value = hexdec($hash[$index]);

                if (($value + $row + $col) % 3 === 0) {
                    $rects[] = [$col, $row];
                }
            }
        }

        $escapedCode = htmlspecialchars($this->code, ENT_QUOTES, 'UTF-8');
        $escapedPayload = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
        $markup = '<svg class="repair-qr-svg" viewBox="0 0 '.$size.' '.$size.'" role="img" aria-label="QR code for '.$escapedCode.'" xmlns="http://www.w3.org/2000/svg">';
        $markup .= '<title>'.$escapedPayload.'</title><rect width="'.$size.'" height="'.$size.'" rx="10" fill="#ffffff"/>';

        foreach ($rects as [$x, $y]) {
            $markup .= '<rect x="'.($x * $cellSize).'" y="'.($y * $cellSize).'" width="'.ceil($cellSize).'" height="'.ceil($cellSize).'" fill="#07111f"/>';
        }

        return $markup.'</svg>';
    }
}
