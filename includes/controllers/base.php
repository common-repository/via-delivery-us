<?php
namespace Ipol\Woo\ViaDelivery\Controllers;

use Ipol\Woo\ViaDelivery\Helpers\View;

abstract class Base
{
    public static function getActions()
    {
        return [];
    }

    /**
     * @param string $view
     * @param array $args
     * @return void
     */
    protected function renderHTML($view, array $args = [], $exit = false)
    {
        header('Content-Type: text/html');

        print View::isExists($view) ? View::load($view, $args) : $view;

        if ($exit) {
            exit;
        }
    }
}