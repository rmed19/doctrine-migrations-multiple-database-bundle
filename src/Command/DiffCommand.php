<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\DiffCommand as DoctrineDiffCommand;

class DiffCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineDiffCommand::class;
    }
}
