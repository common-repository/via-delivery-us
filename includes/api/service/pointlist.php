<?php
namespace Ipol\Woo\ViaDelivery\Api\Service;

use Ipol\Woo\ViaDelivery\Api\Client;
use Ipol\Woo\ViaDelivery\Api\Provider\Map;
use Ipol\Woo\ViaDelivery\Shipment;
use Ipol\Woo\ViaDelivery\Shipping;

class PointList implements ServiceInterface
{
    /**
     * @var Ipol\Woo\ViaDelivery\Api\Provider\Map
     */
    protected $provider;

    /**
     * @param Client
     */
    public function __construct(Client $client)
    {
        $this->provider = $client->getProvider('map');
    }

    /**
     * @param array $params
     * @return array
     */
    public function getPreview(Shipment $shipment, array $params = [])
    {
        $params = array_merge($this->prepare($shipment), $params);

        $res = $this->provider->execMethod('point-list/preview', $params);

        if (isset($res['points'][0]['id'])) {
            $pointId = $res['points'][0]['id'];
            $session = $GLOBALS['viadelivery']->session();

            if ($session)
                $session->set('via_preview_point', $pointId);

        }

        return $res;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getList(Shipment $shipment, array $params = [])
    {
        $params = array_merge($this->prepare($shipment), $params);

        return $this->provider->execMethod('point-list', $params);
    }

    public function getById($pointId, Shipment $shipment = null)
    {
        $params = ['point_id' => $pointId];

        if ($shipment) {
            $params = array_merge($params, $this->prepare($shipment));
        }

        $res = $this->provider->execMethod('point-list', $params);

        if (isset($res['points'][0]['id'])) {
            $pointId = $res['points'][0]['id'];
            $session = $GLOBALS['viadelivery']->session();

            if ($session)
                $session->set('via_selected_point', $pointId);
        }

        return $res;
    }

    /**
     * @param array $params
     * @return string
     */
    public function getMapUrl(Shipment $shipment, array $params = [])
    {
        $parms = $this->prepare($shipment, true);

        $ret = $this->provider->getMapUrl($parms = array_merge([
            'address'     => $parms['street'] .', '. $parms['city'] .', '. $parms['region'] .' '. $parms['zip_code'] .', '. $parms['country'],
            'orderCost'   => $parms['order_price'],
            'orderWeight' => $parms['weight'],
            'orderWidth'  => $parms['width'],
            'orderHeight' => $parms['height'],
            'orderLength' => $parms['length'],
            'currency'    => $parms['currency'],
            'lang'        => $parms['lang'],
        ], $params));

        return $ret;
    }
    
    /**
     * @param Ipol\Woo\ViaDelivery\Shipment $shipment
     * @param boolean $map // data for map
     * @return array
     */
    protected function prepare(Shipment $shipment, $map = false)
    {
        $res = [
            'weight'         => $shipment->getWeight(),
            'width'          => $shipment->getWidth(),
            'height'         => $shipment->getHeight(),
            'length'         => $shipment->getLength(),
            'order_price'    => $shipment->getPrice(),
            'payment_method' => $shipment->getPaymentMethod(),
            'currency'       => $shipment->getCurrency(),
            'lang'           => strtolower(determine_locale()) == 'ru_ru' ? 'ru' : 'en',
        ];

        if ($map) {
            $res['country'] = $shipment->getReceiver()['country'];
            $res['region'] = $shipment->getReceiver()['region'];
            $res['city'] = $shipment->getReceiver()['city'];
            $res['street'] = $shipment->getReceiver()['street'];
            $res['zip_code'] = $shipment->getReceiver()['zip_code'];
        }
        else {
            $res['address'] = $shipment->getReceiver()['formatted_address'];
            $res['group_limit'] = 1;
        }

        return $res;
    }
}