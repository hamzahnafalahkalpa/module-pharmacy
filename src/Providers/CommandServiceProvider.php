<?php

namespace Hanafalah\ModulePharmacy\Providers;

use Illuminate\Support\ServiceProvider;
use Hanafalah\ModulePharmacy\Commands;

class CommandServiceProvider extends ServiceProvider
{
    protected $__commands = [];

    public function register()
    {
        $this->commands(config('module-pharmacy.commands', $this->__commands));
    }

    public function provides()
    {
        return $this->__commands;
    }
}
