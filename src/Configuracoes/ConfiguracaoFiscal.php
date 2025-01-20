<?php

namespace ArquiteturaFiscal\Configuracoes;

class ConfiguracaoFiscal
{
    public static function obterConfiguracao(array $dados): string
    {
        // Determina qual cálculo usar com base no CRT ou outras condições
        if ($dados['crt'] === 'Simples Nacional') {
            return 'ICMSPadrao';
        } elseif ($dados['contadorRegraEspecial']) {
            return 'ICMSPersonalizado';
        }

        throw new \Exception('Nenhuma configuração válida encontrada.');
    }
}
