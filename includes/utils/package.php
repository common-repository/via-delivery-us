<?php
namespace Ipol\Woo\ViaDelivery\Utils;

class Package
{
    const WEIGHT_UNITS = [
        'g'   => 1,
        'oz'  => 28.3495,
        'lbs' => 453.592,
        'kg'  => 1000,
    ];

    const DIMENSIONS_UNITS = [
        'mm' => 1,
        'cm' => 10,
        'in' => 25.4,
        'yd' => 914,
        'm'  => 1000,
    ];

    /**
     * @return string
     */
    public static function getSystemWeightUnit()
    {
        return get_option('woocommerce_weight_unit');
    }

    /**
     * @return string
     */
    public static function getSystemDimensionsUnit()
    {
        return get_option('woocommerce_dimension_unit');
    }

    /**
     * @param float $weight
     *
     *  @return float
     */
    public static function convertWeight($weight)
    {
        return $weight * (static::WEIGHT_UNITS[static::getSystemWeightUnit()] ?: 1);
    }

    /**
     * @param float $dimension
     *
     * @return float
     */
    public static function convertDimension($dimension)
    {
        return $dimension * (static::DIMENSIONS_UNITS[static::getSystemDimensionsUnit()] ?: 1);
    }

    /**
     * @param array $items
     * @return void
     */
    public static function convertMixed(array $items)
    {
        $cartItems = array_filter($items, function($item) { return !($item instanceof \WC_Order_Item_Product); });
        $orderItems = array_filter($items, function($item) { return ($item instanceof \WC_Order_Item_Product); });

        return array_merge(
            static::convertFromCart($cartItems),
            static::convertFromOrder($orderItems)
        );
    }

    /**
     * @param array $items
     * @return array
     */
    public static function convertFromCart(array $items)
    {
        return array_map(function($item) {
            return static::prepare([
                'ID'         => $item['data']->get_id(),
                'NAME'       => $item['data']->get_title(),
                'QUANTITY'   => $item['quantity'],
                'PRICE'      => $item['line_total'] / $item['quantity'],
                'VAT_RATE'   => $item['line_tax'],
                'WEIGHT'     => $item['data']->get_weight() ?: 0,
                'DIMENSIONS' => [
                    'WIDTH'  => $item['data']->get_width()  ?: 0,
                    'HEIGHT' => $item['data']->get_height() ?: 0,
                    'LENGTH' => $item['data']->get_length() ?: 0,
                ]
            ]);
        }, $items);
    }

    /**
     * @param array $items
     * @return array
     */
    public static function convertFromOrder(array $items)
    {
        return array_map(function($item) {
            $product = $item->get_product();

            return static::prepare([
                'ID'         => $product->get_id(),
                'NAME'       => $product->get_title(),
                'QUANTITY'   => $item->get_quantity(),
                'PRICE'      => $product->get_price(),
                'VAT_RATE'   => $item->get_total_tax(),
                'WEIGHT'     => $product->get_weight() ?: 0,
                'DIMENSIONS' => [
                    'WIDTH'  => $product->get_width()  ?: 0,
                    'HEIGHT' => $product->get_height() ?: 0,
                    'LENGTH' => $product->get_length() ?: 0,
                ]
            ], $settings);
        }, $items);
    }

