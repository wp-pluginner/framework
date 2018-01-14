<?php

namespace WpPluginium\Framework;

use WpPluginium\Framework\Provider\ConfigServiceProvider;
use WpPluginium\Framework\Provider\DatabaseServiceProvider;
use WpPluginium\Framework\Provider\DeveloperServiceProvider;
use WpPluginium\Framework\Provider\PluginOptionsServiceProvider;
use WpPluginium\Framework\Provider\ViewServiceProvider;
use WpPluginium\Framework\Provider\WpServiceProvider;


use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Routing\RoutingServiceProvider;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\Debug\Exception\FatalThrowableError;

if ( ! defined( 'ABSPATH' ) ) exit;

class Loader
{

    protected static $instances = [];
    protected $file;

    public $plugin;

    public function __construct( $pluginFile )
    {
        $this->file = $pluginFile;
        $this->bootFramework();

        return $this;
    }

    protected function bootFramework()
    {

        $this->plugin = new Container($this->file);
        with(new ConfigServiceProvider($this->plugin))->register();

        with(new FilesystemServiceProvider($this->plugin))->register();
        with(new EventServiceProvider($this->plugin))->register();

        if($this->plugin['config']->get('plugin.cache_enabled')){
            with(new CacheServiceProvider($this->plugin))->register();
        }
        if($this->plugin['config']->get('plugin.session_enabled')){
            with(new SessionServiceProvider($this->plugin))->register();
        }
        if($this->plugin['config']->get('plugin.route_enabled')){
            with(new RoutingServiceProvider($this->plugin))->register();
        }

        with(new DatabaseServiceProvider($this->plugin))->register();
        with(new PluginOptionsServiceProvider($this->plugin))->register();
        with(new ViewServiceProvider($this->plugin))->register();
        with(new WpServiceProvider($this->plugin))->register();


        $this->plugin->instance('request', Request::capture());
        $this->plugin->instance('response', new Response());
        $this->setInstance();
    }

    private function setInstance()
    {
        self::$instances[($this->plugin['config']->get('plugin.namespace'))] = $this->plugin;
    }

    public static function getInstance( $namespace )
    {
        return self::$instances[(explode('\\', $namespace))[0]];
    }

    public function bootPlugin()
    {
        try {
		    $this->plugin->registerConfiguredProviders();

            register_activation_hook( $this->file, [ $this, 'pluginActivationHook' ] );
            register_deactivation_hook( $this->file, [ $this, 'pluginDeactivationHook' ] );

	    } catch (\Exception $e) {
		    $this->plugin->reportException($e);
		    $this->plugin->renderException($this->plugin['request'], $e);
	    } catch (\Throwable $e) {
		    $this->plugin->reportException($e = new FatalThrowableError($e));
		    $this->plugin->renderException($this->plugin['request'], $e);
	    }


        add_action( 'init', array( $this, 'pluginInitAction' ) );

        $this->setInstance();
    }

    public function pluginActivationHook()
    {
        $this->plugin->option()->delta();
        require_once $this->plugin->wp_property_path . '/hook/activation.php';

    }

    public function pluginDeactivationHook()
    {
         require_once $this->plugin->wp_property_path . '/hook/deactivation.php';
    }

    public function pluginInitAction()
    {
        try {

            if ($slug = $this->plugin['config']->get('plugin.development',false)) {
                with(new DeveloperServiceProvider($this->plugin))->register();
            }
    		if ($this->plugin->bound('router')) {
                $this->plugin->loadRoutes();
                if(!is_admin()) {
                    add_action('template_include', function ($template) {
        				//Save Plugin Instance
        				$this->setInstance();
        				if ($this->plugin['config']->get('routes.loading') == 'eager') {
        					$this->plugin->routeRequest();
        				}elseif(is_404()){
        					$this->plugin->routeRequest();
        				}
        				return $template;
        			});
                }
    		}


	    }catch (\Exception $e) {
		    $this->plugin->reportException($e);
		    $this->plugin->renderException($this->plugin['request'], $e);
	    } catch (\Throwable $e) {
		    $this->plugin->reportException($e = new FatalThrowableError($e));
		    $this->plugin->renderException($this->plugin['request'], $e);
	    }
    }
}
