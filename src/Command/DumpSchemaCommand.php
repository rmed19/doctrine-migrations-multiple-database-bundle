<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand as DoctrineDumpSchemaCommand;

class DumpSchemaCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineDumpSchemaCommand::class;
    }
}
