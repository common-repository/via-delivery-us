<?php
namespace Ipol\Woo\ViaDelivery\Api\Provider;

use Ipol\Woo\ViaDelivery\Api\Client;

abstract class Base implements ProviderInterface
{
    public static $API_ENTRY_POINT_PROD;
    public static $API_ENTRY_POINT_TEST;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * @return boolean
     */
    public function isTestMode()
    {
        return isset($this->settings['test_mode']) && $this->settings['test_mode'] == 'Y';
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        $url = static::$API_ENTRY_POINT_PROD;

        if ($this->isTestMode()) {
            $url = static::$API_ENTRY_POINT_TEST !== false ? static::$API_ENTRY_POINT_TEST : $url;
        }

        if (empty($url)) {
            throw new \Exception('Not set api entry point'. ($this->isTestMode() ? ' (test mode)' : ''));
        }

        return $url;
    }

    /**
     * @param string $method
     * @param array  $args
     * @return void
     */
    public function execMethod($method, array $args = [], $methodType = 'GET', $secure = false)
    {
        $url = $this->getMethodUrl($method, $secure);

        if ($methodType == 'GET') {
            $url = $url .'&'. http_build_query($args);
            $ret = @file_get_contents($url, false);
        } else {
            $context = stream_context_create($r = [
                'http' => [
                    'method'  => $methodType,
                    'header'  => 'Content-Type: application/json' . PHP_EOL,
                    'content' => json_encode($args)
                ],
            ]);

            $ret = @file_get_contents($url, false, $context);
        }

        $data = json_decode($ret, true);

        $dir = explode('/', __DIR__);
        array_pop($dir);
        array_pop($dir);
        array_pop($dir);
        $filename = implode('/', $dir) . '/assets/api.log';

        if (get_option('woocommerce_viadelivery_settings', ['debug_mode' => ''] )['debug_mode'] == 'yes') {
            if (file_exists($filename) and filesize($filename) > 1000)
                file_put_contents($filename, file_get_contents($filename, FALSE, NULL, 1000));

            file_put_contents($filename, date('d.m.Y H:i') . PHP_EOL .
                $url . PHP_EOL .
                '------' . PHP_EOL, FILE_APPEND);
        }
        else if (file_exists($filename))
            unlink($filename);

        return $data;
    }

    /**
     * @param string $method
     * @param bool   $secure
     * @return void
     */
    protected function getMethodUrl($method, $secure = false)
    {
        return ''
            . rtrim($this->getApiUrl(), '/') . '/'
            . $method 
            . '?id='. $this->settings['shop_id']
            . ($secure ? '&sid='. $this->settings['secret_token'] : '')
        ;
    }
}