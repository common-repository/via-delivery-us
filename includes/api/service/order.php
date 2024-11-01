<?php
namespace Ipol\Woo\ViaDelivery\Api\Service;

use Ipol\Woo\ViaDelivery\Api\Client;
use Ipol\Woo\ViaDelivery\Order as OrderModel;

class Order implements ServiceInterface
{
    /**
     * @var Ipol\Woo\ViaDelivery\Api\Provider\Order
     */
    protected $provider;

    /**
     * @param Client
     */
    public function __construct(Client $client)
    {
        $this->provider = $client->getProvider('order');
    }

    /**
     * @param OrderModel $order
     * @return boolean
     */
    public function create(OrderModel $order)
    {
        $params = $this->prepare($order);
        $ret    = $this->provider->execMethod('update', $params, 'POST', true);

        return $ret['status'] == 'OK';
    }

    /**
     * @param OrderModel $order
     * @return boolean
     */
    public function cancel(OrderModel $order)
    {
        $params = $this->prepare($order, ['fulfillment_status' => 'declined']);
        $ret    = $this->provider->execMethod('update', $params, 'POST', true);

        return $ret['status'] == 'OK';
    }

    // DateTime class is in plugins/woocommerce/includes/class-wc-datetime.php
    protected function prepare(OrderModel $order, array $params = [])
    {
        return array_replace_recursive([
            'id'                 => $order->getId(),
            'number'             => $order->getId(),
            'fulfillment_status' => 'accepted',
            'financial_status'   => $order->isPaid() ? 'paid' : 'pending',
            'paid_at'            => $order->isPaid() ? $this->$order->getPaymentDate()->__toString() : null,
            'items_price'        => round($order->getPriceItems(), 2),
            'delivery_price'     => $order->getPriceDelivery(),
            'total_price'        => round($order->getPriceTotal(), 2),
            'currency_code'      => $order->getCurrency(),
            'client'             => [
                'name'  => $order->getRecipient()['name'],
                'email' => $order->getRecipient()['email'],
                'phone' => $order->getRecipient()['phone'],
            ],
            'order_lines'        => array_map(function($item) {
                return [
                    'vat'              => $item['VAT_RATE'],
                    'title'            => $item['NAME'],
                    'weight'           => round($item['WEIGHT'] / 1000, 2),
                    'dimensions'       => implode('x', array_map(function($d) { return round($d / 10, 0); }, $item['DIMENSIONS'])),
                    'quantity'         => intval($item['QUANTITY']),
                    'full_sale_price'  => floatval($item['PRICE']),
                    'full_total_price' => $item['PRICE'] * $item['QUANTITY'],
                    'barcode'          => '',
                    'product_id'       => $item['ID'],
                ];
            }, $order->getShipment()->getItems()),
            'delivery_info'      => [
                'shipping_company_handle' => 'Via.Delivery',
                'price'                   => $order->getPoint()['price'],
                'outlet'                  => [
                    'external_id' => $order->getPoint()['id'],
                    'description' => $order->getPoint()['description'],
                ]
            ]
        ], $params);
    }
}