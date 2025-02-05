# DOCUMENTAÇÃO ARQUITETURA FISCAL

## Introdução

Projeto tem o objetivo de retornar as informações sobre os impostos para alimentar o XML da NF-e e NFC-e.

## Documentação

- com intuito de estudo e/ou aplicação, siga a documentação<br>
- Por enquanto estamos colocando em produção SN e por isso o icms ainda não foi liberado.

## Uso

- preencha os dados que será retornado a resposta.

```php
    require_once '../../../vendor/autoload.php';

    use Divulgueregional\ArquiteturaFiscal\ArquiteturaFiscal;

    $CRT = 1; // infome o crt
    ######################################################
    ####### CARREGAR DADOS ###############################
    ######################################################
    $std = new stdClass(); // Inicializa o objeto $dados

    // Define o bloco produtos
    $std->prod = new stdClass();
    $std->prod->orig = 0;
    $std->prod->valorProduto = '100.00';
    $std->prod->valorFrete = '0.00';
    $std->prod->valorSeguro = '0.00';
    $std->prod->valorOutros = '0.00';
    $std->prod->valorDesconto = '0.00';
    $std->prod->baseProduto = '0.00'; // não precisa informar
    $std->prod->cfop = '5102';

    // Define os blocos ide
    $std->ide = new stdClass();
    $std->ide->tpNF = 1; // 1- nota de saída | 0- nota de entrada (tipoOperacao)

    // Define os blocos emit
    $std->emit = new stdClass();
    $std->emit->CRT = $CRT; // 1- nota de saída | 0- nota de entrada (tipoOperacao)

    // Define o bloco ICMS com base no valor de $CRT
    $std->ICMS = new stdClass();
    if ($CRT == 1) {
        // Simples Nacional
        $std->ICMS->digitado = 'N'; // S- retorna o digitado | N- calcular
        $std->ICMS->orig = 0;
        $std->ICMS->CSOSN = 102;
    } else {
        // Outro regime (não Simples Nacional)
        $std->ICMS->digitado = 'N'; // S- retorna o digitado | N- calcular
        $std->ICMS->orig = 0;

        $std->ICMS->CST = "01";
        $std->ICMS->modBC = 3;
        $std->ICMS->vBC = null;
        $std->ICMS->pICMS = '17.00';
        $std->ICMS->vICMS = null;
    }

    // Adiciona o bloco PIS
    $std->IPI = new stdClass();
    $std->IPI->digitado = 'N'; // S- retorna o digitado | N- calcular
    $std->IPI->cEnq = 999;
    $std->IPI->CST = '53';
    $std->IPI->vBC = null;
    $std->IPI->pIPI = '0.00'; // aliquota
    $std->IPI->vIPI = null;

    // Adiciona o bloco PIS
    $std->PIS = new stdClass();
    $std->PIS->digitado = 'N'; // S- retorna o digitado | N- calcular
    $std->PIS->retirar_valor_icms = 'N'; // S- retirar vICMS da base de calculo | N- pegar a base do produto
    $std->PIS->CST = '07';
    $std->PIS->vBC = null;
    $std->PIS->pPIS = '0.00';
    $std->PIS->vPIS = null;

    // Adiciona o bloco COFINS
    $std->COFINS = new stdClass();
    $std->COFINS->digitado = 'N'; // S- retorna o digitado | N- calcular
    $std->COFINS->retirar_valor_icms = 'N'; // S- retirar vICMS da base de calculo | N- pegar a base do produto
    $std->COFINS->CST = '07';
    $std->COFINS->vBC = null;
    $std->COFINS->pCOFINS = '0.00'; // aliquota
    $std->COFINS->vCOFINS = null;
    ######################################################
    ####### FIM - CARREGAR DADOS #########################
    ######################################################

    $imposto = new ArquiteturaFiscal($std);
    $response = $imposto->calcularImposto();
    echo "<br><br>Dados Recebidos<br>";
    // print_r($response);
    $obj = json_decode($response); // Exibe um objeto
    $array = json_decode($response, true); // Exibe o array
```
