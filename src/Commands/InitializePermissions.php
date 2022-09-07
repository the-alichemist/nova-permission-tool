<?php

namespace DigitalCloud\PermissionTool\Commands;

use Illuminate\Console\Command;
use Laravel\Nova\Http\Requests\NovaRequest;
use DigitalCloud\PermissionTool\Services\InitializePermissions as IPS;

class InitializePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:initialize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates permissions for all Nova Resources & Fields';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        (new IPS())->handle((new NovaRequest()));
    }
}
