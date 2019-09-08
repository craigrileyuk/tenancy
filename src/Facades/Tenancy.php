<?php

declare(strict_types=1);

namespace Stancl\Tenancy\Facades;

use Stancl\Tenancy\TenantManager;
use Illuminate\Support\Facades\Facade;

class Tenancy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TenantManager::class;
    }
}
