<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\MigrateCommand as DoctrineMigrateCommand;

class MigrateCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineMigrateCommand::class;
    }
}
