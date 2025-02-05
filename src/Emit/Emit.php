<?php

namespace Divulgueregional\ArquiteturaFiscal\Emit;

use stdClass;

class Emit
{
    public static function validarEmit($std)
    {
        if ($std->emit->CRT) {
            $crt = Emit::validarCrt($std);
            if ($crt == 'ok') {
                $std->emit->crtNome = Emit::nomeCrt($std->emit->CRT);
            }
        } else {
            $std->emit->status = 'ERRO';
            $std->emit->mensagem .= "CRT nao poder ser nulo ou vazio. Valores permitidos: 1, 2, 3 ou 4.";
        }
    }


    private static function validarCrt(stdClass $std): string
    {
        // Definição dos valores válidos para CRT
        $crtsValidos = [1, 2, 3, 4];

        // Verifica se o CRT está na lista de valores válidos
        if (in_array($std->emit->CRT, $crtsValidos, true)) {
            return 'ok';
        }
        // print_r($std->emit);
        $std->emit->status = 'ERRO';
        $std->emit->mensagem = "CRT {$std->emit->CRT} invalido. Valores permitidos: 1, 2, 3 ou 4.";

        return "CRT {$std->emit->CRT} invalido. Valores permitidos: 1, 2, 3 ou 4.";
    }

    private static function nomeCrt($crt): string
    {
        switch ($crt) {
            case '1':
                return 'Simples Nacional';
                break;
            case '2':
                return 'Simples Nacional Execedeu Receita Bruta';
                break;
            case '3':
                return 'Regime Normal';
                break;
            case '4':
                return 'MEI';
                break;
            default:
                return 'NO';
                break;
        }
    }
}
