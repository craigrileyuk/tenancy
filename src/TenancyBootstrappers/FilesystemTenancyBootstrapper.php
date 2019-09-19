<?php

declare(strict_types=1);

namespace Stancl\Tenancy\TenancyBootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Tenant;

// todo better solution than tenant_asset?

class FilesystemTenancyBootstrapper implements TenancyBootstrapper
{
    protected $originalPaths = [];

    /** @var Application */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->originalPaths = [
            'disks' => [],
            'path' => $this->app->storagePath(),
        ];
    }

    public function start(Tenant $tenant)
    {
        // todo2 revisit this
        $suffix = $this->app['config']['tenancy.filesystem.suffix_base'] . $tenant->id;

        // storage_path()
        $this->app->useStoragePath($this->originalPaths['path'] . "/{$suffix}");

        // Storage facade
        foreach ($this->app['config']['tenancy.filesystem.disks'] as $disk) {
            $this->originalPaths['disks'][$disk] = Storage::disk($disk)->getAdapter()->getPathPrefix();

            if ($root = str_replace('%storage_path%', storage_path(), $this->app['config']["tenancy.filesystem.root_override.{$disk}"])) {
                Storage::disk($disk)->getAdapter()->setPathPrefix($root);
            } else {
                $root = $this->app['config']["filesystems.disks.{$disk}.root"];

                Storage::disk($disk)->getAdapter()->setPathPrefix($root . "/{$suffix}");
            }
        }
    }

    public function end()
    {
        // storage_path()
        $this->app->useStoragePath($this->originalPaths['path']);

        // Storage facade
        foreach ($this->app['config']['tenancy.filesystem.disks'] as $disk) {
            Storage::disk($disk)->getAdapter()->setPathPrefix($this->originalPaths['disks'][$disk]);
        }
    }
}
