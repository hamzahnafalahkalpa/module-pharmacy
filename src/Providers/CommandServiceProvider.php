<?php

namespace Zahzah\ModulePharmacy\Providers;

use Illuminate\Support\ServiceProvider;
use Zahzah\ModulePharmacy\Commands;

class CommandServiceProvider extends ServiceProvider
{
    protected $__commands = [
        
    ];

    public function register(){
        $this->commands(config('module-pharmacy.commands',$this->__commands));
    }

    public function provides(){
        return $this->__commands;
    }
}
