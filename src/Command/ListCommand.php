<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\CurrentCommand as DoctrineCurrentCommand;

class ListCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineCurrentCommand::class;
    }
}
