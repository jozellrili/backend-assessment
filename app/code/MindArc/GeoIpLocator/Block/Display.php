<?php
/**
 * Created by PhpStorm.
 * User: jozell.rili
 * Date: 11/27/2019
 * Time: 4:23 PM
 */

namespace MindArc\GeoIpLocator\Block;

use Magento\Framework\View\Element\Template;
use MindArc\GeoIpLocator\Model\GeoLocation;

class Display extends Template
{
    protected $geoIpLocator;

    /**
     * Display constructor.
     * @param Template\Context $context
     * @param GeoLocation $geoLocation
     */
    public function __construct(Template\Context $context, GeoLocation $geoLocation)
    {
        $this->geoIpLocator = $geoLocation;
        parent::__construct($context);
    }

    /**
     * @return $this|Template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $countryCode = $this->geoIpLocator->getUserCountryCodeByIp();

        switch (strtolower($countryCode)) {
            case 'us':
                $this->setTemplate('MindArc_GeoIpLocator::us-block.phtml');
                break;
            default  :
                $this->setTemplate('MindArc_GeoIpLocator::global-block.phtml');
                break;
        }

        $this->assign('countryCode', $countryCode);
        return $this;
    }
}
