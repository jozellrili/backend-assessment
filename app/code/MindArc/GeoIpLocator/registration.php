<?php
/**
 * Created by PhpStorm.
 * User: jozell.rili
 * Date: 11/27/2019
 * Time: 3:49 PM
 */

use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();

if ($registrar->getPath(ComponentRegistrar::MODULE, 'MindArc_GeoIpLocator') === null) {
    ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'MindArc_GeoIpLocator',
        __DIR__
    );
}