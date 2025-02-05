<?php

namespace Divulgueregional\ArquiteturaFiscal\Configuracoes;

class Formatacao
{
    public static function moedaPonto($valor, $decimal = 2)
    {
        return number_format(floatval($valor), $decimal, '.', '');
    }

    public static function maiuscula($letra)
    {
        return mb_strtoupper($letra);
    }
}
