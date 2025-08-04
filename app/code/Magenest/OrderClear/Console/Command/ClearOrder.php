<?php
declare(strict_types=1);

namespace Magenest\OrderClear\Console\Command;

use Magenest\OrderClear\Model\OrderManagementInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearOrder extends Command
{
    private const STATUS_OPTION = 'status';
    private OrderManagementInterface $orderManagement;

    public function __construct(
        OrderManagementInterface $orderManagement,
        string $name = null
    ) {
        parent::__construct($name);
        $this->orderManagement = $orderManagement;
    }

    protected function configure()
    {
        $this->setName('order:clear')
            ->setDescription('Cancel orders with specific status not updated in last hour')
            ->addOption(
                self::STATUS_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                'Order status to clear'
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $status = $input->getOption(self::STATUS_OPTION);
            
            if (!$status) {
                $output->writeln('<error>Status parameter is required</error>');
                return Command::FAILURE;
            }

            $cancelledCount = $this->orderManagement->cancelOrdersByStatus($status);
            
            $output->writeln(sprintf(
                '<info>Successfully cancelled %d order(s) with status "%s" that were not updated in the last hour.</info>',
                $cancelledCount,
                $status
            ));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}