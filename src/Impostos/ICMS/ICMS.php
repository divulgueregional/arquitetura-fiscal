<?php

// namespace ArquiteturaFiscal\Impostos\ICMS;
namespace Divulgueregional\ArquiteturaFiscal\Impostos\ICMS;

use Divulgueregional\ArquiteturaFiscal\Configuracoes\Formatacao;
use Divulgueregional\ArquiteturaFiscal\Impostos\ICMS\ICMS00;

use PhpParser\Node\Stmt\Return_;
use stdClass;

class ICMS
{
    /**
     * Calcula o valor do ICMS com base nos dados fornecidos.
     *
     * @param array $dados Dados necessários para o cálculo do ICMS.
     * @param stdClass $std Objeto padrão para armazenar os resultados.
     * @return stdClass Retorna o objeto com os valores calculados.
     */
    public static function calcularICMS(stdClass $std)
    {
        // print_r($dados);
        // print_r($std);
        $digitado_icms = Formatacao::maiuscula($std->ICMS->digitado);
        $std->ICMS->digitado = $digitado_icms;

        #############################################################
        ######### VALIDAR ORIGEM PRODUTO ############################
        #############################################################
        $orig = ICMS::validarOrig($std->ICMS->orig);
        if ($orig != 'valido') {
            $std->ICMS->status = 'ERRO';
            $std->ICMS->mensagem .= $orig . '<br>';
        }

        #############################################################
        ######### VALIDAR CST ICMS ##################################
        #############################################################
        $cstIcms = ICMS::validarCstIcms($std);
        if ($cstIcms != 'ok') {
            $std->ICMS->status = 'ERRO';
            $std->ICMS->mensagem .= $cstIcms . '<br>';
        }

        #############################################################
        ######### VALIDAR modBC #####################################
        #############################################################
        if (isset($std->ICMS->modBC)) {
            $modBC = ICMS::validarModBC($std->ICMS->modBC);
            if ($modBC != 'ok') {
                $std->ICMS->status = 'ERRO';
                $std->ICMS->mensagem .= $modBC . '<br>';
            }
        }

        $std->emit->CRT = $std->emit->CRT ?? '';
        if ($std->emit->CRT == 1) {
            // Simples Nacional: Usar CSOSN
            // print_r($std);
        } else {
            if ($digitado_icms == 'S') {
                #############################################################
                ######### DIGITADO ##########################################
                #############################################################
                // retornar os mesmos dados recebidos
                if ($std->ICMS->CST == '00') {
                    $std->ICMS->vBC  = (float) $std->ICMS->vBC > 0 ? Formatacao::moedaPonto((float)$std->ICMS->vBC) : '';
                    $std->ICMS->pICMS = (float) $std->ICMS->pICMS;
                    $std->ICMS->vICMS = (float) $std->ICMS->vICMS > 0 ? Formatacao::moedaPonto($std->ICMS->vICMS) : '';
                }
            } else {
                #############################################################
                ######### CALCULAR ##########################################
                #############################################################
                if ($std->ICMS->CST == '00') {
                    $std = ICMS00::calcularICMS00($std);
                }
            }
        }

        return $std;
    }

    public static function validarOrig($orig)
    {
        // Verifica se o valor está vazio
        if ($orig === '' || $orig === null) {
            return "Obrigatorio preencher o valor de orig.";
        }

        // Verifica se o valor é numérico e está dentro do intervalo permitido
        if (!is_numeric($orig) || $orig < 0 || $orig > 8) {
            return "Valor nao aceito. O valor de orig deve estar entre 0 e 8.";
        }

        // Se tudo estiver correto, retorna true ou uma mensagem de sucesso
        return "valido";
    }

    private static function validarCstIcms(stdClass $std): string
    {
        if ($std->emit->CRT > 1) {
            // Verificar se o CST tem exatamente dois dígitos
            if (strlen($std->ICMS->CST) !== 2) {
                return "CST invalido, CST ICMS tem 2 digitos.";
            }

            // Definição dos CSTs válidos para ICMS (Regime Normal)
            $cstsIcms = ['00', '10', '20', '30', '40', '41', '50', '51', '60', '70', '90'];

            // Verifica se o CST está na lista de ICMS
            if (in_array($std->ICMS->CST, $cstsIcms, true)) {
                return 'ok';
            }

            return "CST {$std->ICMS->CST} não é um CST válido para ICMS.";
        }

        return 'ok';
    }

    public static function validarModBC($modBC)
    {
        // Verifica se o valor está vazio
        if ($modBC === '' || $modBC === null) {
            return "Obrigatorio preencher o valor de modBC.";
        }

        // Verifica se o valor é numérico e está dentro do intervalo permitido
        if (!is_numeric($modBC) || $modBC < 0 || $modBC > 3) {
            return "Valor nao aceito. O valor de modBC deve estar entre 0 e 3.";
        }

        // Se tudo estiver correto, retorna true ou uma mensagem de sucesso
        return "ok";
    }
}
