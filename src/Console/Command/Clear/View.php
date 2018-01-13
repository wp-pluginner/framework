<?php

namespace WpPluginner\Framework\Console\Command\Clear;

use WpPluginner\Framework\Foundation\Console\Command as BaseCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class View extends BaseCommand
{
    protected $signature = 'plugin:clear:view';
    protected $description = 'Clear compiled view files.';

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
