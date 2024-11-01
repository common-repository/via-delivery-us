<?php
namespace Ipol\Woo\ViaDelivery;

use Ipol\Woo\ViaDelivery\Helpers\View;

class Shipping extends \WC_Shipping_Method
{
    const METHOD_ID = 'viadelivery';

    /**
     * @var array
     */
    protected $package;

    /**
     * @var array
     */
    protected $default_dimensions = [
        'width'  => array(
            'mm' => 200,
            'cm' => 20,
            'in' => 8,
            'yd' => 0.222222,
            'm'  => 0.2,
        ),
        'height' => array(
            'mm' => 200,
            'cm' => 20,
            'in' => 8,
            'yd' => 0.222222,
            'm'  => 0.2,
        ),
        'length' => array(
            'mm' => 100,
            'cm' => 10,
            'in' => 3,
            'yd' => 0.0833333,
            'm'  => 0.1,
        ),
        'weight' => array(
            'g'  => 500,
            'kg' => 0.5,
            'lbs'  => 1,
            'oz' => 16,
        ),
    ];

    /**
     * @param \WC_Shipping_Rate $rate
     * @return boolean
     */
    public static function isHeirRate(\WC_Shipping_Rate $rate)
    {
        return static::isHeir($rate->id);
    }

    /**
     * @param \WC_Shipping_Method $method
     * @return boolean
     */
    public static function isHeirMethod(\WC_Order_Item_Shipping $method)
    {
        return static::isHeir($method->get_method_id());
    }

