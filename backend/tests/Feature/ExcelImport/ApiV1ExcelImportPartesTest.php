<?php

namespace Tests\Feature\ExcelImport;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiV1ExcelImportPartesTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'DEMO'];
    }

    /** @return array<string, string> */
    private function authHeaders(string $token): array
    {
        return array_merge($this->tenantHeaders(), ['Authorization' => 'Bearer '.$token]);
    }

    private function login(string $usuario = 'admin', string $password = 'Paqsystems'): string
    {
        $this->seed();
        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => $usuario,
            'password' => $password,
        ], $this->tenantHeaders());
        $login->assertStatus(200);

        return (string) $login->json('resultado.token');
    }

    /**
     * @return array{clienteCode: string, tipoCode: string, asistenteCode: string}
     */
    private function seedCatalogos(): array
    {
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCX',
            'descripcion' => 'Tipo',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => null,
            'code' => 'CLX',
            'nombre' => 'Cliente Import',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoCode = (string) DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->value('code');
        $asistenteCode = (string) DB::table('PQ_PARTES_USUARIOS')->where('code', 'admin')->value('code');

        return [
            'clienteCode' => 'CLX',
            'tipoCode' => $tipoCode !== '' ? $tipoCode : 'GEN',
            'asistenteCode' => $asistenteCode !== '' ? $asistenteCode : 'admin',
        ];
    }

    private function makeXlsx(array $rows): UploadedFile
    {
        $binary = app(\PaqSuite\LaravelCore\ExcelImport\MinimalXlsxExcelImportBinaryExporter::class)
            ->workbookFromRows($rows, 'Hoja1');

        $path = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        file_put_contents($path, $binary);

        return new UploadedFile($path, 'partes.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_template_ok_y_capability_off(): void
    {
        $token = $this->login();
        $this->get('/api/v1/excel-import/processes/partes.tareas.import/template', $this->authHeaders($token))
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        DB::table('pq_parametros_gral')
            ->where('programa', 'ExcelImport')
            ->where('clave', 'ExcelImportEnabled')
            ->update(['valor_bool' => false]);

        $this->getJson('/api/v1/excel-import/processes/partes.tareas.import/template', $this->authHeaders($token))
            ->assertStatus(403)
            ->assertJsonPath('error', 4604);
    }

    public function test_upload_validate_process_parcial_y_es_tarea(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $headerRow = ['cliente', 'asistente', 'tipo_tarea', 'fecha', 'duracion', 'sin_cargo', 'presencial', 'descripcion'];
        $ok = [$cat['clienteCode'], $cat['asistenteCode'], $cat['tipoCode'], '01/08/2026', '01:00', 'falso', 'verdadero', 'Tarea import OK'];
        $bad = [$cat['clienteCode'], $cat['asistenteCode'], $cat['tipoCode'], '01/08/2026', '00:10', 'falso', 'verdadero', 'Duracion invalida'];

        $file = $this->makeXlsx([$headerRow, $ok, $bad]);
        $upload = $this->post('/api/v1/excel-import/batches', [
            'processCode' => 'partes.tareas.import',
            'file' => $file,
        ], $this->authHeaders($token));

        $upload->assertStatus(201);
        $upload->assertJsonPath('resultado.validRows', 1);
        $upload->assertJsonPath('resultado.errorRows', 1);
        $batchId = (string) $upload->json('resultado.batchId');

        $process = $this->postJson('/api/v1/excel-import/batches/'.$batchId.'/process', [], $this->authHeaders($token));
        $process->assertStatus(200);
        $process->assertJsonPath('resultado.status', 'partial');
        $process->assertJsonPath('resultado.processedRows', 1);

        $tarea = DB::table('PQ_PARTES_REGISTRO_TAREA')->where('observacion', 'Tarea import OK')->first();
        $this->assertNotNull($tarea);
        $this->assertTrue((bool) $tarea->es_tarea);
        $this->assertFalse((bool) $tarea->cerrado);
    }

    public function test_forbidden_process_code_menu(): void
    {
        $token = $this->login();
        DB::table('pq_excel_procesos')->updateOrInsert(
            ['codigo' => 'partes.fake.import'],
            [
                'descripcion' => 'Fake',
                'menu_process_code' => 'no_existe_menu',
                'handler_class' => null,
                'allow_partial' => true,
                'sheet_name' => 'Hoja1',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->getJson('/api/v1/excel-import/processes/partes.fake.import/template', $this->authHeaders($token))
            ->assertStatus(403)
            ->assertJsonPath('error', 4603);
    }

    public function test_asistente_no_supervisor_rechaza_otro_owner(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();

        DB::table('PQ_PARTES_USUARIOS')->where('code', 'admin')->update(['supervisor' => false]);

        $headerRow = ['cliente', 'asistente', 'tipo_tarea', 'fecha', 'duracion', 'sin_cargo', 'presencial', 'descripcion'];
        $badOwner = [$cat['clienteCode'], 'PQ', $cat['tipoCode'], '01/08/2026', '01:00', 'falso', 'verdadero', 'Otro owner'];
        $file = $this->makeXlsx([$headerRow, $badOwner]);

        $upload = $this->post('/api/v1/excel-import/batches', [
            'processCode' => 'partes.tareas.import',
            'file' => $file,
        ], $this->authHeaders($token));

        $upload->assertStatus(201);
        $upload->assertJsonPath('resultado.validRows', 0);
        $upload->assertJsonPath('resultado.errorRows', 1);

        $errors = $this->getJson(
            '/api/v1/excel-import/batches/'.$upload->json('resultado.batchId').'/errors',
            $this->authHeaders($token)
        );
        $errors->assertStatus(200);
        $this->assertSame(
            'partes.import.asistenteDistintoSesion',
            $errors->json('resultado.items.0.messageKey')
        );
    }
}
