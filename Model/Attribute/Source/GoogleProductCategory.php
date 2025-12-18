<?php

namespace WeltPixel\ProductFeed\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\ResourceConnection;

class GoogleProductCategory extends AbstractSource
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * CustomOptions constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('weltpixel_productfeed_google_product_category');
            $select = $connection->select()->from($tableName, ['entity_id', 'category_id', 'category_name']);
            $results = $connection->fetchAll($select);

            $this->_options = [
                ['value' => '', 'label' => __(' ')]
            ];
            foreach ($results as $result) {
                $this->_options[] = ['value' => $result['entity_id'], 'label' => $result['category_id'] . ' - ' . $result['category_name']];
            }
        }
        return $this->_options;
    }
}
