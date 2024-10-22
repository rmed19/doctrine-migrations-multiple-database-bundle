<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\GenerateCommand as DoctrineGenerateCommand;

class GenerateCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineGenerateCommand::class;
    }
}
