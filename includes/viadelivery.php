<?php
namespace Ipol\Woo;

class ViaDelivery
{
    const APPLICATION_TOKEN = 'IaYOBwctgwCLBazXD8r';

    /**
     * @var \Iol\Woo\ViaDelivery\Router
     */
    protected $router;

    /**
     * @return void
     */
    public function __construct()
    {
        session_start();

        $this->initIncludes();
        $this->initHooks();
        $this->initFilters();
        $this->initActions();
    }

    /**
     * @return void
     */
    public function initIncludes()
    {
        /**
		 * Class autoloader.
		 */
		include_once VIADELIVERY_PLUGIN_PATH . '/includes/autoloader.php';
    }

    /**
     * @return void
     */
    public function initHooks()
    {
        ViaDelivery\Hooks\Kernel::register();
        ViaDelivery\Hooks\Shipping::register();
        ViaDelivery\Hooks\FrontOrder::register();
        ViaDelivery\Hooks\AdminOrder::register();
        ViaDelivery\Hooks\AdminDash::register();
    }
    
    /**
     * @return void
     */
    public function initFilters()
    {
    }

    /**
     * @return void
     */
    public function initActions()
    {
        $this->router = ViaDelivery\Router::getInstance();
    }

    /**
     * @return string
     */
    public static function getHost()
    {
        $isHttps = !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']);

        return ($isHttps ? 'https' : 'http') .'://'. $_SERVER['HTTP_HOST'];
    }

    /**
     * @return string
     */
    public static function getApplicationId()
    {
        $ret = get_option('viadelivery_application_id');

        if (empty($ret)) {
            $ret = md5(static::getHost() . mt_srand() . time());
            update_option('viadelivery_application_id', $ret);
        }

        return $ret;
    }

    /**
     * @return string
     */
    public static function getApplicationToken()
    {
        return static::APPLICATION_TOKEN;
    }

    /**
     * @return string
     */
    public static function getApplicationSign()
    {
        $appId = static::getApplicationId();
        $token = static::getApplicationToken();
        
        return md5($appId . $token);

    }

    /**
     * @param string $hash
     * 
     * @return boolean
     */
    public static function checkApplicationSign($hash)
    {
        return static::getApplicationSign() == $hash;
    }

    public function session()
    {
        if (WC()->session === NULL) {
            $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );

            // Prefix session class with global namespace
            if (strpos( $session_class, '\\' ) === false) {
                $session_class = '\\' . $session_class;
            }

            WC()->session = new $session_class();
            WC()->session->init();
        }

        return WC()->session;
    }
}