<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\VersionCommand as DoctrineVersionCommand;

class VersionCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineVersionCommand::class;
    }
}
