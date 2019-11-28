<?php
/**
 * Created by PhpStorm.
 * User: jozell.rili
 * Date: 11/27/2019
 * Time: 6:25 PM
 */

namespace MindArc\GeoIpLocator\Observer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Setup\Exception;
use MindArc\GeoIpLocator\Model\GeoLocation;

class CountryRestriction implements ObserverInterface
{
    const RESTRICTED_COUNTRIES = ['ru', 'cn'];

    protected $geoLocationModel;
    protected $userCountry;

    public function __construct(Context $context, GeoLocation $geoLocation)
    {
        $this->geoLocationModel = $geoLocation;
        $this->userCountry = $geoLocation->getUserCountryCodeByIp();

    }

    public function execute(Observer $observer)
    {
        // $this->userCountry = 'cn'; uncomment this line for testing

        // check if the user's country is restricted to access the site
        if (in_array(strtolower($this->userCountry), self::RESTRICTED_COUNTRIES)) {
            throw new Exception(__('Country not allowed!'));
        }
    }
}