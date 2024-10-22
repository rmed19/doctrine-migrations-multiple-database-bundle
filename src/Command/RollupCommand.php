<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\RollupCommand as DoctrineRollupCommand;

class RollupCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineRollupCommand::class;
    }
}
