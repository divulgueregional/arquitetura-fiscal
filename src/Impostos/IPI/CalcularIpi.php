<?php

// namespace Divulgueregional\ArquiteturaFiscal\Impostos\Ipi;
namespace Divulgueregional\ArquiteturaFiscal\Impostos\Ipi;

use Divulgueregional\ArquiteturaFiscal\Configuracoes\Formatacao;
// use stdClass;

class CalcularIpi
{
    function __construct() {}


    public static function calcularIpi($std)
    {
        if ($std->IPI->cEnq == 888) {
            return $std;
        }
        #############################################################
        ######### VALIDAR CST #######################################
        #############################################################
        $validarCST = CalcularIpi::validarCstIpi($std->IPI->CST, $std->ide->tpNF);
        if ($validarCST != 'ok') {
            $std->IPI->mensagem = $validarCST . '<br>';
            $std->IPI->status = 'ERRO';
        }

        if ($std->IPI->digitado == 'S') {
            #############################################################
            ######### DIGITADO ##########################################
            #############################################################
            // retornar os mesmos dados recebidos
            $std->IPI->vBC  = $std->IPI->vBC > 0 ? Formatacao::moedaPonto($std->IPI->vBC, 2) : '';
            $std->IPI->pIPI = $std->IPI->pIPI > 0 ? Formatacao::moedaPonto($std->IPI->pIPI, 2) : '';
            $std->IPI->vIPI = $std->IPI->vIPI > 0 ? Formatacao::moedaPonto($std->IPI->vIPI, 2) : '';
        } else {
            #############################################################
            ######### CALCULAR ##########################################
            #############################################################
            $vBC = null;
            $aliquota = null;
            $valorImposto = null;
            $reducao_icms = null;
            $CST = $std->IPI->CST ? $std->IPI->CST : 'CST não informado';

            // PIS precisa ser calculado
            if ($CST == '00' || $CST == '50') {
                if ($std->emit->CRT > 2) {
                    // Realiza o cálculo, pois exige o cálculo de imposto
                    if ($std->IPI->pIPI > 0) {
                        $vBC = Formatacao::moedaPonto($std->baseProduto, 2);
                        if ($vBC > 0) {
                            $aliquota = Formatacao::moedaPonto($std->IPI->pIPI, 2);
                            $valorCalculado = $vBC * $std->IPI->pIPI / 100;
                            $valorImposto = Formatacao::moedaPonto($valorCalculado, 2);

                            $std->IPI->vBC  = $vBC;
                            $std->IPI->pIPI = $aliquota;
                            $std->IPI->vIPI = $valorImposto;
                            $std->IPI->status = 'IPI CALCULADO';
                        } else {
                            $std->IPI->mensagem .= '0 Valor da base de calculo nao pode ser zerada ou negativa, verifique os valores do produto';
                            $std->IPI->status = 'ERRO';
                        }
                    } else {
                        $std->IPI->mensagem .= 'Nao foi informado a aliquota para calcular o pis';
                        $std->IPI->status = 'ERRO';
                    }
                } else {
                    $std->IPI->CST = $CST;
                    $std->IPI->mensagem .= 'Empresa do SIMPLES NACIONAl nao pode calcular PIS. Mude o CST';
                    $std->IPI->status = 'ERRO';
                }
            } else {
                if ($std->IPI->pIPI > 0) {
                    // Não exige cálculo, pois é isento ou a alíquota é zero
                    $std->IPI->mensagem .= "CST {$CST} não exige caldulo do pis";
                    $std->IPI->status = 'ERRO';
                }
            }
        }

        return  $std;
    }

    private static function validarCstIpi(string $cst, string $tipoOperacao): string
    {
        // Garantir que o CST tenha dois dígitos
        $cst = str_pad($cst, 2, '0', STR_PAD_LEFT);

        if ($tipoOperacao) {
            // Definição dos CSTs válidos para saída e entrada
            $cstsEntrada = ['00', '01', '02', '03', '04', '05', '49'];
            $cstsSaida = ['50', '51', '52', '53', '54', '55', '99'];

            // Se CST for 99, sempre retorna 'ok' (outras entradas)
            if ($cst === '99') {
                return 'ok';
            }

            // Validação conforme tipo de operação
            if ($tipoOperacao === '1') {
                // Nota de saída
                if (in_array($cst, $cstsSaida, true)) {
                    return 'ok';
                }
                return "CST $cst nao e um CST de saida valido para IPI.";
            } else if ($tipoOperacao === '0') {
                // Nota de entrada
                if (in_array($cst, $cstsEntrada, true)) {
                    return 'ok';
                }
                return "CST $cst nao e um CST de entrada valido para IPI.";
            }
        } else {
            // tipoOperacao não informado
            $cstsIpi = ['00', '01', '02', '03', '04', '05', '49', '50', '51', '52', '53', '54', '55', '99'];
            if (in_array($cst, $cstsIpi, true)) {
                return 'ok';
            }

            return "CST $cst nao e um CST valido para IPI.";
        }

        // Caso $tipoOperacao seja inválido
        return "Tipo de operacao {$tipoOperacao} invalido. Opcoes validas para tpNF: 1 - saída, 0 - entrada.";
    }
}
