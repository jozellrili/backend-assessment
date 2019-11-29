<?php
/**
 * Created by PhpStorm.
 * User: jozell.rili
 * Date: 11/28/2019
 * Time: 4:36 PM
 */

namespace MindArc\GoogleFeedGenerator\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class FeedGenerator extends AbstractModel
{
    const OUTPUT_FILENAME = 'generated_feed.xml';

    /** @var CollectionFactory $collectionFactory */
    protected $collectionFactory;

    /** @var UrlInterface $urlInterface */
    protected $urlInterface;

    /** @var Image $imageHelper */
    protected $imageHelper;

    /** @var DirectoryList $directory */
    protected $directory;

    /** @var LoggerInterface $logger */
    protected $logger;

    /** @var CurrencyConverter $converter */
    protected $converter;

    /**
     * FeedGenerator constructor.
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlInterface
     * @param Image $imageHelper
     * @param DirectoryList $directory
     * @param LoggerInterface $logger
     * @param ConvertCurrency $converter
     */
    public function __construct(CollectionFactory $collectionFactory, UrlInterface $urlInterface, Image $imageHelper, DirectoryList $directory, LoggerInterface $logger, ConvertCurrency $converter)
    {
        $this->collectionFactory = $collectionFactory;
        $this->urlInterface = $urlInterface;
        $this->imageHelper = $imageHelper;
        $this->directory = $directory;
        $this->logger = $logger;
        $this->converter = $converter;
    }

    /**
     * Retrieves the product list
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getProductList()
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*');

        return $collection;
    }

    /**
     * Generates an XML file containing all the products in the store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateFeed()
    {
        $document = new \DOMDocument('1.0');
        $document->formatOutput = true;
        $document->preserveWhiteSpace = true;

        $rss = $document->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $document->appendChild($rss);

        $channel = $document->createElement('channel');
        $channel->appendChild($document->createElement('title', 'All Products'));
        $channel->appendChild($document->createElement('description', 'This XML contains all the products in the store.'));
        $channel->appendChild($document->createElement('link', $this->urlInterface->getBaseUrl()));

        if ($this->getProductList()->count()) {
            foreach ($this->getProductList() as $product) {

                // get all the product data
                $productInformation = $product->getData();

                // create xml element <item>
                $item = $document->createElement('item');

                // price value
                $price = $productInformation['price'];

                // iterate to each product data and create a node
                foreach ($productInformation as $key => $info) {
                    $images = ['image', 'small_image', 'thumbnail'];
                    if (in_array($key, $images)) {
                        // get the human readable link of all the images
                        $item->appendChild($document->createElement($key, $this->imageHelper->init($product, 'product_base_image')->getUrl()));
                    } else $item->appendChild($document->createElement($key, $info));

                    if ($key == 'price') $price = $info;
                }

                $convertedAmount = $this->converter->convert($price);
                $item->appendChild($document->createElement('converted_price', $convertedAmount !== false ? $convertedAmount : 'n/a'));

                $channel->appendChild($item);
            }
        }

        $rss->appendChild($channel);

        $content = $document->saveXML();
        $this->writeToFile($content);
        return $content;
    }

    /**
     * @param $content
     */
    private function writeToFile($content)
    {
        try {
            $file = fopen($this->directory->getRoot() . '/' . self::OUTPUT_FILENAME, 'w');
            fwrite($file, $content);
            fclose($file);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

    }


}