    /**
	 * @param  array $items список товаров
	 * @return array
	 */
	public static function sumDimensions($items)
	{
		$ret = array(
			'WEIGHT' => 0,
			'VOLUME' => 0,
			'LENGTH' => 0,
			'WIDTH'  => 0,
			'HEIGHT' => 0,
		);

		$a = array();
		foreach ($items as $item) {
			$a[] = static::calcItemDimensionWithQuantity(
				$item['DIMENSIONS']['WIDTH'],
				$item['DIMENSIONS']['HEIGHT'],
				$item['DIMENSIONS']['LENGTH'],
				$item['QUANTITY']
			);

			$ret['WEIGHT'] += $item['WEIGHT'] * $item['QUANTITY'];
		}

		$n = count($a);
		if ($n <= 0) { 
			return $ret;
		}

		for ($i3 = 1; $i3 < $n; $i3++) {
			// sort sizes in descending order
			for ($i2 = $i3-1; $i2 < $n; $i2++) {
				for ($i = 0; $i <= 1; $i++) {
					if ($a[$i2]['X'] < $a[$i2]['Y']) {
						$a1 = $a[$i2]['X'];
						$a[$i2]['X'] = $a[$i2]['Y'];
						$a[$i2]['Y'] = $a1;
					};

					if ($i == 0 && $a[$i2]['Y']<$a[$i2]['Z']) {
						$a1 = $a[$i2]['Y'];
						$a[$i2]['Y'] = $a[$i2]['Z'];
						$a[$i2]['Z'] = $a1;
					}
				}

				$a[$i2]['Sum'] = $a[$i2]['X'] + $a[$i2]['Y'] + $a[$i2]['Z']; // sum of sides
			}

			// sort loads in ascending order
			for ($i2 = $i3; $i2 < $n; $i2++) {
				for ($i = $i3; $i < $n; $i++) {
					if ($a[$i-1]['Sum'] > $a[$i]['Sum']) {
						$a2 = $a[$i];
						$a[$i] = $a[$i-1];
						$a[$i-1] = $a2;
					}
				}
			}

			// calculate the sum of the dimensions of the two smallest loads
			if ($a[$i3-1]['X'] > $a[$i3]['X']) {
				$a[$i3]['X'] = $a[$i3-1]['X'];
			}

			if ($a[$i3-1]['Y'] > $a[$i3]['Y']) { 
				$a[$i3]['Y'] = $a[$i3-1]['Y'];
			}

			$a[$i3]['Z'] = $a[$i3]['Z'] + $a[$i3-1]['Z'];
			$a[$i3]['Sum'] = $a[$i3]['X'] + $a[$i3]['Y'] + $a[$i3]['Z']; // sum of sides
		}

		return array_merge($ret, array(
			'LENGTH' => $length = Round($a[$n-1]['X'], 2),
			'WIDTH'  => $width  = Round($a[$n-1]['Y'], 2),
			'HEIGHT' => $height = Round($a[$n-1]['Z'], 2),
			'VOLUME' => $width * $height * $length,
		));
    }
    
    /**
	 * @param  $width
	 * @param  $height
	 * @param  $length
	 * @param  $quantity
	 * 
	 * @return array
	 */
	public static function calcItemDimensionWithQuantity($width, $height, $length, $quantity)
	{
		$ar = array($width, $height, $length);
		$qty = $quantity;
		sort($ar);

		if ($qty <= 1) {
			return array(
				'X' => $ar[0],
				'Y' => $ar[1],
				'Z' => $ar[2],
			);
		}

		$x1 = 0;
		$y1 = 0;
		$z1 = 0;
		$l  = 0;

		$max1 = floor(Sqrt($qty));
		for ($y = 1; $y <= $max1; $y++) {
			$i = ceil($qty / $y);
			$max2 = floor(Sqrt($i));
			for ($z = 1; $z <= $max2; $z++) {
				$x = ceil($i / $z);
				$l2 = $x*$ar[0] + $y*$ar[1] + $z*$ar[2];
				if ($l == 0 || $l2 < $l) {
					$l = $l2;
					$x1 = $x;
					$y1 = $y;
					$z1 = $z;
				}
			}
		}
		
		return array(
			'X' => $x1 * $ar[0],
			'Y' => $y1 * $ar[1],
			'Z' => $z1 * $ar[2]
		);
	}

    /**
     * @param array $item
     * 
     * @return array
     */
    protected static function prepare(array $item)
    {
        return array_replace_recursive($item, [
            'WEIGHT'     => static::convertWeight($item['WEIGHT']),
            'DIMENSIONS' => [
                'WIDTH'  => static::convertDimension($item['DIMENSIONS']['WIDTH']),
                'HEIGHT' => static::convertDimension($item['DIMENSIONS']['HEIGHT']),
                'LENGTH' => static::convertDimension($item['DIMENSIONS']['LENGTH']),
            ]
        ]);

        return $item;
    }
}