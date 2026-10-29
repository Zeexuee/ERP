<?php

namespace Tests\Unit;

use App\Services\BarcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BarcodeService $barcodeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->barcodeService = new BarcodeService;
    }

    public function test_can_generate_barcode_svg(): void
    {
        $svg = $this->barcodeService->generateBarcodeSVG('TEST-SKU-12345');

        $this->assertNotEmpty($svg);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    public function test_can_generate_barcode_png_data_url(): void
    {
        $dataUrl = $this->barcodeService->generateBarcodePNG('TEST-SKU-12345');

        $this->assertNotEmpty($dataUrl);
        $this->assertTrue(
            str_starts_with($dataUrl, 'data:image/png;base64,') || str_starts_with($dataUrl, 'data:image/svg+xml;base64,')
        );
    }

    public function test_can_generate_qr_code_svg(): void
    {
        $svg = $this->barcodeService->generateQRCodeSVG('TEST-SKU-12345');

        $this->assertNotEmpty($svg);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    public function test_clean_svg_removes_xml_declarations(): void
    {
        $input = '<?xml version="1.0" standalone="no"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg><g></g></svg>';
        $cleaned = $this->barcodeService->cleanSvg($input);

        $this->assertStringNotContainsString('<?xml', $cleaned);
        $this->assertStringNotContainsString('<!DOCTYPE', $cleaned);
        $this->assertStringStartsWith('<svg', $cleaned);
    }
}
