<?php
namespace Ipol\Woo\ViaDelivery\Api\Provider;

class Map extends Base
{
    public static $API_ENTRY_POINT_PROD = 'https://map-api.viadelivery.pro/';
    public static $API_ENTRY_POINT_TEST = false;

    public function getMapUrl(array $params)
    {
        return 'https://widget.viadelivery.pro/via.maps/?'. http_build_query(array_merge([
            'dealerId' => $this->settings['shop_id'],
            'lang'     => 'en',
            'action'   => 'true',
        ], $params));
    }
}