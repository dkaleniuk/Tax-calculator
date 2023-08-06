<?php

namespace Tests\Command;

use App\Command\CalculateTax;
use App\Service\TaxCalculatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateTaxTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../fixtures/txt/%s';

    private TaxCalculatorInterface $mockTaxCalculator;

    public function setUp(): void
    {
        $this->mockTaxCalculator = $this->createMock(TaxCalculatorInterface::class);
    }

    public function testCalculateTax(): void
    {
        $this->configureTaxCalculatorMock();

        $command = new CalculateTax($this->mockTaxCalculator);

        $application = new Application();
        $application->add($command);

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'payments_info_file' => \sprintf(self::FIXTURES_DIR, 'input.txt')
        ]);

        $tester->assertCommandIsSuccessful();
    }

    public function testCalculateTaxWithoutFile(): void
    {
        $this->configureTaxCalculatorMockWithWrongFile();

        $command = new CalculateTax($this->mockTaxCalculator);

        $application = new Application();
        $application->add($command);

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'payments_info_file' => \sprintf(self::FIXTURES_DIR, 'random.txt')
        ]);

        self::assertEquals('[ERROR] Please provide correct file!', trim($tester->getDisplay()));
    }

    public function testCalculateTaxWithEmptyFile(): void
    {
        $this->configureTaxCalculatorMockWithWrongFile();

        $command = new CalculateTax($this->mockTaxCalculator);

        $application = new Application();
        $application->add($command);

        $application->setAutoExit(false);

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'payments_info_file' => \sprintf(self::FIXTURES_DIR, 'empty_input.txt')
        ]);

        $this->assertStringContainsString('[INFO] Empty row! Moving forward!', $tester->getDisplay());
    }

    private function configureTaxCalculatorMock(): void
    {
        $this->mockTaxCalculator
            ->expects($this->once())
            ->method('calculate')
            ->willReturn('0.46');
    }

    private function configureTaxCalculatorMockWithWrongFile(): void
    {
        $this->mockTaxCalculator
            ->expects($this->never())
            ->method('calculate');
    }
}
