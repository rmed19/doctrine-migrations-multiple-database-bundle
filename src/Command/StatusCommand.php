<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\StatusCommand as DoctrineStatusCommand;

class StatusCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineStatusCommand::class;
    }
}
