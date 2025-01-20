<?php

namespace ArquiteturaFiscal\Factories;

use ArquiteturaFiscal\Impostos\ImpostoInterface;
use ArquiteturaFiscal\Impostos\ICMS\ICMSPadrao;
use ArquiteturaFiscal\Impostos\ICMS\ICMSRegraPersonalizada;

class ImpostoFactory
{
    public static function criar(string $tipo, array $config): ImpostoInterface
    {
        switch ($tipo) {
            case 'ICMSPadrao':
                return new ICMSPadrao();
            case 'ICMSPersonalizado':
                return new ICMSRegraPersonalizada();
            default:
                throw new \InvalidArgumentException("Tipo de imposto {$tipo} não suportado.");
        }
    }
}
