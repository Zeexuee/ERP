<?php

namespace App\Services;

use App\Models\WarehouseItem;
use Illuminate\Support\Facades\Log;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarcodeService
{
    public function generateBarcodePNG(string $code, int $widthFactor = 2, int $height = 50): string
    {
        if (class_exists(BarcodeGeneratorPNG::class)) {
            try {
                $generator = new BarcodeGeneratorPNG;
                $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128, $widthFactor, $height);

                return 'data:image/png;base64,'.base64_encode($barcode);
            } catch (\Throwable $e) {
                Log::warning('Barcode PNG generation error: '.$e->getMessage());
            }
        }

        $svg = $this->generateFallbackBarcodeSVG($code, $widthFactor, $height);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function cleanSvg(string $svg): string
    {
        $svg = preg_replace('/<\?xml.*?\?>/is', '', $svg);
        $svg = preg_replace('/<!DOCTYPE.*?>/is', '', $svg);

        return trim((string) $svg);
    }

    public function generateBarcodeSVG(string $code, int $widthFactor = 2, int $height = 50): string
    {
        if (class_exists(BarcodeGeneratorSVG::class)) {
            try {
                $generator = new BarcodeGeneratorSVG;
                $raw = $generator->getBarcode($code, $generator::TYPE_CODE_128, $widthFactor, $height);

                return $this->cleanSvg($raw);
            } catch (\Throwable $e) {
                Log::warning('Barcode SVG generation error: '.$e->getMessage());
            }
        }

        return $this->generateFallbackBarcodeSVG($code, $widthFactor, $height);
    }

    public function generateQRCodePNG(string $data, int $size = 200): string
    {
        if (class_exists(QrCode::class) && extension_loaded('imagick')) {
            try {
                return (string) QrCode::format('png')
                    ->size($size)
                    ->errorCorrection('M')
                    ->generate($data);
            } catch (\Throwable) {
                // Fallback to SVG if Imagick fails
            }
        }

        return (string) $this->generateQRCodeSVG($data, $size);
    }

    public function generateQRCodeSVG(string $data, int $size = 200): string
    {
        if (class_exists(QrCode::class)) {
            try {
                $raw = (string) QrCode::format('svg')
                    ->size($size)
                    ->errorCorrection('M')
                    ->generate($data);

                return $this->cleanSvg($raw);
            } catch (\Throwable $e) {
                Log::warning('QR Code SVG generation error: '.$e->getMessage());
            }
        }

        return $this->generateFallbackQRCodeSVG($data, $size);
    }

    public function generateQRCodeDataUrl(string $data, int $size = 200): string
    {
        if (class_exists(QrCode::class) && extension_loaded('imagick')) {
            try {
                $qrCode = QrCode::format('png')
                    ->size($size)
                    ->errorCorrection('M')
                    ->generate($data);

                return 'data:image/png;base64,'.base64_encode($qrCode);
            } catch (\Throwable) {
                // Fallback to SVG data URL
            }
        }

        $svg = $this->generateQRCodeSVG($data, $size);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * Fallback lightweight SVG barcode when external package is not installed.
     */
    protected function generateFallbackBarcodeSVG(string $code, int $widthFactor = 2, int $height = 50): string
    {
        $codeLen = max(1, strlen($code));
        $barWidth = max(2, $widthFactor);
        $totalWidth = max(180, ($codeLen * 18 * $barWidth) + 20);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$totalWidth.'" height="'.$height.'" viewBox="0 0 '.$totalWidth.' '.$height.'">';
        $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';

        $x = 10;
        for ($i = 0; $i < $codeLen; $i++) {
            $charVal = ord($code[$i]);
            $w1 = (($charVal % 3) + 1) * $barWidth;
            $w2 = ((($charVal >> 2) % 3) + 1) * $barWidth;
            $svg .= '<rect x="'.$x.'" y="2" width="'.$w1.'" height="'.($height - 4).'" fill="#0f172a"/>';
            $x += $w1 + $barWidth;
            $svg .= '<rect x="'.$x.'" y="2" width="'.$w2.'" height="'.($height - 4).'" fill="#0f172a"/>';
            $x += $w2 + ($barWidth * 2);
        }

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Fallback lightweight SVG QR code placeholder when external package is not installed.
     */
    protected function generateFallbackQRCodeSVG(string $data, int $size = 200): string
    {
        $safeData = htmlspecialchars(substr($data, 0, 24));

        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'">'
            .'<rect width="100%" height="100%" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2" rx="8"/>'
            .'<rect x="16" y="16" width="36" height="36" fill="none" stroke="#0f172a" stroke-width="6"/>'
            .'<rect x="26" y="26" width="16" height="16" fill="#0f172a"/>'
            .'<rect x="'.($size - 52).'" y="16" width="36" height="36" fill="none" stroke="#0f172a" stroke-width="6"/>'
            .'<rect x="'.($size - 42).'" y="26" width="16" height="16" fill="#0f172a"/>'
            .'<rect x="16" y="'.($size - 52).'" width="36" height="36" fill="none" stroke="#0f172a" stroke-width="6"/>'
            .'<rect x="26" y="'.($size - 42).'" width="16" height="16" fill="#0f172a"/>'
            .'<text x="50%" y="'.($size / 2).'" dominant-baseline="middle" text-anchor="middle" font-family="monospace" font-size="11" font-weight="bold" fill="#0f172a">'.$safeData.'</text>'
            .'</svg>';
    }

    public function generateItemBarcode(WarehouseItem $item, string $format = 'png'): string
    {
        $code = $item->barcode ?: $item->sku ?: ('ITEM-'.$item->id);

        if ($format === 'svg') {
            return $this->generateBarcodeSVG($code);
        }

        return $this->generateBarcodePNG($code);
    }

    public function generateItemQRCode(WarehouseItem $item, string $format = 'png'): string
    {
        $qrData = $item->qr_code ?: $item->sku ?: $item->barcode ?: ('ITEM-'.$item->id);

        if ($format === 'svg') {
            return $this->generateQRCodeSVG($qrData);
        }

        return $this->generateQRCodeDataUrl($qrData);
    }

    public function generateItem1DSVG(WarehouseItem $item, int $widthFactor = 2, int $height = 50): string
    {
        $code = $item->sku ?: $item->barcode ?: ('ITEM-'.$item->id);

        return $this->generateBarcodeSVG($code, $widthFactor, $height);
    }

    public function generateItem2DSVG(WarehouseItem $item, int $size = 150): string
    {
        $qrData = $item->sku ?: $item->qr_code ?: $item->barcode ?: ('ITEM-'.$item->id);

        return $this->generateQRCodeSVG($qrData, $size);
    }

    public function generatePrintableLabel(WarehouseItem $item): array
    {
        return [
            'barcode_png' => $this->generateItemBarcode($item, 'png'),
            'qr_code_png' => $this->generateItemQRCode($item, 'png'),
            'barcode_svg' => $this->generateItem1DSVG($item),
            'qr_code_svg' => $this->generateItem2DSVG($item),
            'item_name' => $item->name,
            'sku' => $item->sku,
            'barcode_number' => $item->barcode ?: $item->sku,
            'qr_code_number' => $item->qr_code ?: $item->sku,
            'branch' => $item->branch->name ?? '',
            'location' => $item->location->full_location_code ?? 'N/A',
        ];
    }
}
