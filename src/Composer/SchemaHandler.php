<?php

namespace Norsys\LogsBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;
use Composer\Script\Event;

/**
 * Class SchemaHandler
 */
class SchemaHandler extends ScriptHandler
{
    /**
     * Logs schema create
     *
     * @param Event $event
     */
    public static function logsSchemaCreate(Event $event)
    {
        $options = static::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'create logs schema');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'norsys:logs:schema-create --force', $options['process-timeout']);
    }

    /**
     * Logs schema create
     *
     * @param Event $event
     */
    public static function logsSchemaUpdate(Event $event)
    {
        $options = static::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'update logs schema');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'norsys:logs:schema-update --force', $options['process-timeout']);
    }
}
