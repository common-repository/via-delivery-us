<?php
namespace Ipol\Woo\ViaDelivery\Hooks;

use Ipol\Woo\ViaDelivery\Shipping;

class FrontOrder
{
    public static function register()
    {
        add_action('wp_enqueue_scripts',                     [static::class, 'loadAssets']);
        add_action('woocommerce_after_order_notes',          [static::class, 'addFields']);
        add_action('woocommerce_cart_totals_after_shipping', [static::class, 'addFields']);
        add_action('woocommerce_after_checkout_validation',  [static::class, 'validateFields'], 20, 2);
        add_action('woocommerce_checkout_update_order_meta', [static::class, 'saveFields'], 10, 2);
    }

    /**
     * @return void
     */
    public function loadAssets($hook)
    {
        $dir = explode('/', __DIR__);
        array_pop($dir);
        array_pop($dir);
        $filename = implode('/', $dir) . '/assets/js.log';

        if (get_option('woocommerce_viadelivery_settings', ['debug_mode' => ''] )['debug_mode'] == 'yes')
            file_put_contents($filename, date('d.m.Y H:i') . PHP_EOL .
                'File ' . VIADELIVERY_PLUGIN_URI . 'assets/js/front.js ' .
                (file_exists('/' . implode('/', $dir) . '/assets/js/front.js') ? '' : 'NOT ') . 'exists' . PHP_EOL .
                '------' . PHP_EOL);
        else if (file_exists($filename))
            unlink($filename);

        wp_enqueue_script('via-front', VIADELIVERY_PLUGIN_URI.'assets/js/front.js?'. mt_rand(), ['jquery']);
        wp_enqueue_style('via-front', VIADELIVERY_PLUGIN_URI.'assets/css/front.css?'. mt_rand());
    }

    /**
     * @return void
     */
    public static function addFields()
    {
        print '<input type="hidden" id="via_selected_point" name="via_selected_point" value="">';
        print '<input type="hidden" id="via_selected" name="via_selected" value="">';
        print '<input type="hidden" id="via_iframe_url" name="via_iframe_url" value="'. static::getMapUrlSelf() .'">';
        print '<input type="hidden" id="via_save_point_url" name="via_save_point_url" value="'. static::getSavePointUrl() .'">';
        print '<input type="hidden" id="via_select_button_text" name="via_select_button_text" value="'. static::getSelectButtonTitle() .'">';
        print '<input type="hidden" id="via_select_button_css" name="via_select_button_css" value="'. static::getSelectButtonCss() .'">';
    }

    /**
     * @param [type] $data
     * @param [type] $errors
     * @return void
     */
    public static function validateFields($data, $errors)
    {
        if (empty(sanitize_text_field($_POST['via_selected']))) {
            return ;
        }

        $session = $GLOBALS['viadelivery']->session();
        $pointId = sanitize_text_field($_POST['via_selected_point']) ?:
            ($session->get('via_selected_point') ?: $session->get('via_preview_point')) ;

        if (empty($pointId)) {
            $errors->add('dpd_terminal_code',  __( 'Please, select point.', 'viadelivery'), 'error');
        }
    }

    /**
     * @return void
     */
    public static function saveFields($orderId, $posted)
    {
        // update_post_meta($orderId, 'via_selected_point', sanitize_text_field($_POST['via_selected_point']));
    }

    /**
     * @return string
     */
    protected function getMapUrl()
    {
        $cart = $GLOBALS['woocommerce']->cart;
        $shippingMethod = new Shipping(0);

        return $shippingMethod->getMapUrl([
            'contents'       => $cart->cart_contents,
            'cart_subtotal'  => $cart->subtotal,
            'payment_method' => '_PAYMENT_METHOD_',
            'destination'    => [
                'street'  => '_STREET_',
                'country' => '_COUNTRY_',
                'state'   => '_REGION_',
                'city'    => '_CITY_',
                'zip_code'=> '_ZIP_CODE_',
            ],
        ]);
    }

    /**
     * @return self
     */
    protected function getMapUrlSelf()
    {
        return '/wp-json/woo-viadelivery/map/?'. http_build_query([
            'payment_method' => '_PAYMENT_METHOD_',
            'street'         => '_STREET_',
            'country'        => '_COUNTRY_',
            'region'         => '_REGION_',
            'city'           => '_CITY_',
            'zip_code'       => '_ZIP_CODE_',
            '_wpnonce'       => wp_create_nonce( 'wp_rest' ),
        ]);
    }

    /**
     * @return self
     */
    protected function getSavePointUrl()
    {
        return '/wp-json/woo-viadelivery/map/save_point?'. http_build_query([
            '_wpnonce' => wp_create_nonce( 'wp_rest' ),
        ]);
    }

    /**
     * @return string
     */
    protected function getSelectButtonTitle()
    {
        $shippingMethod = new Shipping(0);

        return $shippingMethod->get_option('select_pickpoint_button_text') ?: __('Select pickpoint', 'viadelivery');
    }

    /**
     * @return string
     */
    protected function getSelectButtonCss()
    {
        $shippingMethod = new Shipping(0);

        return $shippingMethod->get_option('select_pickpoint_button_css');
    }
}