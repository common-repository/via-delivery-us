<?php
namespace Ipol\Woo\ViaDelivery;

/**
 * Autoloader class.
 */
class Autoloader {

	/**
	 * Path to the includes directory.
	 *
	 * @var string
	 */
	private $include_path = '';

	/**
	 * The Constructor.
	 */
	public function __construct() 
	{
		if (function_exists('__autoload')) 
		{
			spl_autoload_register('__autoload');
		}

		spl_autoload_register( [$this, 'autoload']);

		$this->include_path = untrailingslashit( VIADELIVERY_PLUGIN_PATH  ) . '/includes/';
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class Class name.
	 * @return string
	 */
	private function get_file_path_from_class($class) 
	{
        $class = substr($class, 20);

		return str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path File path.
	 * @return bool Successful or not.
	 */
	private function load_file($path) 
	{
		if ( $path && is_readable($this->include_path . $path)) {
			include_once $this->include_path . $path;

			return true;
		}

		return false;
	}

	/**
	 * Auto-load WC classes on demand to reduce memory consumption.
	 *
	 * @param string $class Class name.
	 */
	public function autoload($class) {
		$class = strtolower($class);

		if (0 !== strpos($class, 'ipol\\woo\\viadelivery')) {
			return;
		}

		$path = $this->get_file_path_from_class($class);

		if (empty($path) || ! $this->load_file($path)) {
			$this->load_file($this->path);
		}
	}
}

new Autoloader();
