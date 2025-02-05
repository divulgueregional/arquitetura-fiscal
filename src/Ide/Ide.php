<?php

namespace Divulgueregional\ArquiteturaFiscal\Ide;

use stdClass;

class Ide
{
    public static function validarIde(stdClass $std)
    {
        Ide::tpNF($std->ide->tpNF, $std);

        if ($std->ide->tpNF == 1) {
            $std->ide->tpNFNome = 'Saída';
        } else {
            $std->ide->tpNFNome = 'Entrada';
        }
        // $std->ide->tpNFNome = 123;
        return $std;
    }

    /*
    tpNF: Tipo de operacao
    definição: 1- saida, 0- entrada
    */
    public static function tpNF($tpNF, $std): string
    {
        if ($tpNF !== 1 && $tpNF !== 0) {
            $std->ide->status = 'ERRO';
            $std->ide->mensagem .= "campo tpNF = {$tpNF} invalido. Opcao tpNF validos: 1- saida, 0- entrada";

            return "campo tpNF = {$tpNF} invalido. Opcao tpNF validos: 1- saida, 0- entrada";
        }

        return 'ok';
    }
}
