<?php
namespace Ipol\Woo\ViaDelivery\Hooks;

use \Ipol\Woo\ViaDelivery\Shipping;

class Kernel
{
    public static function register()
    {
        add_action('plugins_loaded', [static::class, 'loadLocalization']);
        add_action('admin_menu', [static::class, 'loadAdminMenu']);
        add_filter('woocommerce_shipping_methods', [static::class, 'loadShippingMethods']);
        add_filter('nonce_user_logged_out', [static::class, 'nonceUserLoggedOut'], 100, 2);
    }

    /**
     * @return void
     */
    public static function loadLocalization()
    {
        load_textdomain('viadelivery', VIADELIVERY_PLUGIN_PATH.'languages/viadelivery-'.determine_locale().'.mo');
    }

    /**
     * @param array $methods
     * @return []
     */
    public static function loadShippingMethods($methods)
    {
        $methods[Shipping::METHOD_ID] = Shipping::class;

        return $methods;
    }

    public static function loadAdminMenu()
    {
        add_menu_page(
            __('Via.Delivery.US', 'viadelivery'),
            __('Via.Delivery.US', 'viadelivery'),
            'manage_options',
            'viadelivery',
            null,
            null,
            '55.51'
        );

        add_submenu_page(
            'viadelivery', 
            __('Home', 'viadelivery'), 
            __('Home', 'viadelivery'), 
            'manage_options', 
            'viadelivery', 
            [\Ipol\Woo\ViaDelivery\Controllers\AdminSettings::class, 'indexAction']
        );

        add_submenu_page(
            'viadelivery', 
            __('Settings', 'viadelivery'), 
            __('Settings', 'viadelivery'), 
            'manage_options', 
            'admin.php?page=wc-settings&tab=shipping&section=viadelivery',
            null
        );
    }

    /**
     * @param string $uid
     * @param string $action
     * @return string
     */
    public function nonceUserLoggedOut($uid, $action) {
        $session = $GLOBALS['viadelivery']->session();

        if ($session->has_session()) {
            if ($session->get_customer_id()) {
                $uid = $session->get_customer_id();
            }
        }

        return $uid;
    }
}