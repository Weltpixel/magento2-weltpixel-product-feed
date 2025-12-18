<?php
namespace WeltPixel\ProductFeed\Model\ResourceModel\ProductFeed;

/**
 * Class Collection
 * @package WeltPixel\ProductFeed\Model\ResourceModel\ProductFeed
 */
class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('WeltPixel\ProductFeed\Model\ProductFeed', 'WeltPixel\ProductFeed\Model\ResourceModel\ProductFeed');
    }
}
