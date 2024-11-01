<?php
namespace Ipol\Woo\ViaDelivery\Api\Service;

use Ipol\Woo\ViaDelivery\Api\Client;

interface ServiceInterface
{
    /**
     * @param Client
     */
    public function __construct(Client $client);
}