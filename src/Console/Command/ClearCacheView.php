<?php

namespace WpPluginner\Framework\Console\Command;

use WpPluginner\Framework\Foundation\Console\Command as BaseCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheView extends BaseCommand
{
    protected $signature = 'view:clear';
    protected $description = 'Clear view files compiled.';
    protected $help = 'This command allows you to create a user...';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = $this->plugin->config()->get('view.compiled');
        $clean = $this->plugin['files']->cleanDirectory($path);
        if ($clean) {
            $this->plugin['files']->put($path . '/.gitignore',"*\n!.gitignore\n");
        }
        $this->info('View cache files cleared.');
    }
}
