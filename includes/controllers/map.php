<?php
namespace Ipol\Woo\ViaDelivery\Controllers;

use Ipol\Woo\ViaDelivery\Shipping;

class Map
{
    /**
     * @return array
     */
    public static function getActions()
    {
        return [
            '/map/' => [
                'methods'             => 'GET',
                'callback'            => [static::class, 'indexAction'],
                'permission_callback' => null,
                'args'                => [],
            ],

            '/map/save_point/' => [
                'methods'             => 'POST',
                'callback'            => [static::class, 'savePointAction'],
                'permission_callback' => null,
                'args'                => [],
            ]
        ];
    }

    /**
     * @return void
     */
    public static function indexAction()
    {
        wc_load_cart();

        $cart = \WC()->cart;

        $shippingMethod = new Shipping(0);
        $url = $shippingMethod->getMapUrl([
            'contents'       => $cart->get_cart(),
            'cart_subtotal'  => $cart->get_subtotal(),
            'payment_method' => isset($_GET['payment_method']) ? sanitize_text_field($_GET['payment_method']) : '',
            'destination'    => [
                'country' => isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '',
                'state'   => isset($_GET['region']) ? sanitize_text_field($_GET['region']) : '',
                'city'    => isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '',
                'street'  => isset($_GET['street']) ? sanitize_text_field($_GET['street']) : '',
                'zip_code'=> isset($_GET['zip_code']) ? sanitize_text_field($_GET['zip_code']) : '',
            ]]
            // pass pickup point to the map
            , $GLOBALS['viadelivery']->session()->get('via_selected_point'),
        );
        
        header('Location: '. $url);
        exit;
    }

    public static function savePointAction(\WP_REST_Request $request)
    {
        $pointId = $request->get_param('point');
        $session = $GLOBALS['viadelivery']->session();

        if ($session) {
            $session->set('via_selected_point', $pointId);
        }

        return ['ok'];
    }
}