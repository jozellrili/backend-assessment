<?php
/**
 * Created by PhpStorm.
 * User: jozell.rili
 * Date: 11/28/2019
 * Time: 4:48 PM
 */

namespace MindArc\GoogleFeedGenerator\Controller\Index;

use MindArc\GoogleFeedGenerator\Model\FeedGenerator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    /** @var FeedGenerator $feed */
    protected $feed;

    /**
     * Action constructor
     * @param FeedGenerator $feed
     * @param Context $context
     */
    public function __construct(FeedGenerator $feed, Context $context)
    {
        parent::__construct($context);
        $this->feed = $feed;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $content = $this->feed->generateFeed();
        header('Content-type: application/xml; charset=utf-8');
        echo $content;
        exit;
    }
}