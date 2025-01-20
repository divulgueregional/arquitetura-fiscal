<?php

use ArquiteturaFiscal\Factories\ImpostoFactory;
use ArquiteturaFiscal\Configuracoes\ConfiguracaoFiscal;

class CalculadoraFiscal
{
    public function calcularImposto(array $dados): float
    {
        $tipoImposto = ConfiguracaoFiscal::obterConfiguracao($dados);
        $imposto = ImpostoFactory::criar($tipoImposto, $dados);

        return $imposto->calcular($dados);
    }
}