    /**
     * @param \WC_Order $order
     * @return boolean
     */
    public static function isHeirOrder(\WC_Order $order)
    {
        foreach ($order->get_shipping_methods() as $shippingMethod) {
            if (static::isHeirMethod($shippingMethod)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $methodId
     * @return boolean
     */
    public static function isHeir($methodId)
    {
        return substr($methodId, 0, strlen(static::METHOD_ID)) == static::METHOD_ID;
//        return strpos($methodId, 'viadelivery') === 0;
    }

    /**
     * @inheritDoc
     *
     * @param integer $instance_id
     */
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id                 = static::METHOD_ID;
        $this->method_title       = __('Via.Delivery', 'viadelivery');
        $this->method_description = __('Via.Delivery description', 'viadelivery'); 
        $this->availability       = 'including';
        $this->supports           = [
            'shipping-zones',
            'settings',
            'instance-settings',
        ];
        
        $this->init();
        
        $this->enabled = 'yes';
        $this->title   = 'Via.Delivery';
    }

    /**
     * @return void
     */
    public function init()
    {
        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function init_form_fields()
    {
        $weight_unit = get_option('woocommerce_weight_unit');
        $dimension_unit = get_option('woocommerce_dimension_unit');

        $this->form_fields = [
            'shop_id' => [
                'type'              => 'text',
                'default'           => '',
                'title'             => __('ShopID', 'viadelivery'),
                'description'       => '',
                'disabled'          => false,

                'class'             => '',
                'css'               => '',
                'placeholder'       => '',
                'desc_tip'          => false,
                'custom_attributes' => array(),
            ],

            'secret_token' => [
                'type'        => 'text',
                'title'       => __('Secret token', 'viadelivery'),
                'description' => '',
            ],
/*
             'language' => [
                 'type'        => 'select',
                 'title'       => __('Language', 'viadelivery'),
                 'description' => '',
                 'options'     => [
                     'en' => 'English',
                     'ru' => 'Russian',
                 ]
             ],
*/
            'dimensions_title' => [
                'type'        => 'title',
                'title'       => __('Dimensions', 'viadelivery'),
                'description' => '',
            ],

            'default_weight' => [
                'type'        => 'decimal',
                'title'       => __('Weight, ' . $weight_unit, 'viadelivery'),
                'description' => '',
                'default'     => $this->default_dimensions['weight'][$weight_unit],
            ],

            'default_dimensions_width' => [
                'type'        => 'decimal',
                'title'       => __('Width, ' . $dimension_unit, 'viadelivery'),
                'description' => '',
                'default'     => $this->default_dimensions['width'][$dimension_unit],
            ],

            'default_dimensions_height' => [
                'type'        => 'decimal',
                'title'       => __('Height, ' . $dimension_unit, 'viadelivery'),
                'description' => '',
                'default'     => $this->default_dimensions['height'][$dimension_unit],
            ],

            'default_dimensions_length' => [
                'type'        => 'decimal',
                'title'       => __('Length, ' . $dimension_unit, 'viadelivery'),
                'description' => '',
                'default'     => $this->default_dimensions['length'][$dimension_unit],
            ],

            'select_pickpoint_button_title' => [
                'type'        => 'title',
                'title'       => __('Appearance', 'viadelivery'),
                'description' => '',
            ],

            'select_pickpoint_button_text' => [
                'type'        => 'text',
                'title'       => __('Pickpoint selection button title into checkout form', 'viadelivery'),
                'default'     => __('Select pickpoint', 'viadelivery'),
                'description' => '',
            ],

            'select_pickpoint_button_css' => [
                'type'        => 'text',
                'title'       => __('CSS-class pickpoint button into checkout form', 'viadelivery'),
                'default'     => '',
                'description' => '',
            ],

            'debug_mode' => [
                'type'        => 'checkbox',
                'title'       => __('Debug mode title', 'viadelivery'),
                'description' => __('Debug mode description', 'viadelivery'),
                'default'     => 'no',
            ],
        ];
    }

    /**
     * @inheritDoc
     *
     * @param string $dim
     *
     * @return mixed
     */
    public function get_default_dimension($dim) {
        switch ($dim) {
            case 'weight':
                $weight_unit = get_option('woocommerce_weight_unit');
                return $this->default_dimensions[$dim][$weight_unit] ?? 0;
            case 'width':
            case 'height':
            case 'length':
                $dimension_unit = get_option('woocommerce_dimension_unit');
                return $this->default_dimensions[$dim][$dimension_unit] ?? 0;
            default:
                return 0;
        }
    }

    /**
     * @inheritDoc
     *
     * @return array
     */
    public function get_instance_form_fields()
    {
        if (empty($this->instance_form_fields)) {
            parent::get_instance_form_fields();

            $this->instance_form_fields = array_merge($this->instance_form_fields, [
                'method_title' => [
                    'type'        => 'text',
                    'title'       => __('Method title into checkout form', 'viadelivery'),
                    'default'     => 'Via.Delivery',
                    'description' => '',
                ],
            ]);
        }

        return $this->instance_form_fields;
    }

    /**
     * @inheritDoc
     *
     * @param array $package
     * 
     * @return boolean
     */
    public function calculate_shipping($package = [])
    {
        $shipment = $this->getShipment($package);

        $dir = explode('/', __DIR__);
        array_pop($dir);
        $filename = implode('/', $dir) . '/assets/calculate.log';

        if (get_option('woocommerce_viadelivery_settings', ['debug_mode' => ''] )['debug_mode'] == 'yes')
            file_put_contents($filename, date('d.m.Y H:i') . PHP_EOL .
                print_r($shipment->getLocationTo(), true) . PHP_EOL .
                '------' . PHP_EOL);
        else if (file_exists($filename))
            unlink($filename);

        if (!$shipment->isPossibleDelivery()) {
            return false;
        }

        $tariff = (new Calculator($this->settings))->preview($shipment);

        if (!key_exists('points', $tariff) or empty($tariff['points'])) {
            return false;
        }

        foreach ($tariff['points'] as $key => $value) {
            if ($key > 0)
                continue;

            $this->add_rate([
                'id' => $this->id, // . '_' . $key,
                'cost' => $value['price'],
                'label' => sprintf(
                    __('%s (%s)', 'viadelivery'),
                    $this->get_option('method_title'),
                    $this->pickup_address($value),
                ),
                'meta_data' => ['point' => $value],
            ]);
        }

        return true;
    }

    /**
     * @param array $package
     * @return boolean
     */
    public function calculate_shipping_concrete($package = [], $pointId)
    {
        $shipment = $this->getShipment($package);

        if (!$shipment->isPossibleDelivery()) {
            return false;
        }

        $tariff = (new Calculator($this->settings))->calculate($shipment, $pointId);

        $this->add_rate([
            'id'    => $this->id,
            'cost'  => $tariff['price'],
            'label' => sprintf(
                __('%s (%s)', 'viadelivery'),
                $this->get_option('method_title'),
                $this->pickup_address($tariff),
            ),
            'meta_data' => ['point' => $tariff],
        ]);

        return true;
    }

    /**
     * @param array $package
     * @return string
     */
    public function getMapUrl(array $package = [], $pointId = null)
    {
        $shipment   = $this->getShipment($package, false);
        $calculator = new Calculator($this->settings);

        return $calculator->getMapUrl($shipment, array_filter([
            'pointId'  => $pointId,
        ]));
    }

    /**
     * @return Ipol\Woo\ViaDelivery\Api\Client
     */
    public function getApiClient()
    {
        return new Api\Client($this->settings);
    }

    /**
     * @return Shipment
     */
    public function getShipment(array $package = [], $geocode = true)
    {
        $country = strval($package['destination']['country']);
        $state = strval($package['destination']['state']);
        $city = strval($package['destination']['city']);
        $street = strval($package['destination']['address_1']) . ' ' . strval($package['destination']['address_2']);
        $postcode = strval($package['destination']['postcode']);

        try {
            $WC_Countries = new \WC_Countries();
        }
        catch (Exception $e) {
            return null;
        }
        $states = (array) $WC_Countries->get_states( $country );

        if (array_key_exists($state, $states)) {
            $state = $states[$state];
        }

        if ($geocode && !empty($country) && !empty($city)) {
            $address = $this->getApiClient()->getService('geocode')->identify(
                $country, $state, $city, $street, $postcode,
                strtolower(get_locale()) == 'ru_ru' ? 'ru' : 'en'
                );
        }
        else {
            $address = $package['destination'];
        }

        $shipment = new Shipment($this->settings);

        $shipment->setReceiver(
            $address['formatted_address'],
            $address['country'],
            $address['region'],
            $address['city'],
            $address['street'],
            $address['zip_code'],
        );

        $shipment->setItems(
            Utils\Package::convertMixed($package['contents']), 
            $package['cart_subtotal'],
            $package['cart_currency'] ?? get_option('woocommerce_currency')
        );

        return $shipment;
    }

    /**
     * @return string
     */
    private function pickup_address($arr) {
        return sprintf(
                __('I will pickup at: %s. ', 'viadelivery'),
                $arr['description']
            ) .
            sprintf(
                __('Address: %s. ', 'viadelivery'),
                $arr['full_address']
            ) .
            sprintf(
                __('Expected delivery time: %s days.', 'viadelivery'),
                $arr['max_days']
            ) .
            (key_exists('distance', $arr) ?
                ' ' . ($arr['distance'] < 322 ?
                    sprintf(
                        __('Distance to this pick-up location: %d feet.', 'viadelivery'),
                        round($arr['distance'] * 3.28084)
                    ) :
                    sprintf(
                        __('Distance to this pick-up location: %1.1f miles.', 'viadelivery'),
                        round($arr['distance'] / 1609.34, 1))
                    ) : ''
            );
    //        (". ID = " . $arr['id']);
    }
}