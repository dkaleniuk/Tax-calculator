<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\TaxCalculatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'calculate-tax')]
class CalculateTax extends Command
{
    private const INPUT_FILE_ARGUMENT = 'payments_info_file';

    public function __construct(
        private readonly TaxCalculatorInterface $taxCalculator,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Calculate tax for batch of transactions from the file')
            ->addArgument(self::INPUT_FILE_ARGUMENT, InputArgument::REQUIRED, 'File with payment data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fileName = $input->getArgument(self::INPUT_FILE_ARGUMENT);

        if (!\is_file($fileName) || !\is_readable($fileName)) {
            $io->error('Please provide correct file!');

            return Command::FAILURE;
        }

        foreach (\file($fileName) as $paymentDataRow) {
            if (empty(\trim($paymentDataRow))) {
                $io->info('Empty row! Moving forward!');
                continue;
            }

            try {
                $io->text($this->taxCalculator->calculate($paymentDataRow));
            } catch (\Throwable $throwable) {
                $io->caution($throwable->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
