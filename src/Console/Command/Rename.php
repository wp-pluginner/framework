<?php

namespace WpPluginium\Framework\Console\Command;

use WpPluginium\Framework\Foundation\Console\Command as BaseCommand;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class Rename extends BaseCommand
{
    protected $name = 'plugin:rename';
    protected $description = 'Rename Plugin Utilities.';

    protected $pluginDevUtilities = [];
    protected $developerFile;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $continue = true;
        $this->rollback = $this->option('rollback');
        $this->loadPluginDevUtilities();
        if ($this->rollback) {
            $pluginDevUtilities = $this->pluginDevUtilities;
            if (
                isset($pluginDevUtilities['current']) &&
                isset($pluginDevUtilities['old']) &&
                isset($pluginDevUtilities['replace']) &&
                isset($pluginDevUtilities['replace-rollback'])
            ) {
                $this->pluginDevUtilities['old'] = $pluginDevUtilities['current'];
                $this->pluginDevUtilities['current'] = $pluginDevUtilities['old'];
                $this->pluginDevUtilities['replace'] = $pluginDevUtilities['replace-rollback'];
            } else {
                $this->error("File: developer.json is corrupted.");
                $continue = false;
            }

        } else {
            $this->loadPluginUtilitiesToProcess();
        }
        if ($continue) {
            $continue = $this->printConfirmablePluginUtilitiesTable();
        }
        if ($continue) {
            $continue = $this->printConfirmablePluginNamespaceTable();
        }
        if ($continue) {
            $this->renamePluginUtilities();
            $res = `composer dump-autoload --optimize`;
            if ($this->pluginDevUtilities['old']['slug'] != $this->pluginDevUtilities['current']['slug']) {
                $res = `npm run dev`;
            }

            if (!$this->rollback) {
                $replaceRollback = [];
                if (
                    isset($this->pluginDevUtilities['replace']) &&
                    is_array($this->pluginDevUtilities['replace'])
                ) {

                    foreach ($this->pluginDevUtilities['replace'] as $key => $val) {
                        $replaceRollback[$val] = $key;
                    }
                    $replaceRollback = array_reverse($replaceRollback);
                }
                $this->pluginDevUtilities['replace-rollback'] = $replaceRollback;
            } else {
                $this->pluginDevUtilities['replace'] = $pluginDevUtilities['replace'];
                unset($this->pluginDevUtilities['old']);
                unset($this->pluginDevUtilities['replace-rollback']);
            }
            $this->plugin['files']->put($this->developerFile, json_encode($this->pluginDevUtilities, JSON_PRETTY_PRINT));

            $this->info("Process Complete... Check the result from your Wordpress Dashboard.");
            if (!$this->rollback) {
                if ($this->confirm("Type yes to rolling back, if fatal error occured.")) {
                    $this->call("plugin:rename", ['--rollback' => true]);
                }
            }
        }
    }

    protected function renamePluginUtilities()
    {
        $replaceFrom = $replaceTo = [];
        if (
            isset($this->pluginDevUtilities['replace']) &&
            is_array($this->pluginDevUtilities['replace'])
        ) {
            $replaceFrom = array_keys($this->pluginDevUtilities['replace']);
            $replaceTo = array_values($this->pluginDevUtilities['replace']);
        }

        $directories = [
            'app' => $this->plugin->app_path,
            'bootstrap' => $this->plugin->bootstrap_path,
            'config' => $this->plugin->config_path,
            'public' => $this->plugin->public_path,
            'resource' => $this->plugin->resource_path,
            'wp-property' => $this->plugin->wp_property_path,
            'vendor' => $this->plugin->vendor_path,
        ];

        foreach ($directories as $dirName => $path) {
            $this->output->write("Processing Directory: <comment>{$dirName}</comment>...");
            $totalFiles = $this->replacePluginUtilities($path, $replaceFrom, $replaceTo);
            $this->output->writeln("<info>OK ({$totalFiles} Files Processed)</info>");
        }

        $files = [
            '/plugin.php',
            '/functions.php',
            '/composer.json',
            '/composer.lock',
            '/README.md',
            '/artisan',
        ];

        $totalFiles = 0;
        $this->output->write("Processing Other Files...");
        foreach ($files as $key => $file) {
            $totalFiles += $this->replacePluginUtilities($this->plugin->base_path . $file, $replaceFrom, $replaceTo);
        }
        $this->output->writeln("<info>OK ({$totalFiles} Files Processed)</info>");
    }

    protected function replacePluginUtilities( $path, $replaceFrom = [], $replaceTo = [], $totalFiles = 0 )
    {
        if ($this->plugin['files']->exists($path)) {
            if ( $this->plugin['files']->isDirectory($path) ) {
                $files = $this->plugin['files']->glob($path . '/*');
                foreach ($files as $file) {
                    $totalFiles += $this->replacePluginUtilities($file, $replaceFrom, $replaceTo);
                }
            } else {
                $content = $this->plugin['files']->get($path);

                $content = str_replace($replaceFrom, $replaceTo, $content);
                $content = str_replace($this->pluginDevUtilities['old'], $this->pluginDevUtilities['current'], $content);


                $this->plugin['files']->put($path, $content);
                $totalFiles += 1;
            }
        }
        return $totalFiles;
    }

    protected function loadPluginDevUtilities()
    {

        $this->developerFile = $this->plugin->base_path . '/developer.json';
        if ($this->plugin['files']->exists($this->developerFile)) {
            $developerContent = $this->plugin['files']->get($this->developerFile);
            if ($this->isJsonContent($developerContent)) {
                $this->pluginDevUtilities = json_decode($developerContent, true);
            }
        }
    }

    protected function loadPluginUtilitiesToProcess()
    {
        $this->pluginDevUtilities['old'] = [
            'name' => $this->plugin['config']->get('plugin.name'),
            'slug' => $this->plugin['config']->get('plugin.slug'),
            'variable' => strtoupper($this->plugin['config']->get('plugin.slug')),
            'namespace' => $this->plugin['config']->get('plugin.namespace')
        ];
        $this->checkPluginNewName($this->option('name'));
        $this->checkPluginNewSlug($this->option('slug'));
        $this->checkPluginNewNamespace($this->option('namespace'));
    }

    protected function checkPluginNewName( $name = false )
    {
        if (!$name) {
            $name = $this->ask('Plugin Name');
        }
        $this->pluginDevUtilities['current']['name'] = $name;

    }

    protected function checkPluginNewSlug( $slug = false )
    {
        if (!$slug) {
            $slug = $this->ask('Plugin Slug ( Only lowercase and underscore ( _ ) )');
        }
        $this->pluginDevUtilities['current']['slug'] = Str::slug($slug, '_');
        $this->pluginDevUtilities['current']['variable'] = strtoupper($this->pluginDevUtilities['current']['slug']);

    }

    protected function checkPluginNewNamespace( $namespace = false )
    {
        if (!$namespace) {
            $namespace = $this->ask('Plugin Namespace ( Only letters )');
        }
        $this->pluginDevUtilities['current']['namespace'] = $namespace;

    }

    protected function printConfirmablePluginUtilitiesTable()
    {
        if ($this->option('force')) return true;
        $this->table(
            ['#', 'Current', 'Replace With'],
            [
                [
                    'Plugin Name',
                    $this->pluginDevUtilities['old']['name'],
                    $this->pluginDevUtilities['current']['name'],
                ],
                [
                    'Plugin Slug',
                    $this->pluginDevUtilities['old']['slug'],
                    $this->pluginDevUtilities['current']['slug'],
                ],
                [
                    'Plugin Namespace',
                    $this->pluginDevUtilities['old']['namespace'],
                    $this->pluginDevUtilities['current']['namespace'],
                ],
            ]
        );
        return $this->confirm('Do you wish to continue?');
    }

    protected function printConfirmablePluginNamespaceTable()
    {
        if (
            isset($this->pluginDevUtilities['replace']) &&
            is_array($this->pluginDevUtilities['replace'])
        ) {
            if ($this->option('force')) return true;
            $data = [];
            foreach ($this->pluginDevUtilities['replace'] as $key => $value) {
                $data[] = [ $key, $value ];
            }
            $this->table(
                ['Replace From', 'Replace To'],
                $data
            );
            return $this->confirm('Do you wish to continue?');
        } else {
            $this->pluginDevUtilities['replace'] = [];
        }

        return true;

    }

    /**
     * Verify json content.
     *
     * @param  string  $string
     *
     * @return boolean
     */
    protected function isJsonContent( $string ) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['name', null, InputOption::VALUE_OPTIONAL, 'New plugin name.'],
            ['slug', null, InputOption::VALUE_OPTIONAL, 'New plugin slug, Only lowercase and underscore ( _ ).'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'New plugin namespace, Only letters ( no space, no special chars ).'],
            ['rollback', null, InputOption::VALUE_NONE, 'Rolling back the codes to WP-Pluginner.'],
            ['force', null, InputOption::VALUE_NONE, 'Do not show confirmation, just execute the cammand.'],
        ];
    }
}
