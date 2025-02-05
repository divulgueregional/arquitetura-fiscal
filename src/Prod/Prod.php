<?php

namespace Divulgueregional\ArquiteturaFiscal\Prod;

use stdClass;

class Prod
{
    public static function validarProd(stdClass $std)
    {
        if (empty($std->prod->valorProduto) || $std->prod->valorProduto <= 0) {

            $std->prod->status = 'ERRO';
            $std->prod->mensagem .= "precisa informar o valor do produto";
            $std->prod->valorProduto = 0;

            return $std;
        }
    }
}
