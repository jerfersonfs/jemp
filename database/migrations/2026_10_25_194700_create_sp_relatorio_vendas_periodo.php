<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_relatorio_vendas_periodo');
        DB::unprepared($this->procedureDefinition());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_relatorio_vendas_periodo');
    }

    private function procedureDefinition(): string
    {
        $source = file_get_contents(database_path('procedures/sp_relatorio_vendas_periodo.sql'));

        if ($source === false) {
            throw new RuntimeException('Não foi possível ler o SQL da procedure de relatório de vendas.');
        }

        if (preg_match('/^[\t ]*CREATE\s+PROCEDURE\b.*?END\s*\$\$/ims', $source, $matches) !== 1) {
            throw new RuntimeException('A definição da procedure não foi encontrada no arquivo SQL.');
        }

        $definition = preg_replace('/\$\$\s*$/', '', trim($matches[0]));

        if ($definition === null) {
            throw new RuntimeException('Não foi possível preparar a definição da procedure para o PDO.');
        }

        return $definition;
    }
};
