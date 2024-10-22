<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand as DoctrineUpToDateCommand;

class UpToDateCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineUpToDateCommand::class;
    }
}
