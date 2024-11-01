<?php
namespace Ipol\Woo\ViaDelivery;

class Shipment
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
	protected $locationTo = [];
	
	/**
	 * @var decimal
	 */
	protected $orderItemsPrice;

	/**
	 * @var string
	 */
	protected $orderItemsCurrency;

	/**
	 * @var array
	 */
	protected $dimensions = [
		'WIDTH' => 0,
		'HEIGHT' => 0,
		'LENGTH' => 0,
		'WEIGHT' => 0,
	];

    /**
     * @var array
     */
    protected $default_dimensions = [
        'width'  => 200,
        'height' => 200,
        'length' => 100,
        'weight' => 500,
    ];

	/**
	 * @var string
	 */
	protected $paymentMethod;

    /**
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $weight_unit = get_option('woocommerce_weight_unit');
        $dimension_unit = get_option('woocommerce_dimension_unit');

        if (empty($settings['default_weight'])) {
            $settings['default_weight'] = $this->default_dimensions['weight'];
        }
        else {
            if ($weight_unit == 'kg')
                $settings['default_weight'] = round($settings['default_weight'] * 1000, 0);
            else if ($weight_unit == 'lbs')
                $settings['default_weight'] = round($settings['default_weight'] * 453.592, 0);
            else if ($weight_unit == 'oz')
                $settings['default_weight'] = round($settings['default_weight'] * 28.3495, 0);
        }

        if (empty($settings['default_dimensions_width'])) {
            $settings['default_dimensions_width'] = $this->default_dimensions['width'];
        }
        else {
            if ($dimension_unit == 'cm')
                $settings['default_dimensions_width'] = round($settings['default_dimensions_width'] * 10, 0);
            else if ($dimension_unit == 'm')
                $settings['default_dimensions_width'] = round($settings['default_dimensions_width'] * 1000, 0);
            else if ($dimension_unit == 'in')
                $settings['default_dimensions_width'] = round($settings['default_dimensions_width'] * 25.4, 0);
            else if ($dimension_unit == 'yd')
                $settings['default_dimensions_width'] = round($settings['default_dimensions_width'] * 914.4, 0);
        }

        if (empty($settings['default_dimensions_length'])) {
            $settings['default_dimensions_length'] = $this->default_dimensions['length'];
        }
        else {
            if ($dimension_unit == 'cm')
                $settings['default_dimensions_length'] = round($settings['default_dimensions_length'] * 10, 0);
            else if ($dimension_unit == 'm')
                $settings['default_dimensions_length'] = round($settings['default_dimensions_length'] * 1000, 0);
            else if ($dimension_unit == 'in')
                $settings['default_dimensions_length'] = round($settings['default_dimensions_length'] * 25.4, 0);
            else if ($dimension_unit == 'yd')
                $settings['default_dimensions_length'] = round( $settings['default_dimensions_length'] * 914.4, 0);
        }

        if (empty($settings['default_dimensions_height'])) {
            $settings['default_dimensions_height'] = $this->default_dimensions['height'];
        }
        else {
            if ($dimension_unit == 'cm')
                $settings['default_dimensions_height'] = round($settings['default_dimensions_height'] * 10, 0);
            else if ($dimension_unit == 'm')
                $settings['default_dimensions_height'] = round($settings['default_dimensions_height'] * 1000, 0);
            else if ($dimension_unit == 'in')
                $settings['default_dimensions_height'] = round($settings['default_dimensions_height'] * 25.4, 0);
            else if ($dimension_unit == 'yd')
                $settings['default_dimensions_height'] = round($settings['default_dimensions_height'] * 914.4, 0);
        }

        $this->settings = $settings;
    }

    /**
     * @param mixed $formatted_address
	 * @param mixed $country
	 * @param string $region
	 * @param string $city
     * @param string $zip_code
	 *
	 * @return static
	 */
	public function setReceiver($formatted_address, $country, $region, $city, $street, $zip_code)
	{		
        $this->locationTo = [
            'formatted_address' => $formatted_address,
            'country'           => $country,
            'region'            => $region,
            'city'              => $city,
            'street'            => $street,
            'zip_code'          => $zip_code,
        ];

		return $this;
	}

	/**
	 * @return array
	 */
	public function getReceiver()
	{
		return $this->locationTo;
	}
	
	/**
	 * @param mixed $paymentSystemId
	 */
	public function setPaymentMethod($paymentSystemId)
	{
		$this->paymentMethod = $paymentSystemId;	

		return $this;
	}

	/**
	 * @return array
	 */
	public function getPaymentMethod()
	{
		return $this->paymentMethod;
	}

	/**
	 * @return bool
	 */
	public function isPaymentOnDelivery()
	{}

	/**
	 * @return boolean
	 */
	public function isPossibleDelivery()
	{
		$receiver = $this->getReceiver();

		return (!empty($receiver['formatted_address']) or (!empty($receiver['country']) && !empty($receiver['city'])));
//        return !empty($receiver['country']) && !empty($receiver['city']);
	}

    /**
	 * @param array   $items
	 * @param integer $itemsPrice
	 * @param array   $defaultDimensions
	 */
	public function setItems($items, $itemsPrice = null, $itemsCurrency = 'RUB', $defaultDimensions = [])
	{
		$this->orderItems      = (array) $items;
		$this->orderItemsPrice = $itemsPrice != null 
			? $itemsPrice
			: array_reduce($this->orderItems, function($ret, $item) {
				return $ret + $item['PRICE'] * $item['QUANTITY'];
			  }, 0)
		;
		$this->orderItemsCurrency = $itemsCurrency;
		$this->dimensions         = $this->calcDimensions($this->orderItems, $defaultDimensions);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getItems()
	{
        return $this->orderItems;
    }
    
    /**
	 * @return float
	 */
	public function setPrice($price, $currency = 'RUB')
	{
		$this->orderItemsPrice    = $price;
		$this->orderItemsCurrency = $currency;
    }
    
    /**
	 * @return float
	 */
	public function getPrice()
	{
		return $this->orderItemsPrice;
	}
	
	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->orderItemsCurrency;
	}
    
    /**
	 * @param float $width
	 * @param float $height
	 * @param float $length
	 * @param float $weight
	 */
	public function setDimensions($width, $height, $length, $weight)
	{
		$this->dimensions['WIDTH']  = $width;
		$this->dimensions['HEIGHT'] = $height;
		$this->dimensions['LENGTH'] = $length;
		$this->dimensions['WEIGHT'] = $weight;
	}

    /**
	 * @return array
	 */
	public function getDimensions()
	{
		return $this->dimensions;
    }
    
    /**
	 * @param float $width
	 */
	public function setWidth($width)
	{
		$this->dimensions['WIDTH'] = $width;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getWidth()
	{
		return $this->dimensions['WIDTH'];
    }
    
    /**
	 * @param float $height
	 */
	public function setHeight($height)
	{
		$this->dimensions['HEIGHT'] = $height;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getHeight()
	{
		return $this->dimensions['HEIGHT'];
    }
    
    /**
	 * @param float $length
	 */
	public function setLength($length)
	{
		$this->dimensions['LENGTH'] = $length;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getLength()
	{
		return $this->dimensions['LENGTH'];
    }
    
    /**
	 * @param float $weight
	 */
	public function setWeight($weight)
	{
		$this->dimensions['WEIGHT'] = $weight;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getWeight()
	{
		return $this->dimensions['WEIGHT'];
	}

	/**
	 * @return float
	 */
	public function getVolume()
	{
        $volume = $this->dimensions['WIDTH'] * $this->dimensions['HEIGHT'] * $this->dimensions['LENGTH'];
        
        return round($volume / 1000000, 3);
	}
    
    /**
	 * @param  array $items
	 * @param  array $defaultDimensions
	 * 
	 * @return array
	 */
	protected function calcDimensions(&$items, $defaultDimensions = array())
	{
		$defaultDimensions = $defaultDimensions ?: array(
			'WEIGHT' => $this->settings['default_weight'],
			'WIDTH'  => $this->settings['default_dimensions_width'],
			'HEIGHT' => $this->settings['default_dimensions_height'],
			'LENGTH' => $this->settings['default_dimensions_length'],
		);

		$sumWeight     = 0;
		$sumDimensions = [];

		$items = array_map(function($item) use (&$sumWeight, $defaultDimensions) {
			if (!is_array($item['DIMENSIONS'])) {
				$item['DIMENSIONS'] = unserialize($item['DIMENSIONS']) ?: [
					'WIDTH'  => 0,
					'HEIGHT' => 0,
					'LENGTH' => 0,
				];
			}

			$item['WEIGHT'] = $item['WEIGHT'] ?: $defaultDimensions['WEIGHT'];
			$item['DIMENSIONS']['WIDTH']  = $item['DIMENSIONS']['WIDTH']  ?: $defaultDimensions['WIDTH'];
			$item['DIMENSIONS']['HEIGHT'] = $item['DIMENSIONS']['HEIGHT'] ?: $defaultDimensions['HEIGHT'];
			$item['DIMENSIONS']['LENGTH'] = $item['DIMENSIONS']['LENGTH'] ?: $defaultDimensions['LENGTH'];

			$sumWeight += $item['QUANTITY'] * $item['WEIGHT'];

			return $item;
		}, $items);

		$dimensions = array_merge([
			[
				$defaultDimensions['WIDTH'], 
				$defaultDimensions['HEIGHT'], 
				$defaultDimensions['LENGTH']
			]
		], array_column($items, 'DIMENSIONS'));

		foreach ($dimensions as &$dimension) {
			rsort($dimension);
		}

		$sumDimensions['LENGTH'] = max(array_column($dimensions, 0));
		$sumDimensions['WIDTH']  = max(array_column($dimensions, 1));
		$sumDimensions['HEIGHT'] = max(array_column($dimensions, 2));

		return [
			'WIDTH'  => round($sumDimensions['WIDTH']  / 10, 0),
			'HEIGHT' => round($sumDimensions['HEIGHT'] / 10, 0),
			'LENGTH' => round($sumDimensions['LENGTH'] / 10, 0),
			'WEIGHT' => round($sumWeight, 0),
		];
	}

    /**
     * @return array
     */
    public function getLocationTo()
    {
        return $this->locationTo;
    }

}