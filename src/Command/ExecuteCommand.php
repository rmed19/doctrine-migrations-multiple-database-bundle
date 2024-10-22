<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand as DoctrineExecuteCommand;

class ExecuteCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineExecuteCommand::class;
    }
}
