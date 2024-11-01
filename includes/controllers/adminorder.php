<?php
namespace Ipol\Woo\ViaDelivery\Controllers;

use Ipol\Woo\ViaDelivery\Shipping;
use Ipol\Woo\ViaDelivery\Order;

class AdminOrder extends Base
{
    /**
     * @return array
     */
    public static function getActions()
    {
        return [
            '/order/(?P<orderId>[0-9]+)' => [
                'methods'             => ['GET', 'POST'],
                'callback'            => [static::class, 'indexAction'],
                'permission_callback' => null,
                'args'                => [
                    'orderId' => [
                        'default'  => null,
                        'required' => true,
                    ]
                ],
            ],

            '/order/(?P<orderId>[0-9]+)/status' => [
                'methods'             => ['POST', 'GET'],
                'callback'            => [static::class, 'setStatusAction'],
                'permission_callback' => null,
                'args'                => [
                    'orderId' => [
                        'default'  => null,
                        'required' => true,
                    ]
                ],
            ],
            
            '/order/(?P<orderId>[0-9]+)/paid' => [
                'methods'             => ['POST', 'GET'],
                'callback'            => [static::class, 'setPaidAction'],
                'permission_callback' => null,
                'args'                => [
                    'orderId' => [
                        'default'  => null,
                        'required' => true,
                    ]
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    public static function indexAction(\WP_REST_Request $request)
    {
        $orderId       = $request->get_param('orderId');
        $order         = static::getOrder($orderId);
        $errors        = [];
        $notifications = [];

        if (!$order) {
            return ['error' => 'Order Not Found'];
        }

        if ($request->get_method() == 'POST') {
            list($errors, $notifications) = static::processRequest($order, $request);
        }

        return static::renderHTML('backend/order/form', [
            'order'         => $order,
            'point'         => $order->getPoint(),
            'errors'        => $errors,
            'notifications' => $notifications,
            'mapUrl'        => $order->getMapUrl(),
        ]);
    }

    /**
     * @return void
     */
    public static function setStatusAction(\WP_REST_Request $request)
    {
        $orderId       = $request->get_param('orderId');
        $order         = static::getOrder($orderId);
        $errors        = [];
        $notifications = [];

        if (!$order) {
            return ['error' => 'Order Not Found'];
        }

        $status = $request['status'];
        $statuses = wc_get_order_statuses();

        if (empty($status)) {
            return ['error' => 'Status is required'];
        }


        if (!array_key_exists($status, $statuses)) {
            return ['error' => 'Unknown status'];
        }

        $order->setStatus($status, __('Order status changed from Via.Delivery hook', 'viadelivery'));

        return ['ok'];
    }

    public static function setPaidAction(\WP_REST_Request $request)
    {
        $orderId       = $request->get_param('orderId');
        $order         = static::getOrder($orderId);
        $errors        = [];
        $notifications = [];

        if (!$order) {
            return ['error' => 'Order Not Found'];
        }

        $ret = $order->markPaid();

        if ($ret) {
            print __('Payment flag set successfully', 'viadelivery');
        } else {
            print __('Payment flag set failure', 'viadelivery');
        }

        exit;
    }

    /**
     * @param int $orderId
     * @return Ipol\Woo\ViaDelivery\Order
     */
    protected static function getOrder($orderId)
    {
        $wcOrder = new \WC_Order($orderId);

        if (!Shipping::isHeirOrder($wcOrder)) {
            return false;
        }

        return new Order($wcOrder);
    }

    protected static function processRequest(Order $order, $data)
    {
        $errors = $notifications = [];

        switch($data['action'])
        {
            case 'save':
                $order->setPoint($data['order']['point'], true);
                $order->save();
            break;

            case 'send':
            case 'create':
                $order->create();
            break;

            case 'cancel':
                $order->cancel();
            break;
        }

        return [$errors, $notifications];
    }
}