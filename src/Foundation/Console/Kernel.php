<?php

namespace WpPluginner\Framework\Foundation\Console;

use WpPluginner\Framework\Loader;

abstract class Kernel
{
    protected $plugin;
    protected $application;
    protected $commands = [];

    public function __construct( $namespace = null )
    {
        $this->plugin = $this->getPluginInstance($namespace);
        $this->application = new Application();
        $this->addFrameworkBaseCommands();
        $this->addPluginCommands();
    }

    protected function getPluginInstance($namespace)
    {
        $namespace = is_null($namespace) ? (new \ReflectionClass(get_class($this)))->getNamespaceName() : $namespace;
        return Loader::getInstance($namespace);
    }

    protected function addFrameworkBaseCommands()
    {
        $classCommands = [
            'WpPluginner\Framework\Console\Command\Rename',
            'WpPluginner\Framework\Console\Command\Clear\View',
            'WpPluginner\Framework\Console\Command\Make\Controller',
            'WpPluginner\Framework\Console\Command\Make\Model',
        ];
        foreach ($classCommands as $classCommand) {
            $this->application->add($this->plugin->make($classCommand));
        }
    }

    public function handle()
    {
        return $this->application->run();
    }



    protected function addPluginCommands()
    {
        if (is_array($this->commands)) {
            foreach ($this->commands as $classCommand) {
                $this->application->add($this->plugin->make($classCommand));
            }
        }
    }
}
