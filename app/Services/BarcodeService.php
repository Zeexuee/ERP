<?php

namespace App\Services;

use App\Models\WarehouseItem;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarcodeService
{
    public function generateBarcodePNG(string $code, int $widthFactor = 2, int $height = 50): string
    {
        $generator = new BarcodeGeneratorPNG;
        $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128, $widthFactor, $height);

        return 'data:image/png;base64,'.base64_encode($barcode);
    }

    public function cleanSvg(string $svg): string
    {
        $svg = preg_replace('/<\?xml.*?\?>/is', '', $svg);
        $svg = preg_replace('/<!DOCTYPE.*?>/is', '', $svg);

        return trim((string) $svg);
    }

    public function generateBarcodeSVG(string $code, int $widthFactor = 2, int $height = 50): string
    {
        $generator = new BarcodeGeneratorSVG;
        $raw = $generator->getBarcode($code, $generator::TYPE_CODE_128, $widthFactor, $height);

        return $this->cleanSvg($raw);
    }

    public function generateQRCodePNG(string $data, int $size = 200): string
    {
        if (extension_loaded('imagick')) {
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
        $raw = (string) QrCode::format('svg')
            ->size($size)
            ->errorCorrection('M')
            ->generate($data);

        return $this->cleanSvg($raw);
    }

    public function generateQRCodeDataUrl(string $data, int $size = 200): string
    {
        if (extension_loaded('imagick')) {
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
