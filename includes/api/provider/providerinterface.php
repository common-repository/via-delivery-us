<?php
namespace Ipol\Woo\ViaDelivery\Api\Provider;

interface ProviderInterface
{
    /**
     * @param array
     */
    public function __construct(array $settings);

    /**
     * @param string $method
     * @param array  $args
     * @return void
     */
    public function execMethod($method, array $args = [], $methodType = 'GET', $secure = false);
}