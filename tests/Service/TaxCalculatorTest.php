<?php

namespace Tests\Service;

use App\Common\Exception\InvalidArgumentException;
use App\Service\CountryCodeProviderInterface;
use App\Service\ExchangeRatesProviderInterface;
use App\Service\TaxCalculator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class TaxCalculatorTest extends TestCase
{
    public function setUp(): void
    {
        if (\method_exists(Dotenv::class, 'bootEnv')) {
            (new Dotenv())->bootEnv(\dirname(__DIR__) . '/../.env');
        }
    }

    public function testCalculateWithEUCountry(): void
    {
        $mockExchangeRateProvider = $this->createMock(ExchangeRatesProviderInterface::class);
        $mockExchangeRateProvider->method('get')->willReturn(1.2);

        $mockCountryCodeProvider = $this->createMock(CountryCodeProviderInterface::class);
        $mockCountryCodeProvider->method('getCountryCodeByBin')->willReturn('LT');

        $taxCalculator = new TaxCalculator($mockExchangeRateProvider, $mockCountryCodeProvider);

        $result = $taxCalculator->calculate('{"bin":"516793","amount":"100.00","currency":"USD"}');

        $this->assertEquals('0.84', $result);
    }

    public function testCalculateWithNonEUCountry(): void
    {
        $mockExchangeRateProvider = $this->createMock(ExchangeRatesProviderInterface::class);
        $mockExchangeRateProvider->method('get')->willReturn(1.2);

        $mockCountryCodeProvider = $this->createMock(CountryCodeProviderInterface::class);
        $mockCountryCodeProvider->method('getCountryCodeByBin')->willReturn('US');

        $taxCalculator = new TaxCalculator($mockExchangeRateProvider, $mockCountryCodeProvider);

        $result = $taxCalculator->calculate('{"bin":"516793","amount":"50.00","currency":"USD"}');

        $this->assertEquals('0.84', $result);
    }

    public function testCalculateWithMissingFieldsAsAmountAndCurrency(): void
    {
        $mockExchangeRateProvider = $this->createMock(ExchangeRatesProviderInterface::class);
        $mockCountryCodeProvider = $this->createMock(CountryCodeProviderInterface::class);

        $taxCalculator = new TaxCalculator($mockExchangeRateProvider, $mockCountryCodeProvider);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing fields in the payment data: amount, currency');

        $taxCalculator->calculate('{"bin":"some_bin"}');
    }

    public function testCalculateWithMissingFieldsAsCurrency(): void
    {
        $mockExchangeRateProvider = $this->createMock(ExchangeRatesProviderInterface::class);
        $mockCountryCodeProvider = $this->createMock(CountryCodeProviderInterface::class);

        $taxCalculator = new TaxCalculator($mockExchangeRateProvider, $mockCountryCodeProvider);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing fields in the payment data: currency');

        $taxCalculator->calculate('{"bin":"516793","amount":"50.00"}');
    }

    public function testCalculateWithMissingFieldsAsData(): void
    {
        $mockExchangeRateProvider = $this->createMock(ExchangeRatesProviderInterface::class);
        $mockCountryCodeProvider = $this->createMock(CountryCodeProviderInterface::class);

        $taxCalculator = new TaxCalculator($mockExchangeRateProvider, $mockCountryCodeProvider);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing fields in the payment data: bin');

        $taxCalculator->calculate('{"amount":"50.00","currency": "USD"}');
    }

    public function testCalculateWithMissingFields(): void
    {
        $mockExchangeRateProvider = $this->createMock(ExchangeRatesProviderInterface::class);
        $mockCountryCodeProvider = $this->createMock(CountryCodeProviderInterface::class);

        $taxCalculator = new TaxCalculator($mockExchangeRateProvider, $mockCountryCodeProvider);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing fields in the payment data: bin, amount, currency');

        $taxCalculator->calculate('{}');
    }

    public function testCalculateWithInvalidPaymentData(): void
    {
        $mockExchangeRateProvider = $this->createMock(ExchangeRatesProviderInterface::class);
        $mockCountryCodeProvider = $this->createMock(CountryCodeProviderInterface::class);

        $taxCalculator = new TaxCalculator($mockExchangeRateProvider, $mockCountryCodeProvider);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid payment data provided!');

        $taxCalculator->calculate('invalid_json');
    }
}
