<?php
namespace Ipol\Woo\ViaDelivery;

class Router
{
    protected static $instance;

    protected $request;

    /**
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance = static::$instance ?: new static();
    }

    protected function __construct()
    {
        $this->init();
    }

    /**
     * @return void
     */
    protected function init()
    {
        $routes = $this->getRoutes();

        add_action( 'rest_api_init', function() use ($routes) {
            foreach ($routes as $path => $params) {
                register_rest_route('woo-viadelivery', $path, $params);
            }
        });
    }

    /**
     * @return array
     */
    protected function getRoutes()
    {
        return array_merge([], 
            Controllers\Map::getActions(),
            Controllers\AdminOrder::getActions(),
            Controllers\AdminSettings::getActions(),

            []
        );
    }
}