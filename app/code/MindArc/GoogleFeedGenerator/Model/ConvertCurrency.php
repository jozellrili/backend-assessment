<?php

namespace MindArc\GoogleFeedGenerator\Model;

use Magento\Framework\App\Cache;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

class ConvertCurrency extends AbstractModel
{
    /** fixer.io API details */
    const FIXER_IO_API = 'http://data.fixer.io/api/latest';
    const FIXER_IO_API_KEY = '[insert_API_key_here]';

    /** Cache Configuration Options */
    const CACHE_TTL = 3600; // 1 day
    const CACHE_KEY = 'currency_exchange_rate';


    const DEFAULT_CURRENCY = 'USD';
    protected $baseCurrency = NULL;


    /** Protected Members */
    protected $cache;
    protected $storeManager;

    /**
     * ConvertCurrency constructor.
     * @param Cache $cache
     * @param StoreManagerInterface $storeManagerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(Cache $cache, StoreManagerInterface $storeManagerInterface)
    {
        $this->cache = $cache;
        $this->storeManager = $storeManagerInterface;

        $this->baseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * Retrieve the rates from cache
     * @return array|mixed|string
     */
    public function getRateFromCache()
    {
        $rates = $this->cache->load(self::CACHE_KEY);

        if (!$rates && is_null(unserialize($rates))) {

            $rates = $this->getLatestExchangeRate();

            // if rate has count save it to cache for future use within the TTL
            if (count($rates)) $this->setRateToCache($rates);
        }

        return $rates;

    }

    /**
     * @param $object
     */
    public function setRateToCache($object)
    {
        $this->cache->save(serialize($object), self::CACHE_KEY, ['collection'], self::CACHE_TTL);
    }


    /**
     * @return array|string
     */
    public function getLatestExchangeRate()
    {
        // compose the get url with its query strings
        $url = self::FIXER_IO_API . '? ' . http_build_query(['access_key' => self::FIXER_IO_API_KEY, 'format' => 1]);
        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
        ];

        // init curl request
        $curl = new Curl();
        $curl->get($url);
        $curl->setHeaders($curlOpt);

        // get curl request response body
        $response = $curl->getBody();

        // check if response return is success then assign the response to $rate or [];
        $rates = isset($response['success']) ? $response : [];

        return $rates;
    }

    /**
     * @param $amount
     * @param null $from
     * @param null $to
     * @return bool|float
     */
    public function convert($amount, $from = null, $to = null)
    {
        // Assign values for $from and $to if no parameter is passed.
        if (is_null($from)) $from = $this->baseCurrency;
        if (is_null($to)) $to = self::DEFAULT_CURRENCY;

        $exchangeRate = $this->getRateFromCache();

        if (!$exchangeRate) return false;

        $convertedAmount = $amount / ($exchangeRate['rates'][$from] * $exchangeRate['rates'][$to]);

        return round($convertedAmount, 2);
    }
}