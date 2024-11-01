<?php
namespace Ipol\Woo\ViaDelivery\Api;

class Client
{
    const API_ENTRY_POINT = 'https://api.viasarci.com/api/';

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @return array
     */
    public static function getProviderClassMap()
    {
        return [
            'map'   => Provider\Map::class,
            'order' => Provider\Order::class,
        ];
    }

    /**
     * @return array
     */
    public static function getServiceClassMap()
    {
        return [
            'point-list' => Service\PointList::class,
            'order'      => Service\Order::class,
            'geocode'    => Service\GeoCode::class,
        ];
    }

    /**
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
	 * Возвращает конкретную провайдер API
	 * 
	 * @param  string $providerName имя службы
	 * 
	 * @return Provider\ProviderInterface
	 */
	public function getProvider($providerName)
	{
        $classmap = static::getProviderClassMap();

		if (isset($classmap[$providerName]) && $classmap[$providerName]) {
			if (isset($this->providers[$providerName])) {
				return $this->providers[$providerName];
			}

			return $this->providers[$providerName] = new $classmap[$providerName]($this->settings);
		}

		throw new \Exception("Provider {$providerName} not found");
    }

    /**
	 * Возвращает конкретную службу API
	 * 
	 * @param  string $serviceName имя службы
	 * 
	 * @return Service\ServiceInterface
	 */
	public function getService($serviceName)
	{
        $classmap = static::getServiceClassMap();

		if (isset($classmap[$serviceName]) && $classmap[$serviceName]) {
			if (isset($this->services[$serviceName])) {
				return $this->services[$serviceName];
			}

			return $this->services[$serviceName] = new $classmap[$serviceName]($this);
		}

		throw new \Exception("Service {$serviceName} not found");
    }
}