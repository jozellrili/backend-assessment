<?php
/**
 * Created by PhpStorm.
 * User: jozell.rili
 * Date: 11/27/2019
 * Time: 5:49 PM
 */

namespace MindArc\GeoIpLocator\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Model\AbstractModel;

class GeoLocation extends AbstractModel
{

    const GEO_IP_LOCATOR_API = 'http://api.ipstack.com/';
    const GEO_IP_LOCATOR_API_KEY = '041e22bfab2010005994322e22b16a24';

    protected $userIp;

    /** @var Curl */
    protected $curlClient;

    public function __construct(Curl $curl)
    {
        // get the IP Address of the current User
        $this->userIp = $this->getUserIp();

        // setup curl
        $this->curlClient = $curl;
    }

    /**
     * @return string
     */
    public function getUserIp()
    {
        $ipAddress = '0.0.0.0';

        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (!empty($_SERVER['HTTP_X_FORWARDED']))
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (!empty($_SERVER['HTTP_FORWARDED_FOR']))
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (!empty($_SERVER['HTTP_FORWARDED']))
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        else if (!empty($_SERVER['REMOTE_ADDR']))
            $ipAddress = $_SERVER['REMOTE_ADDR'];

        return $ipAddress;
    }

    /**
     * @return mixed
     */
    public function getUserCountryCodeByIp()
    {
        $url = self::GEO_IP_LOCATOR_API . $this->userIp . '/?access_key=' . self::GEO_IP_LOCATOR_API_KEY;

        $this->curlClient->get($url);
        $this->curlClient->setHeaders([
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = $this->curlClient->getBody();

        if (isset($response['location'])) return $response['location']['country_flag_emoji'];
        else return 'Global';
    }
}