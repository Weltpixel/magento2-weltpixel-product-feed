<?php
namespace WeltPixel\ProductFeed\Model\Feed;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use WeltPixel\ProductFeed\Model\ProductFeed;
use Magento\Framework\Exception\LocalizedException;
use WeltPixel\ProductFeed\Model\Feed\Generator\Google as GoogleFeedGenerator;

class Generator
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var GoogleFeedGenerator
     */
    protected $googleFeedGenerator;

    /**
     * Generator constructor.
     *
     * @param ProductCollectionFactory $productCollectionFactory
     * @param SerializerInterface $serializer
     * @param GoogleFeedGenerator $googleFeedGenerator
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        SerializerInterface $serializer,
        GoogleFeedGenerator $googleFeedGenerator
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->serializer = $serializer;
        $this->googleFeedGenerator = $googleFeedGenerator;
    }

    /**
     * @param ProductFeed $feed
     * @return string
     * @throws LocalizedException
     */
    public function generate(ProductFeed $feed)
    {
        try {
            $this->validateFeed($feed);
            $collection = $this->getProductCollection($feed);
            $fieldMappings = $this->getContentMappings($feed);

            $feedTemplateType = $feed->getTemplateType();

            switch ($feedTemplateType) {
                case \WeltPixel\ProductFeed\Model\ProductFeed\Attribute\Source\TemplateType::TYPE_GOOGLE:
                    $this->googleFeedGenerator->generateFeed($feed, $collection, $fieldMappings);
                    break;
            }

        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Error generating feed: %1', $e->getMessage())
            );
        }
    }

    /**
     * Get filtered product collection based on feed model
     *
     * @param ProductFeed $feed
     * @return ProductCollection
     * @throws LocalizedException
     */
    protected function getProductCollection(ProductFeed $feed)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addStoreFilter($feed->getStoreId())
            ->addAttributeToSelect('*');

        $this->applyBasicFilters($collection, $feed);
        $this->applyConditionsFilters($collection, $feed);

        return $collection;
    }

    /**
     * @param ProductCollection $collection
     * @param ProductFeed $feed
     * @return void
     */
    protected function applyConditionsFilters(ProductCollection $collection, ProductFeed $feed)
    {
        $conditions = $this->serializer->unserialize($feed->getConditionsSerialized());
        if (!is_array($conditions) || !isset($conditions['conditions'])) {
            return;
        }

        $productIdsFromConditions = $feed->getMatchingProductIds();
        $collection->addIdFilter($productIdsFromConditions);
    }

    /**
     * Apply basic product filters (status, visibility, stock)
     *
     * @param ProductCollection $collection
     * @param ProductFeed $feed
     */
    private function applyBasicFilters(ProductCollection $collection, ProductFeed $feed)
    {
        if ($feed->getExcludeDisabledProducts()) {
            $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        }

        if ($feed->getExcludeNotVisibleProducts()) {
            $collection->addAttributeToFilter('visibility', ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]);
        }

        if ($feed->getExcludeOutOfStockProducts()) {
            $collection->joinField(
                'stock_status',
                'cataloginventory_stock_status',
                'stock_status',
                'product_id=entity_id',
                '{{table}}.stock_status = ' . StockStatus::STATUS_IN_STOCK
            );
        }
    }

    /**
     * Get decoded content mappings
     *
     * @param ProductFeed $feed
     * @return array
     */
    private function getContentMappings(ProductFeed $feed)
    {
        if (!$feed->getContentMappings()) {
            return [];
        }

        return $this->serializer->unserialize($feed->getContentMappings());
    }

    /**
     * @param ProductFeed $feed
     * @return void
     * @throws LocalizedException
     */
    protected function validateFeed(ProductFeed $feed)
    {
        if (!$feed->getData('status')) {
            throw new LocalizedException(
                __('Feed is inactive. Make sure the feed is enabled to be able to generate it.')
            );
        }
    }
}
