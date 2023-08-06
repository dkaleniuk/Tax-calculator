<?php

declare(strict_types=1);

namespace App\Service;

use App\Common\Enum\EUCountry;
use App\Common\Exception\InvalidArgumentException;
use App\Common\Http\DTO\PaymentDTO;

class TaxCalculator implements TaxCalculatorInterface
{
    private const ROUND_PRECISION = 2;
    private const BASE_CURRENCY = 'EUR';
    private const EU_FEE = 0.01;
    private const NON_EU_FEE = 0.02;

    public function __construct(
        private readonly ExchangeRatesProviderInterface $exchangeRate,
        private readonly CountryCodeProviderInterface $countryCodeProvider,
    ) {
    }

    public function calculate(string $paymentData): string
    {
        $paymentData = $this->parsePaymentData($paymentData);

        $convertedAmount = $this->getConvertedAmount($paymentData);

        $tax = $this->getTaxByCountryCode(
            $this->countryCodeProvider->getCountryCodeByBin($paymentData->getBinNumber())
        );

        return (string) (ceil($tax * $convertedAmount * 100) / 100);
    }

    private function parsePaymentData(string $paymentData): PaymentDTO
    {
        $decodedPayment = \json_decode($paymentData, true);

        if (\is_null($decodedPayment)) {
            throw InvalidArgumentException::createFromMessage('Invalid payment data provided!');
        }

        $missingFields = [];
        foreach (PaymentDTO::REQUIRED_FIELDS as $requiredField) {
            if (!\array_key_exists($requiredField, $decodedPayment)) {
                $missingFields[] = $requiredField;
            }
        }

        if (!empty($missingFields)) {
            throw InvalidArgumentException::createFromMessage(\sprintf('Missing fields in the payment data: %s', \implode(', ', $missingFields)));
        }

        return new PaymentDTO(
            $decodedPayment['bin'],
            $decodedPayment['amount'],
            $decodedPayment['currency']
        );
    }

    private function getConvertedAmount(PaymentDTO $paymentDTO): float
    {
        $amount = \floatval($paymentDTO->getAmount());
        if ($paymentDTO->getCurrency() !== self::BASE_CURRENCY) {
            $exchangeRate = $this->exchangeRate->get($paymentDTO->getCurrency());
            $amount /= $exchangeRate;
        }

        return $amount;
    }

    private function getTaxByCountryCode(string $countryCode): float
    {
        return EUCountry::isEU($countryCode) ? self::EU_FEE : self::NON_EU_FEE;
    }
}
