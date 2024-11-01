<?php
namespace Ipol\Woo\ViaDelivery\Api\Provider;

class Order extends Base
{
    public static $API_ENTRY_POINT_PROD = 'https://insales.viadelivery.pro/webhook/';
    public static $API_ENTRY_POINT_TEST = false;
}