<?php
/**
 * Created by PhpStorm.
 * User: jozell.rili
 * Date: 11/28/2019
 * Time: 4:35 PM
 */

namespace MindArc\GoogleFeedGenerator\Cron;

use MindArc\GoogleFeedGenerator\Model\FeedGenerator;
use Psr\Log\LoggerInterface;

class UpdateFeed
{
    /** @var FeedGenerator $feed */
    protected $feed;

    /** @var LoggerInterface $logger */
    protected $logger;

    /**
     * @param FeedGenerator $feed
     * @param LoggerInterface $logger
     */
    public function __construct(FeedGenerator $feed, LoggerInterface $logger)
    {
        $this->feed = $feed;
        $this->logger = $logger;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $this->logger->info('Refreshing google feed...');
        $this->feed->generateFeed();
        $this->logger->info('Google auto refresh done!');
        return $this;
    }
}