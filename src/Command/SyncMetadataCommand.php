<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand as DoctrineSyncMetadataCommand;

class SyncMetadataCommand extends AbstractCommand
{
    protected function commandClass(): string
    {
        return DoctrineSyncMetadataCommand::class;
    }
}
