<?php

namespace WpPluginium\Framework\Foundation\Console;

use Symfony\Component\Console\Application as SymfonyApplication;

/**
 *
 */
class Application extends SymfonyApplication
{

    function __construct($name = 'WP Pluginner Artisan', $version = '1.0.0')
    {
        parent::__construct($name,$version);
    }


}
