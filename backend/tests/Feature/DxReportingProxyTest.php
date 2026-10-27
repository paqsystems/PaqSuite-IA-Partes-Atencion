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

    public function test_post_dxxrd_invoke_reconstruye_body_desde_request_bag(): void
    {
        Http::fake([
            'http://127.0.0.1:5055/DXXRD' => Http::response(
                '{"success":true,"result":[]}',
                200,
                ['Content-Type' => 'application/json'],
            ),
        ]);

        $response = $this->post('/DXXRD', [
            'actionKey' => 'getUrls',
            'arg' => 'true',
        ]);

        $response->assertOk();
        $this->assertSame('{"success":true,"result":[]}', $response->getContent());

        Http::assertSent(function ($request) {
            if ($request->url() !== 'http://127.0.0.1:5055/DXXRD' || $request->method() !== 'POST') {
                return false;
            }

            $contentType = $request->header('Content-Type');
            $contentType = is_array($contentType) ? ($contentType[0] ?? '') : (string) $contentType;

            return $request->body() === 'actionKey=getUrls&arg=true'
                && str_contains($contentType, 'application/x-www-form-urlencoded');
        });
    }

    public function test_post_dxxrd_raw_form_usa_content_type_urlencoded(): void
    {
        Http::fake([
            'http://127.0.0.1:5055/DXXRD' => Http::response('{"success":true}', 200),
        ]);

        $this->call(
            'POST',
            '/DXXRD',
            [],
            [],
            [],
            [],
            'actionKey=getUrls&arg=true',
        )->assertOk();

        Http::assertSent(function ($request) {
            $contentType = $request->header('Content-Type');
            $contentType = is_array($contentType) ? ($contentType[0] ?? '') : (string) $contentType;

            return str_contains($contentType, 'application/x-www-form-urlencoded')
                && $request->body() === 'actionKey=getUrls&arg=true';
        });
    }

    public function test_prefijo_desconocido_no_existe(): void
    {
        $this->get('/DXXOTHER/GetDesignerModel')->assertNotFound();
    }
}
