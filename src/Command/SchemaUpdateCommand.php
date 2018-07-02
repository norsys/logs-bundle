<?php

namespace Norsys\LogsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Norsys\LogsBundle\Model\SchemaBuilder;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class SchemaUpdateCommand
 */
class SchemaUpdateCommand extends ContainerAwareCommand
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('norsys:logs:schema-update')
            ->setDescription('Update Monolog table from schema')
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
        $connection = $this->getContainer()->get('norsys_logs.doctrine_dbal.connection');
        $tableName = $this->getContainer()->getParameter('norsys_logs.doctrine.table_name');

        $schemaBuilder = $this->getContainer()->get('norsys_logs.model.log_schema_builder');
        $schemaDiffFactory = $this->getContainer()->get('norsys_logs.dbal.schema_diff_factory');

        $sqls = $schemaDiffFactory->getSchemaDiff()->toSql($connection->getDatabasePlatform());

        if (0 === count($sqls)) {
            $output->writeln('Nothing to update - your database is already in sync with the current Monolog schema.');

            return 0;
        }

        $output->writeln(
            '<comment>Warning</comment>: This operation may not be executed in a production environment'
        );
        $output->writeln(sprintf(
            '<info>SQL operations to execute to Monolog table "<comment>%s</comment>":</info>',
            $tableName
        ));

        $output->writeln(implode(';' . PHP_EOL, $sqls));

        if ($input->getOption('force') === false) {
            $helperQuestion = $this->getHelper('question');
            $question = new ConfirmationQuestion('Do you want to execute these SQL operations? (y/N) : ', false);

            if ($helperQuestion->ask($input, $output, $question) === false) {
                return;
            }
        }

        $error = false;
        try {
            $schemaBuilder->update();
            $output->writeln(sprintf(
                '<info>Successfully updated Monolog table "<comment>%s</comment>"! "%s" queries were executed</info>',
                $tableName,
                count($sqls)
            ));
        } catch (\Exception $e) {
            $output->writeln(sprintf(
                '<error>Could not update Monolog table "<comment>%s</comment>"...</error>',
                $tableName
            ));
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $error = true;
        }

        return ($error === true) ? 1 : 0;
    }
}
