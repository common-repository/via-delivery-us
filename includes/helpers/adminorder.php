<?php
namespace Ipol\Woo\ViaDelivery\Helpers;

use Ipol\Woo\ViaDelivery\Order;

class AdminOrder
{
    const AJAX_URL = '/wp-json/woo-viadelivery/order/%d?width=800&height=600';
    const AJAX_PAYMENT_URL = '/wp-json/woo-viadelivery/order/%d/paid';
    
    /**
     * @param array $point
     * 
     * @return string
     */
    public static function getPointDescription($point)
    {
        if (empty($point)) {
            return '';
        }

        return ''
            . '<b>'. $point['description'] .'</b><br>'
            . sprintf('%s %s %s %s', $point['region'], $point['city_pref'], $point['city'], $point['street'])
        ;
    }

    /**
     * @param Order $order
     * 
     * @return string
     */
    public static function getButtons(Order $order, array $keys = [])
    {
        $buttons = [
            'payment' => static::getPaymentButton($order),
            'edit'    => static::getEditButton($order),
            'create'  => static::getCreateButton($order),
            'cancel'  => static::getCancelButton($order),
        ];

        $keys = $keys ?: array_keys($buttons);

        return implode('&nbsp;', array_filter(array_intersect_key($buttons, array_flip($keys))));
    }

    /**
     * @param Order $order
     * 
     * @return string
     */
    public static function getEditButton(Order $order)
    {
        if ($order->isCreated()) {
            return '';
        }

        // iframe is forbidden in administration panel
//        return sprintf('<a href="%s" class="'. ($order->isPossibleDelivery() ? 'thickbox' : '') .' viadelivery button">%s</a>',
//            htmlspecialchars($order->isPossibleDelivery() ? static::getAjaxUrl($order) : 'javascript:alert("'. __('Please add shipping address into order', 'viadelivery') .'")'),
//            __('Change Delivery Point', 'viadelivery')
//        );
        return '';
    }

    public function getPaymentButton(Order $order)
    {
        if ($order->isCreated()) {
            return '';
        }

        if ($order->isPaid()) {
            return '';
        }

        if ($order->maybePaid()) {
            return sprintf('<a href="%s" class="thickbox viadelivery button">%s</a>',
                static::getAjaxPaymentUrl($order, ['action']), 
                __('Mark paid', 'viadelivery')
            );
        } 

        $hint = __('Please change order status to pending to enable payment status changing', 'viadelivery');

        return sprintf('<a href="%s" class="viadelivery button gray">%s</a>',
            htmlspecialchars('javascript:alert("'. $hint .'")'), 
            __('Mark paid', 'viadelivery')
        );
    }

    /**
     * @param Order $order
     * 
     * @return string
     */
    public static function getCreateButton(Order $order)
    {
        if ($order->isCreated()) {
            return '';
        }

        if (!$order->isPossibleDelivery()) {
            return '';
        }

        if (!$order->getPoint()) {
            return '';
        }

        return sprintf('<a href="%s" class="viadelivery button via-order-submit" data-action="create" data-confirm="%s?" data-reload="yes">%s</a>',
            static::getAjaxUrl($order), 
            __('Are you sure you want to submit order', 'viadelivery'), 
            __('Send order', 'viadelivery')
        );
    }
    
    /**
     * @param Order $order
     * 
     * @return string
     */
    public static function getCancelButton(Order $order)
    {
        if (!$order->isCreated()) {
            return '';
        }

        return sprintf('<a href="%s" class="viadelivery button via-order-submit" data-action="cancel" data-confirm="%s" data-reload="yes">%s</a>',
            static::getAjaxUrl($order), 
            __('Are you sure you want to cancel order', 'viadelivery'), 
            __('Cancel delivery', 'viadelivery')
        );
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function getAjaxUrl(Order $order)
    {
        return sprintf(static::AJAX_URL, $order->getId());
    }
    
    /**
     * @param Order $order
     * @return string
     */
    protected function getAjaxPaymentUrl(Order $order)
    {
        return sprintf(static::AJAX_PAYMENT_URL, $order->getId());
    }
}