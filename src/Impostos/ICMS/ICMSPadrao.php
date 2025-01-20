<?php

namespace ArquiteturaFiscal\Impostos\ICMS;

use ArquiteturaFiscal\Impostos\ImpostoInterface;

class ICMSPadrao implements ImpostoInterface
{
    public function calcular(array $dados): float
    {
        // Exemplo de cálculo básico de ICMS
        $baseCalculo = $dados['valorProduto'] - $dados['descontos'];
        return $baseCalculo * ($dados['aliquota'] / 100);
    }
}
