<?php

namespace WpPluginner\Framework\Console\Command;

use WpPluginner\Framework\Foundation\Console\Command as BaseCommand;

use Illuminate\Support\Str;

class PluginRename extends BaseCommand
{
    protected $signature = 'plugin:rename  {--slug=} {--namespace=} {--rollback}';
    protected $description = 'Rename Plugin Utilities.';
    protected $help = 'This command allows you to rename plugin utilities...';

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
                isset($pluginDevUtilities['replace-rollback'])
            ) {
                $this->pluginDevUtilities['old'] = $pluginDevUtilities['current'];
                $this->pluginDevUtilities['current'] = $pluginDevUtilities['old'];
                $this->pluginDevUtilities['replace'] = $pluginDevUtilities['replace-rollback'];
            } else {
                $this->error("File: developer.json is corrupted.");
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
                unset($this->pluginDevUtilities['old']);
            }
            $this->plugin['files']->put($this->developerFile, json_encode($this->pluginDevUtilities, JSON_PRETTY_PRINT));
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
            'plugin.php',
            'composer.json',
            'composer.lock',
            'README.md',
        ];

        $totalFiles = 0;
        $this->output->write("Processing Other Files...");
        foreach ($files as $key => $file) {
            $totalFiles += $this->replacePluginUtilities($file, $replaceFrom, $replaceTo);
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
            'slug' => $this->plugin['config']->get('plugin.slug','wp_pluginner'),
            'variable' => strtoupper($this->plugin['config']->get('plugin.slug','wp_pluginner')),
            'namespace' => $this->plugin['config']->get('plugin.namespace','WpPluginner')
        ];
        $this->checkPluginNewSlug($this->option('slug'));
        $this->checkPluginNewNamespace($this->option('namespace'));
    }

    protected function checkPluginNewSlug( $slug = false )
    {
        if (!$slug) {
            $slug = $this->ask('Plugin Slug ( Only lowercase and underscore (-) )');
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
        $this->table(
            ['#', 'Current', 'Replace With'],
            [
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
            $data = [];
            foreach ($this->pluginDevUtilities['replace'] as $key => $value) {
                $data[] = [ $key, $value ];
            }
            $this->table(
                ['Replace From', 'Replace To'],
                $data
            );
            return $this->confirm('Do you wish to continue?');
        }
        $this->pluginDevUtilities['replace'] = [];
        return true;

    }

    protected function isJsonContent($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
