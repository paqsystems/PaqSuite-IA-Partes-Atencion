<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PQ_PARTES_CLIENTES', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('nombre', 255);
            $table->unsignedBigInteger('tipo_cliente_id');
            $table->string('code', 50);
            $table->string('email', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('inhabilitado')->default(false);
            $table->dateTime('created_at', 3)->nullable();
            $table->dateTime('updated_at', 3)->nullable();

            $table->unique('code', 'pq_partes_clientes_code_uq');
            $table->foreign('tipo_cliente_id', 'pq_partes_clientes_tipo_cliente_fk')
                ->references('id')
                ->on('PQ_PARTES_TIPOS_CLIENTE');
            $table->foreign('user_id', 'pq_partes_clientes_user_id_fk')
                ->references('id')
                ->on('users');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlsrv') {
            DB::unprepared(<<<'SQL'
IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'pq_partes_clientes_user_id_filtered_uq'
      AND object_id = OBJECT_ID(N'dbo.PQ_PARTES_CLIENTES')
)
BEGIN
    CREATE UNIQUE INDEX pq_partes_clientes_user_id_filtered_uq
        ON dbo.PQ_PARTES_CLIENTES(user_id)
        WHERE user_id IS NOT NULL;
END
SQL);
        } else {
            Schema::table('PQ_PARTES_CLIENTES', function (Blueprint $table) {
                $table->unique('user_id', 'pq_partes_clientes_user_id_uq');
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlsrv') {
            DB::unprepared(<<<'SQL'
IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'pq_partes_clientes_user_id_filtered_uq'
      AND object_id = OBJECT_ID(N'dbo.PQ_PARTES_CLIENTES')
)
    DROP INDEX pq_partes_clientes_user_id_filtered_uq ON dbo.PQ_PARTES_CLIENTES;
SQL);
        }

        Schema::dropIfExists('PQ_PARTES_CLIENTES');
    }
};
