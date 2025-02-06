<?php

namespace Divulgueregional\ArquiteturaFiscal;


use stdClass;
use Divulgueregional\ArquiteturaFiscal\Configuracoes\Formatacao;
use Divulgueregional\ArquiteturaFiscal\Prod\Prod;
use Divulgueregional\ArquiteturaFiscal\Ide\Ide;
use Divulgueregional\ArquiteturaFiscal\Emit\Emit;
use Divulgueregional\ArquiteturaFiscal\Impostos\IPI\IPI;
use Divulgueregional\ArquiteturaFiscal\Impostos\ICMS\ICMS;
use Divulgueregional\ArquiteturaFiscal\Impostos\ICMS\ICMSLimpar;
use Divulgueregional\ArquiteturaFiscal\Impostos\PIS\PIS;
use Divulgueregional\ArquiteturaFiscal\Impostos\COFINS\COFINS;

class ArquiteturaFiscal
{
    private $valorProduto;
    private $valorFrete;
    private $valorSeguro;
    private $valorOutros;
    private $valorDesconto;

    private $baseProduto;
    private $std;

    function __construct(stdClass $std)
    {
        $this->std = $std;

        // Inicializando os valores fornecidos 
        $this->valorProduto = $std->prod->valorProduto ? $std->prod->valorProduto : 0;
        $this->valorFrete = $std->prod->valorFrete ?? 0;
        $this->valorSeguro = $std->prod->ValorSeguro ?? 0;
        $this->valorOutros = $std->prod->valorOutros ?? 0;
        $this->valorDesconto = $std->prod->valorDesconto ?? 0;

        if ($std->prod->baseProduto > 0) {
            $this->std->prod->baseProduto = Formatacao::moedaPonto($std->prod->baseProduto, 2);
        }
        $this->baseProduto = Formatacao::moedaPonto(($this->valorProduto + $this->valorFrete + $this->valorSeguro + $this->valorOutros) - $this->valorDesconto);
        $this->std->baseProduto = $this->baseProduto;
    }

    public function calcularImposto()
    {
        $std = $this->prod();
        $std = $this->ide();
        $std = $this->emit();
        $std = $this->ipi();
        $std = $this->icms();
        $std = $this->pis();
        $std = $this->cofins();

        $eesponse = json_encode($std, JSON_PRETTY_PRINT);

        return $eesponse;
    }

    public function prod()
    {
        $this->std->prod->status = 'SUCESSO';
        $this->std->prod->mensagem = '';
        $response = Prod::validarProd($this->std);
        return $response;
    }

    public function ide()
    {
        $this->std->ide->status = 'SUCESSO';
        $this->std->ide->mensagem = '';
        $this->std->ide->tpNFNome = '';
        $response = Ide::validarIde($this->std);
        return $response;
    }

    public function emit()
    {
        $this->std->emit->status = 'SUCESSO';
        $this->std->emit->mensagem = '';
        $this->std->emit->crtNome = '';
        $response = Emit::validarEmit($this->std);
        return $response;
    }
    public function ipi()
    {
        $this->std->IPI->status = 'SUCESSO';
        $this->std->IPI->mensagem = '';
        // print_r($this->std->IPI);
        // die;
        $response = IPI::calcularIpi($this->std);
        return $response;
    }

    public function icms()
    {
        $this->std->ICMS->status = 'SUCESSO';
        $this->std->ICMS->mensagem = '';
        $response = ICMS::calcularICMS($this->std);
        return $response;
    }

    public function pis()
    {
        $this->std->PIS->status = 'SUCESSO';
        $this->std->PIS->mensagem = '';
        $response = PIS::calcularPis($this->std);
        return $response;
    }

    public function cofins()
    {
        $this->std->COFINS->status = 'SUCESSO';
        $this->std->COFINS->mensagem = '';
        $response = COFINS::calcularCofins($this->std);
        return $response;
    }

    public function teste()
    {
        return 'Conexão a arquitetura fiscal realizada com sucesso!!!';
    }

    #########################################################################
    ########## LIMPAR VAZIO RETORNO #########################################
    #########################################################################
    public function limparRetorno($std, $opcao)
    {
        switch ($opcao) {
            case 'ICMS':
                $icms = $std->ICMS;
                // print_r($icms);
                // die;
                $icms->crt = $std->emit->CRT;
                $std = ICMSLimpar::limparIcms($icms);
                unset($std->digitado, $std->status, $std->mensagem, $std->crt);

                return $std;
                break;
            case 'IPI':
                $ipi = $std->IPI;
                unset($ipi->digitado, $ipi->status, $ipi->mensagem);
                // print_r($ipi);
                // die;
                return $this->retirarCamposVazios($ipi);

                break;
            case 'PIS':
                $pis = $std->PIS;
                unset($pis->digitado, $pis->retirar_valor_icms, $pis->status, $pis->mensagem);
                // print_r($pis);
                // die;
                return $this->retirarCamposVazios($pis);

                break;
            case 'COFINS':
                $cofins = $std->COFINS;
                unset($cofins->digitado, $cofins->retirar_valor_icms, $cofins->status, $cofins->mensagem);
                // print_r($pis);
                // die;
                return $this->retirarCamposVazios($cofins);

                break;
            default:
                return null;
                break;
        }
    }

    public function retirarCamposVazios($std)
    {
        // Função para remover propriedades vazias
        foreach ($std as $key => $value) {
            if (empty($value)) {
                unset($std->$key);
            }
        }
        return $std;
    }
}
