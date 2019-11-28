<?php

namespace MindArc\GoogleFeedGenerator\Model;

use Magento\Framework\App\Cache;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

class CurrencyConverter extends AbstractModel
{
    const FIXER_IO_API = 'http://data.fixer.io/api/latest';
    const API_KEY = 'ab3834145c4b8f853a366cd31eed5ca0';

    /** Cache Settings */
    const CACHE_TTL = 3600;
    const CACHE_KEY = 'google_feed_exchange_rate';

    /** @var Cache $cache */
    protected $cache;

    /** @var StoreManagerInterface $storeManager */
    protected $storeManager;

    /**
     * CurrencyConverter constructor.
     * @param Cache $cache
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Cache $cache, StoreManagerInterface $storeManager)
    {
        $this->cache = $cache;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve the current exchange rates from either the cache or from Fixer.io
     * @return mixed|string|null
     */
    public function getExchangeRate()
    {
        // Get the exchange rates from cache
        $rates = $this->cache->load(self::CACHE_KEY);

        // If the cache does not contain the exchange rate, send a cUrl request for the latest exhange rates
        if (!$rates || is_null(unserialize($rates))) {
            try {
                $url = self::FIXER_IO_API . '?' . http_build_query(['access_key' => self::API_KEY, 'format' => 1]);

                $curl = new Curl();
                $curl->get($url);
                $curl->setHeaders([
                    CURLOPT_RETURNTRANSFER => true,
                ]);
                $response = $curl->getBody();
                $rates = isset($response['success']) ? $response : null;
            } catch (\Exception $e) {
                $rates = null;
            }

            $this->cache->save(serialize($rates), self::CACHE_KEY, ['collection'], self::CACHE_TTL);
        } else {
            $rates = unserialize($rates);
        }

        return $rates;
    }

    /**
     * Converts the given amount in $from currency code to the $to currency code
     * @param $amount
     * @param null $from
     * @param null $to
     * @return bool|float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convertAmount($amount, $from = null, $to = null)
    {
        // Set the default currency codes, if not specified
        if ($from === null) $from = $this->storeManager->getStore()->getBaseCurrencyCode();
        if ($to === null) $to = 'USD';

        // Get the current exchange rates. If the API failed for some reason, stop conversion.
        $currencies = $this->getExchangeRate();

        if ($currencies === null) return false;

        // Convert the amount
        return round($amount / $currencies['rates'][$from] * $currencies['rates'][$to], 2);
    }

}