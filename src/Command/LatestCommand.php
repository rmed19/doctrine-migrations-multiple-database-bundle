<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\LatestCommand as DoctrineLatestCommand;

class LatestCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineLatestCommand::class;
    }
}
