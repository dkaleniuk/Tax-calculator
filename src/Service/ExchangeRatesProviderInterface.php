<?php

declare(strict_types=1);

namespace App\Service;

interface ExchangeRatesProviderInterface
{
    public function get(string $currency): float;
}
