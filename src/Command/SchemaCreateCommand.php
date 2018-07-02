<?php

namespace Norsys\LogsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SchemaCreateCommand
 */
class SchemaCreateCommand extends ContainerAwareCommand
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('norsys:logs:schema-create')
            ->setDescription('Create schema to log Monolog entries')
            ->addOption('force', 'f', null, 'Execute queries');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loggerClosure = function ($message) use ($output) {
            $output->writeln($message);
        };

        $tableName = $this->getContainer()->getParameter('norsys_logs.doctrine.table_name');

        $schemaBuilder = $this->getContainer()->get('norsys_logs.model.log_schema_builder');

        if ($input->getOption('force') === true) {
            try {
                $schemaBuilder->drop($loggerClosure);
                $output->writeln(sprintf(
                    '<info>Dropped table <comment>%s</comment> for Doctrine Monolog connection.</info>',
                    $tableName
                ));
            } catch (\Exception $e) {
                $output->writeln(sprintf(
                    '<info>Dropped table ignored for Doctrine Monolog connection (not necessary).</info>',
                    $tableName
                ));
            }
        }

        try {
            $schemaBuilder->create($loggerClosure);
            $output->writeln(sprintf(
                '<info>Created table <comment>%s</comment> for Doctrine Monolog connection.</info>',
                $tableName
            ));
        } catch (\Exception $e) {
            $output->writeln(sprintf(
                '<error>Could not create table <comment>%s</comment> for Doctrine Monolog connection.</error>',
                $tableName
            ));
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        return 0;
    }
}
