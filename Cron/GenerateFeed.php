<?php
namespace WeltPixel\ProductFeed\Cron;

use WeltPixel\ProductFeed\Model\Feed\Generator as FeedGenerator;
use WeltPixel\ProductFeed\Model\ResourceModel\ProductFeed\CollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use WeltPixel\ProductFeed\Helper\Data as FeedHelper;

class GenerateFeed
{
    /**
     * @var FeedGenerator
     */
    protected $feedGenerator;

    /**
     * @var CollectionFactory
     */
    protected $feedCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var FeedHelper
     */
    protected $feedHelper;


    public function __construct(
        FeedGenerator $feedGenerator,
        CollectionFactory $feedCollectionFactory,
        LoggerInterface $logger,
        TimezoneInterface $timezone,
        FeedHelper $feedHelper
    ) {
        $this->feedGenerator = $feedGenerator;
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->feedHelper = $feedHelper;
    }

    public function execute()
    {
        if (!$this->feedHelper->isProductFeedEnabled()) {
            return $this;
        }

        try {
            $collection = $this->feedCollectionFactory->create();
            $collection->addFieldToFilter('feed_execution_mode', \WeltPixel\ProductFeed\Model\ProductFeed\Attribute\Source\FeedExecutionMode::MODE_SCHEDULE);
            $collection->addFieldToFilter('status', 1);

            $currentDate = $this->timezone->date();

            $currentDay = $currentDate->format('N'); // 1 (for Monday) through 7 (for Sunday)
            $currentTime = (int)$currentDate->format('G') * 60 + (int)$currentDate->format('i'); // Current time in minutes since midnight

            foreach ($collection as $feed) {
                try {
                    $feedCronDays = explode(',', $feed->getData('feed_cron_day'));
                    $feedCronTimes = explode(',', $feed->getData('feed_cron_time'));

                    if (in_array($currentDay, $feedCronDays) && in_array($currentTime, $feedCronTimes)) {
                        $this->feedGenerator->generate($feed);
                    }
                } catch (\Exception $e) {
                    $this->logger->error(sprintf(
                        'Error generating feed %s: %s',
                        $feed->getName(),
                        $e->getMessage()
                    ));
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Error in weltpixel_productfeed_generate cron job: %s', $e->getMessage()));
        }

        return $this;
    }
}
