<?php

// namespace ArquiteturaFiscal\Impostos\ICMS;
namespace Divulgueregional\ArquiteturaFiscal\Impostos\ICMS;

use Divulgueregional\ArquiteturaFiscal\Configuracoes\Formatacao;

use PhpParser\Node\Stmt\Return_;
use stdClass;

class ICMSLimpar
{
    /**
     * Calcula o valor do ICMS com base nos dados fornecidos.
     *
     * @param array $dados Dados necessários para o cálculo do ICMS.
     * @param stdClass $std Objeto padrão para armazenar os resultados.
     * @return stdClass Retorna o objeto com os valores calculados.
     */
    public static function limparIcms(stdClass $std)
    {
        // print_r($std);
        if ($std->crt == 1) {

            return ICMSLimpar::sn($std);
        } else {
            // não é do simples nacional unset($objeto->digitado);
            if ($std->CST == '00') {
                return ICMSLimpar::icms00($std);
            }
        }
        return 'ok';
    }


    #############################################################
    ######### SIMPLES NACIONAL ##################################
    #############################################################
    private static function sn($std)
    {

        return $std;
    }

    #############################################################
    ######### ICMS CST 00 #######################################
    #############################################################
    private static function icms00($std)
    {
        return ICMSLimpar::removerPropriedadesVazias($std);
    }

    private static function removerPropriedadesVazias($objeto)
    {
        // Converte o objeto para array
        $array = (array) $objeto;

        // Filtra o array, removendo valores vazios
        $arrayFiltrado = array_filter($array, function ($valor) {
            return !empty($valor); // Remove valores vazios
        });

        // Converte o array filtrado de volta para stdClass
        return (object) $arrayFiltrado;
    }
}
