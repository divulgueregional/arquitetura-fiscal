<?php

namespace Divulgueregional\ArquiteturaFiscal;

use ArquiteturaFiscal\Factories\ImpostoFactory;
use ArquiteturaFiscal\Configuracoes\ConfiguracaoFiscal;

class ArquiteturaFiscal
{
    public function calcularImposto(array $dados): float
    {
        $tipoImposto = ConfiguracaoFiscal::obterConfiguracao($dados);
        $imposto = ImpostoFactory::criar($tipoImposto, $dados);

        return $imposto->calcular($dados);
    }

    public function teste()
    {
        return 'Conexão a arquitetura fiscla realizada com sucesso!!!';
    }
}
