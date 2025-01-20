<?php

namespace ArquiteturaFiscal\Impostos;

interface ImpostoInterface
{
    public function calcular(array $dados): float;
}
