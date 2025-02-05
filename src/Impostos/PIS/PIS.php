<?php

namespace Divulgueregional\ArquiteturaFiscal\Impostos\PIS;

// use ArquiteturaFiscal\Configuracoes\Formatacao;
use Divulgueregional\ArquiteturaFiscal\Configuracoes\Formatacao;
use stdClass;

class PIS
{
    function __construct() {}

    public static function calcularPis($std)
    {
        ################################################################
        ######### VALIDAR CST ##########################################
        ################################################################
        $validarCST = PIS::validarCstPis($std->PIS->CST, $std->ide->tpNF);
        if ($validarCST != 'ok') {
            $std->PIS->mensagem = $validarCST . '<br>';
            $std->PIS->status = 'ERRO';
        }

        if ($std->PIS->digitado == 'S') {
            #############################################################
            ######### DIGITADO ##########################################
            #############################################################
            // retornar os mesmos dados recebidos
            $std->PIS->vBC  = $std->PIS->vBC > 0 ? Formatacao::moedaPonto($std->PIS->vBC, 2) : '';
            $std->PIS->pPIS = $std->PIS->pPIS > 0 ? Formatacao::moedaPonto($std->PIS->pPIS, 2) : '';
            $std->PIS->vPIS = $std->PIS->vPIS > 0 ? Formatacao::moedaPonto($std->PIS->vPIS, 2) : '';
        } else {
            #############################################################
            ######### CALCULAR ##########################################
            #############################################################
            $vBC = null;
            $aliquota = null;
            $valorImposto = null;
            $reducao_icms = null;
            $CST = $std->PIS->CST ? $std->PIS->CST : 'CST não informado';

            // PIS precisa ser calculado
            if ($CST == '01' || $CST == '02' || $CST == '03' || $CST == '05') {
                if ($std->emit->CRT > 2) {
                    // Realiza o cálculo, pois exige o cálculo de imposto
                    if ($std->PIS->pPIS > 0) {
                        $retirar_valor_icms = 0;
                        if (Formatacao::maiuscula($std->PIS->retirar_valor_icms) == 'S') {
                            $retirar_valor_icms = $std->ICMS->vICMS;
                            $reducao_icms = " COM REDUCAO DO ICMS";
                        }
                        $vBC = Formatacao::moedaPonto($std->baseProduto - $retirar_valor_icms, 2);
                        if ($vBC > 0) {
                            $aliquota = Formatacao::moedaPonto($std->PIS->pPIS, 2);
                            $valorCalculado = $vBC * $std->PIS->pPIS / 100;
                            $valorImposto = Formatacao::moedaPonto($valorCalculado, 2);

                            $std->PIS->vBC  = $vBC;
                            $std->PIS->pPIS = $aliquota;
                            $std->PIS->vPIS = $valorImposto;
                            $std->PIS->status = 'PIS CALCULADO' . $reducao_icms;
                        } else {
                            $std->PIS->mensagem .= '0 Valor da base de calculo nao pode ser zerada ou negativa, verifique os valores do produto';
                            $std->PIS->status = 'ERRO';
                        }
                    } else {
                        $std->PIS->mensagem .= 'Nao foi informado a aliquota para calcular o pis';
                        $std->PIS->status = 'ERRO';
                    }
                } else {
                    $std->PIS->CST = $CST;
                    $std->PIS->mensagem .= 'Empresa do SIMPLES NACIONAl nao pode calcular PIS. Mude o CST';
                    $std->PIS->status = 'ERRO';
                }
            } elseif ($CST == '04' || $CST == '06' || $CST == '07' || $CST == '08' || $CST == '09') {
                // Não exige cálculo, pois é isento ou a alíquota é zero
                $std->PIS->vBC = '';
                $std->PIS->pPIS = '';
                $std->PIS->vPIS = '';
            } else {
                // CST inválido ou não reconhecido.
                $std->PIS->mensagem .= "CST {$CST} nao exige caldulo do pis";
                $std->PIS->status = 'ERRO';
            }
        }

        return  $std;
    }

    private static function validarCstPis(string $cst, string $tipoOperacao): string
    {
        // Garantir que o CST tenha dois dígitos
        $cst = str_pad($cst, 2, '0', STR_PAD_LEFT);

        if ($tipoOperacao) {

            // Definição dos CSTs válidos
            $cstsSaida = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '49'];
            $cstsEntrada = ['50', '51', '52', '53', '54', '55', '56', '60', '61', '62', '63', '64', '65', '66', '67', '70', '71', '72', '73', '74', '75', '98'];

            // Se CST for 99, sempre retorna 'ok'
            if ($cst === '99') {
                return 'ok';
            }

            // Validação conforme tipo de operação
            if ($tipoOperacao === '1') {
                // nota de saída
                if (in_array($cst, $cstsSaida, true)) {
                    return 'ok';
                }
                return "CST $cst nao e um CST de saida.";
            } else if ($tipoOperacao === '0') {
                // nota de entrada
                if (in_array($cst, $cstsEntrada, true)) {
                    return 'ok';
                }
                return "CST $cst nao e um CST de entrada.";
            }
        } else {
            $csts = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '49', '50', '51', '52', '53', '54', '55', '56', '60', '61', '62', '63', '64', '65', '66', '67', '70', '71', '72', '73', '74', '75', '98'];
            if (in_array($cst, $csts, true)) {
                return 'ok';
            }
            return "CST $cst nao e um CST valido para pis.";
        }

        // Caso $tipoOperacao seja inválido
        return "Tipo de operacao {$tipoOperacao} invalido. Opcao tpNF validas: 1- saida, 0- entrada";
    }
}
