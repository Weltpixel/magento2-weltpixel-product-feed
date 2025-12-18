<?php
namespace WeltPixel\ProductFeed\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use WeltPixel\ProductFeed\Helper\Data as FeedHelper;

/**
 * Class ProductFeed
 * @package WeltPixel\ProductFeed\Model\ResourceModel
 */
class ProductFeed extends AbstractDb
{
    /**
     * @var FeedHelper
     */
    protected $feedHelper;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * ProductFeed constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param FeedHelper $feedHelper
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        FeedHelper $feedHelper,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->feedHelper = $feedHelper;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('weltpixel_productfeed', 'id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Exception
     */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $feedCronDay = $object->getData('feed_cron_day');
        if (is_array($feedCronDay)) {
            $object->setData('feed_cron_day', implode(",", $feedCronDay));
        }

        $feedCronTime = $object->getData('feed_cron_time');
        if (is_array($feedCronTime)) {
            $object->setData('feed_cron_time', implode(",", $feedCronTime));
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        try {
            $feedDirectory = $this->feedHelper->getFeedDirectory($object);
            
            if ($this->fileDriver->isDirectory($feedDirectory)) {
                $this->fileDriver->deleteDirectory($feedDirectory);
            }
        } catch (\Exception $e) {
            // Log error but don't stop deletion process
            $this->_logger->error('Error deleting feed directory: ' . $e->getMessage());
        }

        return parent::_afterDelete($object);
    }
}
