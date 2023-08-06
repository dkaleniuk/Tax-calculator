<?php

declare(strict_types=1);

namespace App\Service;

interface CountryCodeProviderInterface
{
    public function getCountryCodeByBin(string $binNumber): string;
}
