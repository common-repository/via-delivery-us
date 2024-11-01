<?php
namespace Ipol\Woo\ViaDelivery;

class Order
{
    protected $order;
    protected $orderShipping;
    protected $shipping;
    protected $point;

    /**
     * @param \WC_Order $order
     */
    public function __construct(\WC_Order $order)
    {   
        $this->order = $order;
        $this->init();
    }

    /**
     * @return boolean
     */
    public function isCreated()
    {
        
        return get_post_meta($this->getId(), 'via_order_sended', 'N') == 'Y';
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->order->get_id();
    }

    /**
     * @return array
     */
    public function getPoint()
    {
        return $this->orderShipping['point'] ?: [];
    }

    /**
     * @param string $pointId
     * @param boolean $recalc
     * @return static
     */
    public function setPoint($pointId, $recalc = false)
    {
        $package  = $this->getPackage();
        $shipping = $this->getShippingHandler();

        $shipping->calculate_shipping_concrete($package, $pointId);

        if (!empty($shipping->rates['viadelivery'])) {
            $rate  = $shipping->rates['viadelivery'];

            if ($recalc) {
                $this->orderShipping->set_shipping_rate($rate);

                foreach ($rate->get_meta_data() as $key => $value) {
                    $this->orderShipping->update_meta_data($key, $value);
                }
            } else {
                $data = $rate->get_meta_data();

                $this->orderShipping['point'] = $data['point'];
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMapUrl()
    {
        $point   = $this->getPoint();
        $package = $this->getPackage();

        return $this->getShippingHandler()->getMapUrl($package, $point['id']);
    }

    /**
     * @return boolean
     */
    public function isPaid()
    {
        return $this->order->is_paid() && $this->getPaymentDate();
    }

    /**
     * @return WC_DateTime|NULL
     */
    public function getPaymentDate()
    {
        return $this->order->get_date_paid('edit');
    }

    /**
     * @return boolean
     */
    public function maybePaid()
    {
        return $this->order->has_status(apply_filters('woocommerce_valid_order_statuses_for_payment_complete', ['on-hold', 'pending', 'failed', 'cancelled'], $this->order));
    }

    public function isPossibleDelivery()
    {
        return $this->getShipment()->isPossibleDelivery();
    }

    /**
     * @return decimal
     */
    public function getPriceItems()
    {
        return $this->getPriceTotal() - $this->getPriceDelivery();
    }

    /**
     * @return decimal
     */
    public function getPriceDelivery()
    {
        return floatval($this->orderShipping->get_total());
    }

    /**
     * @return decimal
     */
    public function getPriceTotal()
    {
        return floatval($this->order->get_total());
    }

    /**
     * @return string   
     */
    public function getCurrency()
    {
        return $this->order->get_currency();
    }

    /**
     * @return array
     */
    public function getRecipient()
    {
        return [
            'country'      => $country = $this->order->get_shipping_country() ?: $this->order->get_billing_country(),
            'state'        => $state = $this->order->get_shipping_state() ?: $this->order->get_billing_state(),
            'city'         => $city = $this->order->get_shipping_city() ?: $this->order->get_billing_city(),
            'address'      => $addr = $this->order->get_shipping_address_1() ?: $this->order->get_billing_address_1(),
            'full_address' => implode(', ', [$country, $state, $city, $addr]),
            'name'         => $this->order->get_formatted_shipping_full_name() ?: $this->order->get_formatted_billing_full_name(),
            'email'        => $this->order->get_billing_email(),
            'phone'        => $this->order->get_billing_phone(),
        ];
    }

    /**
     * @return Ipol\Woo\ViaDelivery\Shipment
     */
    public function getShipment()
    {
        $package = $this->getPackage();

        return $this->getShippingHandler()->getShipment($package);
    }

    /**
     * @param string $status
     * 
     * @return static
     */
    public function setStatus($new_status, $message = '')
    {
        $this->order->update_status($new_status, $message, true);

        do_action('woocommerce_order_edit_status', $this->getId(), $new_status);
                    
        return $this;
    }

    /**
     * @return void
     */
    public function markPaid()
    {
        return $this->order->payment_complete();
    }

    public function save()
    {
        $this->orderShipping->save();
        $this->order->calculate_totals(true);
    }

    /**
     * @return boolean
     */
    public function create()
    {
        if ($this->isCreated()) {
            return true;
        }

        $api = $this->getShippingHandler()->getApiClient();
        $ret = $api->getService('order')->create($this);

        if ($ret) {
            update_post_meta($this->getId(), 'via_order_sended', 'Y');

            return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function cancel()
    {
        if (!$this->isCreated()) {
            return true;
        }

        $api = $this->getShippingHandler()->getApiClient();
        $ret = $api->getService('order')->cancel($this);

        if ($ret) {
            update_post_meta($this->getId(), 'via_order_sended', 'N');

            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    protected function init()
    {
        foreach ($this->order->get_shipping_methods() as $shippingMethod) {
            if (!Shipping::isHeirMethod($shippingMethod)) {
                continue;
            }

            $this->orderShipping = $shippingMethod;

            break;
        }
    }

    /**
     * @return Ipol\Woo\ViaDelivery\Shipping
     */
    protected function getShippingHandler()
    {
        return $this->shipping = $this->shipping ?: new Shipping($this->orderShipping->get_instance_id());
    }

    /**
     * @return array
     */
    protected function getPackage()
    {
        return [
            'destination' => [
                'country' => $this->order->get_shipping_country() ?: $this->order->get_billing_company(),
                'state'   => $this->order->get_shipping_state()   ?: $this->order->get_billing_state(),
                'city'    => $this->order->get_shipping_city()   ?: $this->order->get_billing_city(),
            ],
            'contents'      => $this->order->get_items(),
            'cart_subtotal' => null,
            'cart_currency' => $this->order->get_currency(),
        ];
    }
}