<?php

namespace Oscmarb\MigrationsMultipleDatabase\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class DoctrineMigrationsMultipleDatabaseBundle extends Bundle
{
    public function getParent(): string
    {
        return 'DoctrineMigrationsBundle';
    }
}
