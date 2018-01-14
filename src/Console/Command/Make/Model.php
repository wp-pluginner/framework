<?php

namespace WpPluginium\Framework\Console\Command\Make;

use WpPluginium\Framework\Foundation\Console\GeneratorCommand;

use Illuminate\Support\Str;

class Model extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'plugin:make:model {name : The name of the class} {--table= : Table name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $table_name = $this->option('table');
        if (!$table_name) {
            $class = str_replace($this->getNamespace($name).'\\', '', $name);
            $table_name = strtolower($this->plugin['config']->get('slug') . '_' .$class);
        }
        return str_replace('DummyTableName', $table_name, parent::buildClass($name));
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../../stubs/model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Model';
    }
}
