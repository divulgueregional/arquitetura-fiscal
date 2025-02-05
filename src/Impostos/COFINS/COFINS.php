<?php

namespace Divulgueregional\ArquiteturaFiscal\Impostos\COFINS;

// use ArquiteturaFiscal\Configuracoes\Formatacao;
use Divulgueregional\ArquiteturaFiscal\Configuracoes\Formatacao;
use stdClass;

// namespace ArquiteturaFiscal\Impostos\Cofins;

// use stdClass;

class COFINS
{
    private $Formatar;
    private $std;
    private $formatacao;
    private  $valores;

    function __construct()
    {
        // $this->texto = null;
        // $this->Formatar = new Formatacao;
        // $this->formatacao = new \ArquiteturaFiscal\Configuracoes\Formatacao();
    }

    public static function calcularCofins($std)
    {
        #############################################################
        ######### VALIDAR CST #######################################
        #############################################################
        $validarCST = COFINS::validarCstCofins($std->COFINS->CST, $std->ide->tpNF);
        if ($validarCST != 'ok') {
            $std->COFINS->mensagem .= $validarCST . '<br>';
            $std->COFINS->status = 'ERRO';
        }

        if ($std->COFINS->digitado == 'S') {
            #############################################################
            ######### DIGITADO ##########################################
            #############################################################
            // retornar os mesmos dados recebidos
            $std->COFINS->vBC  = $std->COFINS->vBC > 0 ? Formatacao::moedaPonto($std->COFINS->vBC, 2) : '';
            $std->COFINS->pCOFINS = $std->COFINS->pCOFINS > 0 ? Formatacao::moedaPonto($std->COFINS->pCOFINS, 2) : '';
            $std->COFINS->vCOFINS = $std->COFINS->vCOFINS > 0 ? Formatacao::moedaPonto($std->COFINS->vCOFINS, 2) : '';
        } else {
            #############################################################
            ######### CALCULAR ##########################################
            #############################################################
            $vBC = null;
            $aliquota = null;
            $valorImposto = null;
            $reducao_icms = null;
            $CST = $std->COFINS->CST ? $std->COFINS->CST : 'CST não informado';

            // COFINS precisa ser calculado
            if ($CST == '01' || $CST == '02' || $CST == '03' || $CST == '05') {
                if ($std->emit->CRT > 2) {
                    // Realiza o cálculo, pois exige o cálculo de imposto
                    if ($std->COFINS->pCOFINS > 0) {
                        $retirar_valor_icms = 0;
                        if (Formatacao::maiuscula($std->COFINS->retirar_valor_icms) == 'S') {
                            $retirar_valor_icms = $std->ICMS->vICMS;
                            $reducao_icms = " COM REDUCAO DO ICMS";
                        }
                        $vBC = Formatacao::moedaPonto($std->baseProduto - $retirar_valor_icms, 2);
                        if ($vBC > 0) {
                            $aliquota = Formatacao::moedaPonto($std->COFINS->pCOFINS, 2);
                            $valorCalculado = $vBC * $std->COFINS->pCOFINS / 100;
                            $valorImposto = Formatacao::moedaPonto($valorCalculado, 2);

                            $std->COFINS->vBC  = $vBC;
                            $std->COFINS->pCOFINS = $aliquota;
                            $std->COFINS->vCOFINS = $valorImposto;
                            $std->COFINS->retirar_valor_icms = $std->COFINS->retirar_valor_icms;
                            $std->COFINS->status = 'COFINS CALCULADO' . $reducao_icms;
                        } else {
                            $std->COFINS->mensagem .= '0 Valor da base de calculo nao pode ser zerada ou negativa, verifique os valores do produto';
                            $std->COFINS->status = 'ERRO';
                        }
                    } else {
                        $std->COFINS->mensagem .= 'Nao foi informado a aliquota para calcular o cofins';
                        $std->COFINS->status = 'ERRO';
                    }
                } else {
                    $std->COFINS->CST = $CST;
                    $std->COFINS->mensagem = 'Empresa do SIMPLES NACIONAl nao pode calcular COFINS, mude o CST';
                    $std->COFINS->status = 'ERRO';
                }
            } elseif ($CST == '04' || $CST == '06' || $CST == '07' || $CST == '08' || $CST == '09') {
                // Não exige cálculo, pois é isento ou a alíquota é zero
                $std->COFINS->vBC  = '';
                $std->COFINS->pCOFINS = '';
                $std->COFINS->vCOFINS = '';
            } else {
                // CST inválido ou não reconhecido.
                $std->COFINS->mensagem .= "CST {$CST} nao exige caldulo do cofins";
                $std->COFINS->status = 'ERRO';
            }
        }

        return  $std;
    }

    private static function validarCstCofins(string $cst, string $tipoOperacao): string
    {
        // Garantir que o CST tenha dois dígitos
        $cst = str_pad($cst, 2, '0', STR_PAD_LEFT);

        if ($tipoOperacao) {
            // Definição dos CSTs válidos para COFINS
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
            return "CST $cst nao e um CST valido para cofins.";
        }

        // Caso $tipoOperacao seja inválido
        return "Tipo de operacao {$tipoOperacao} invalido. Opcao tpNF validas: 1- saida, 0- entrada";
    }
}
