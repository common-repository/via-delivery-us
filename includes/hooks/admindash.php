<?php
namespace Ipol\Woo\ViaDelivery\Hooks;

class AdminDash
{
    /**
     * @return void
     */
    public static function register()
    {
        add_action('admin_enqueue_scripts', [static::class, 'loadAssets']);
    }

    /**
     * @return void
     */
    public static function loadAssets()
    {
        wp_register_script('chartjs', 'https://www.chartjs.org/dist/2.9.3/Chart.min.js');
        wp_enqueue_script('jquery');
        wp_enqueue_script('chartjs');
        wp_enqueue_style('via-dashstyle', VIADELIVERY_PLUGIN_URI.'assets/css/dashboard.css');
    }
}