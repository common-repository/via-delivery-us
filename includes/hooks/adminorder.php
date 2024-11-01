<?php
namespace Ipol\Woo\ViaDelivery\Hooks;

use Ipol\Woo\ViaDelivery\Shipping;
use Ipol\Woo\ViaDelivery\Order;
use Ipol\Woo\ViaDelivery\Helpers\AdminOrder as Helper;

class AdminOrder
{
    /**
     * @return void
     */
    public static function register()
    {
        add_action('admin_enqueue_scripts', [static::class, 'loadAssets']);
        add_action('add_meta_boxes', [static::class, 'addButton']);
        add_action( 'restrict_manage_posts', array( static::class, 'restrict_manage_posts' ) );
        add_filter('manage_edit-shop_order_columns', [static::class, 'addOrderTableColumns']);
        add_filter('manage_shop_order_posts_custom_column', [static::class, 'valueOrderTableColumns']);
        add_filter('bulk_actions-edit-shop_order', [static::class, 'addOrderTableActions']);
        add_filter('handle_bulk_actions-edit-shop_order', [static::class, 'doOrderTableActions'], 10, 3);
        add_filter('wc_order_statuses', [static::class, 'getOrderStatuses']);
    }

    /**
     * @return void
     */
    public static function loadAssets()
    {
        wp_enqueue_script('via-back', VIADELIVERY_PLUGIN_URI.'assets/js/backend.js?'. mt_rand(), ['jquery']);
    }

    /**
     * @return void
     */
    public static function addButton()
    {
        add_meta_box('via-metabox', __('Via.Delivery','viadelivery'), [static::class, 'getMetaBoxContent'], 'shop_order', 'side', 'core');
    }

    /**
     * @return void
     */
    public static function getMetaBoxContent()
    {
        $post    = $GLOBALS['post'];
        $wcOrder = new \WC_Order($post->ID);

        if (!Shipping::isHeirOrder($wcOrder)) {
            $ret = __('Another delivery method was selected', 'viadelivery');
        } else {
            $order = new Order($wcOrder);
            $point = $order->getPoint();

            $ret = ''
                . '<p>'. Helper::getPointDescription($point) .'</p>'
                . '<p>'. Helper::getButtons($order) .'</p>'
            ;
        }

        print $ret;
    }

    /**
     * @param array $columns
     * @return array
     */
    public static function addOrderTableColumns($columns)
    {
        $columns = $columns ?: [];

        return array_merge($columns, [
            'VIADELIVERY_PHONE_COLUMN'  => __('Phone', 'viadelivery'),
            'VIADELIVERY_SHIPPING_ADDR' => __('Shipping address', 'viadelivery'),
            'VIADELIVERY_STATUS'        => __('Via.Delivery', 'viadelivery'),
        ]);
    }

    /**
     * @param string $column
     * @return void
     */
    public static function valueOrderTableColumns($column)
    {
        $orderId   = $GLOBALS['post']->ID;
        $wcOrder   = new \WC_Order($orderId);
        $order     = new Order($wcOrder);
        $recipient = $order->getRecipient();
        $ret       = '';

        switch ($column) {
            case 'VIADELIVERY_PHONE_COLUMN':
                $ret = $recipient['phone'];
            break;

            case 'VIADELIVERY_SHIPPING_ADDR':
                if (Shipping::isHeirOrder($wcOrder)) {
                    $point = $order->getPoint();
                    $ret   = Helper::getPointDescription($point);
                } else {
                    $ret = $recipient['full_address'];
                }
            break;

            case 'VIADELIVERY_STATUS':
                if (Shipping::isHeirOrder($wcOrder)) {
                    $ret = Helper::getButtons($order, ['create', 'cancel']);
                }
            break;
        }

        print $ret;
    }

    
    /**
     * @param array $actions
     * @return array
     */
    public static function addOrderTableActions($actions)
    {
        $actions = $actions ?: [];

        return array_merge($actions, [
            'VIADELIVERY_CREATE_ORDER' => __('Send to Via.Delivery', 'viadelivery'),
            'VIADELIVERY_CANCEL_ORDER' => __('Cancel delivery', 'viadelivery')
        ]);
    }

    public static function doOrderTableActions($redirect, $doaction, $object_ids)
    {
        $redirect = remove_query_arg(['VIADELIVERY_CREATE_ORDER', 'VIADELIVERY_CANCEL_ORDER'], $redirect);

        switch($doaction)
        {
            case 'VIADELIVERY_CREATE_ORDER':
                foreach ($object_ids as $id) {
                    $wcOrder = new \WC_Order($id);
                    
                    if (Shipping::isHeirOrder($wcOrder)) {
                        $order = new Order($wcOrder);
                        $order->create();
                    }
                }
            break;

            case 'VIADELIVERY_CANCEL_ORDER':
                foreach ($object_ids as $id) {
                    $wcOrder = new \WC_Order($id);
                    
                    if (Shipping::isHeirOrder($wcOrder)) {
                        $order = new Order($wcOrder);
                        $order->cancel();
                    }
                }
            break;
        }

        return $redirect;
    }

    public static function getOrderStatuses($order_statuses)
    {
        return array_merge($order_statuses ?: [], [
            'via-approved'            => __('Approved', 'viadelivery'),
            'via-dispatced'           => __('Dispatced', 'viadelivery'),
            'via-delivered-pickpoint' => __('Delivered to pickpoint', 'viadelivery'),
            'via-picked-up'           => __('Picked Up', 'viadelivery'),
            'via-expiring'            => __('Expiring', 'viadelivery'),
        ]);
    }

    public static function restrict_manage_posts()
    {
        global $typenow;

        if ($typenow !== 'shop_order') {
            return ;
        }

        $shipping = new Shipping();
        $settings = $shipping->settings;

        print '<a href="https://viadelivery.pro/" class="button" target="_blank">Via.Delivery Backoffice</a>';

        if (!empty($settings['secret_token'])) {
            print '&nbsp;';
//            print '<a href="https://pay.viadelivery.pro/order_docs/@'. $settings['secret_token'] .'" class="button" target="_blank">Print Stickers</a>';
            print '<a href="https://docs.viadelivery.pro/s/docs/stickers/@'. $settings['secret_token'] .'" class="button" target="_blank">Print Stickers</a>';
        }
    }
}