<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DxReportingProxyTest extends TestCase
{
    public function test_get_designer_model_se_reenvia_al_sidecar(): void
    {
        Http::fake([
            'http://127.0.0.1:5055/DXXRD/GetDesignerModel' => Http::response(
                '{"reportModel":true}',
                200,
                ['Content-Type' => 'application/json'],
            ),
        ]);

        $response = $this->call(
            'POST',
            '/DXXRD/GetDesignerModel',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
            'reportUrl=partes.consultaDetallada.principal',
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        $this->assertSame('{"reportModel":true}', $response->getContent());

        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:5055/DXXRD/GetDesignerModel'
                && $request->method() === 'POST'
                && str_contains((string) $request->body(), 'partes.consultaDetallada.principal');
        });
    }

    public function test_prefijo_desconocido_no_existe(): void
    {
        $this->get('/DXXOTHER/GetDesignerModel')->assertNotFound();
    }
}
