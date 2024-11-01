<?php
namespace Ipol\Woo\ViaDelivery\Helpers;

class View 
{

    /**
     * @param string $path
     * @param array  $data
     * @return void
     */
    public static function load($path, array $args = [])
    {
        extract($args);

        ob_start();
        
        require_once static::getPath($path);
        
        return ob_get_clean();
    }

    /**
     * @param string $path
     * @return boolean
     */
    public static function isExists($path)
    {
        return is_file(static::getPath($path));
    }

    /**
     * @param string $path
     * @return void
     */
    public static function getPath($view)
    {
        return VIADELIVERY_PLUGIN_PATH .'views/'. $view .'.php';
    }
}