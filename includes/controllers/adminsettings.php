<?php
namespace Ipol\Woo\ViaDelivery\Controllers;

use Ipol\Woo\ViaDelivery;
use Ipol\Woo\ViaDelivery\Shipping;
use Ipol\Woo\ViaDelivery\Helpers\View;

class AdminSettings extends Base
{
    /**
     * @return array
     */
    public static function getActions()
    {
        return [
            '/check-signup-code/' => [
                'methods'             => 'GET',
                'callback'            => [static::class, 'checkSignupAction'],
                'permission_callback' => null,
                'args'                => [],
            ],

            '/activation/' => [
                'methods'             => ['GET', 'POST'],
                'callback'            => [static::class, 'activationSignupAction'],
                'permission_callback' => null,
                'args'                => [],
                'accept_json'         => true,
                'parsed_body'         => true,
            ]
        ];
    }

    public static function indexAction()
    {
        $shipping = new Shipping(0);
        $settings = $shipping->settings;

        if (!empty($settings['shop_id']) && !empty($settings['secret_token'])) {
            return static::renderHTML('backend/dashboard/index', [
                'settings' => $settings,
                'locale'   => strtolower(get_locale()) == 'ru_ru' ? 'ru' : 'en',
            ]);
        }

        return static::authorizeAction();
    }

    // functions are in plugins/woocommerce/includes/abstracts/abstract-wc-settings-api.php
    public static function authorizeAction()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $shipping = new Shipping(0);
            $shipping->process_admin_options();

            $shipping->update_option('default_weight', $shipping->get_default_dimension('weight'));
            $shipping->update_option('default_dimensions_width', $shipping->get_default_dimension('width'));
            $shipping->update_option('default_dimensions_height', $shipping->get_default_dimension('height'));
            $shipping->update_option('default_dimensions_length', $shipping->get_default_dimension('length'));

            wp_redirect('/wp-admin/admin.php?page=viadelivery');
            exit;
        }

        $host   = ViaDelivery::getHost();
        $appId  = ViaDelivery::getApplicationId();
        $locale = strtolower(get_locale()) == 'ru_ru' ? 'ru' : 'en';

        $signUpUrl = 'https://module.viadelivery.pro/?platform=wordpress&client_id='. $appId .'&uri='. $host .'&lang='. $locale;

        return static::renderHTML('backend/dashboard/auth', [
            'locale'    => $locale,
            'signUpUrl' => $signUpUrl,
        ]);
    }

    public static function checkSignupAction(\WP_REST_Request $request)
    {
        $code = $request['code'];

        if (ViaDelivery::checkApplicationSign($code)) {
            return ['ok'];
        }

        return new \WP_Error('Invalid code', 'Invalid code', ['status' => \WP_Http::BAD_REQUEST]);
    }

    /**
     * @param \WP_REST_Request $request
     * @return void
     */
    public static function activationSignupAction(\WP_REST_Request $request)
    {
        $sign = $request['code'];

        if (!ViaDelivery::checkApplicationSign($sign)) {
            return new \WP_Error('Invalid code', 'Invalid code', ['status' => \WP_Http::UNAUTHORIZED]);
        }

        $data = $request->get_json_params();

        if (empty($data['id']) || empty($data['token'])) {
            return new \WP_Error('Invalid data', 'Invalid data', ['status' => \WP_Http::BAD_REQUEST]);
        }

        $shipping = new Shipping(0);
        $shipping->update_option('shop_id', $data['id']);
        $shipping->update_option('secret_token', $data['token']);
//        $shipping->update_option('default_weight', 10);
//        $shipping->update_option('default_dimensions_width', 20);
//        $shipping->update_option('default_dimensions_height', 10);
//        $shipping->update_option('default_dimensions_length', 20);

        return ['ok'];
    }
}