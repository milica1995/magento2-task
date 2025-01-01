<?php

declare(strict_types=1);

namespace Younify\OrderExport\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Younify\OrderExport\Model\ExportOrders;

class ExportOrdersCommand extends Command
{
    private $exportOrders;

    public function __construct(ExportOrders $exportOrders)
    {
        parent::__construct();
        $this->exportOrders = $exportOrders;
    }

    protected function configure()
    {
        $this->setName('custom:export:orders')
            ->setDescription('Export orders to CSV')
            ->addOption('status', null, InputOption::VALUE_OPTIONAL, 'Order status (comma-separated, e.g., pending,complete)')
            ->addOption('group', null, InputOption::VALUE_OPTIONAL, 'Customer group')
            ->addOption('start-date', null, InputOption::VALUE_OPTIONAL, 'Start Date (YYYY-MM-DD)', '2024-01-01')
            ->addOption('end-date', null, InputOption::VALUE_OPTIONAL, 'End Date (YYYY-MM-DD)', '2024-12-31');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statuses = $input->getOption('status');
        $group = $input->getOption('group');
        $startDate = $input->getOption('start-date');
        $endDate = $input->getOption('end-date');

        $statusesArray = $statuses ? explode(',', $statuses) : null;

        $fileName = $this->exportOrders->export($statusesArray, $group, $startDate, $endDate);

        if ($fileName) {
            $output->writeln("<info>Export completed: $fileName</info>");
            return Cli::RETURN_SUCCESS;
        }

        $output->writeln("<error>Export failed!</error>");
        return Cli::RETURN_FAILURE;
    }
}