<?php

namespace WeltPixel\ProductFeed\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Store\Model\ScopeInterface;
use WeltPixel\ProductFeed\Model\ProductFeed;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var string
     */
    const FEEDS_DIRECTORY_NAME = 'weltpixel-feeds';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var array
     */
    protected $storeCategories;

    /**
     * @var int
     */
    protected $rootCategoryId;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Data constructor.
     * @param Context $context
     * @param Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productRepository = $productRepository;
        $this->storeCategories = [];
    }

    /**
     * @return bool
     */
    public function isProductFeedEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue('weltpixel_productfeed/general/enable', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string
     */
    public function getStorageFolder($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_productfeed/general/storage_folder', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string
     */
    public function getImagePath($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_productfeed/general/image_path', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $storeId
     * @return integer
     */
    public function getBatchSize($storeId = null)
    {
        return (int)$this->scopeConfig->getValue('weltpixel_productfeed/general/batch_size', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $feed
     * @return array
     */
    public function fetchFeedAnalyticsParams($feed)
    {
        $feedProperties = [
            'feed_utm_source',
            'feed_utm_medium',
            'feed_utm_term',
            'feed_utm_campaign',
            'feed_utm_content'
        ];
        $feedAnalyticsParams = [];

        foreach ($feedProperties as $feedProperty) {
            $feedValue = $feed->getData($feedProperty, false);
            if (strlen(trim($feedValue)) > 0) {
                $feedAnalyticsParams[str_replace('feed_', '', $feedProperty)] = $feedValue;
            }
        }
        return $feedAnalyticsParams;
    }

    /**
     * Get feed file name with proper extension
     *
     * @param ProductFeed $feed
     * @return string
     */
    public function getFeedFileName(ProductFeed $feed)
    {
        $fileName = $feed->getFileName() ?: 'google_product_feed';
        return $fileName . '.' . $feed->getFileType();
    }

    /**
     * Get public URL for feed file
     *
     * @param mixed $item
     * @return string|bool
     */
    public function getFeedPublicLink($item)
    {
        if (is_array($item)) {
            $storeId = $item['store_id'];
            $fileName = $item['file_name'];
            $fileType =$item['file_type'];
            $itemId = $item['id'];
        } else {
            $storeId = $item->getStoreId();
            $fileName = $item->getFileName();
            $fileType = $item->getFileType();
            $itemId = $item->getId();
        }

        $feedDirectory = $this->getStorageFolder($storeId);

        if ($feedDirectory == 'media') {
            try {
                $store = $this->storeManager->getStore($storeId);
                $mediaBaseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

                // Build the complete URL
                $feedUrl = $mediaBaseUrl .
                          self::FEEDS_DIRECTORY_NAME . DIRECTORY_SEPARATOR .
                          $itemId . DIRECTORY_SEPARATOR .
                          $fileName . '.' . $fileType;

                return $feedUrl;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * @param $item
     * @return string
     */
    public function getFeedDirectory($item) {
        if (is_array($item)) {
            $storeId = $item['store_id'];
            $itemId = $item['id'];
        } else {
            $storeId = $item->getStoreId();
            $itemId = $item->getId();
        }

        $feedDirectory = $this->getStorageFolder($storeId);
        $directoryPath =  DirectoryList::VAR_DIR;

        switch ($feedDirectory) {
            case 'media':
                $directoryPath = DirectoryList::MEDIA;
                break;
            case 'var':
                $directoryPath = DirectoryList::VAR_DIR;
                break;
        }

        return  $this->filesystem->getDirectoryRead($directoryPath)->getAbsolutePath() . self::FEEDS_DIRECTORY_NAME . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductAvailability($product)
    {
        $inStockValue = 'in stock';
        $outOfStockValue = 'out of stock';

        $productAvailability = $outOfStockValue;
        if ($product->isAvailable()) {
            $productAvailability = $inStockValue;
        }

        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return $productAvailability;
        }

        $productStockItem = $product->getExtensionAttributes()->getStockItem();
        if (!$productStockItem) {
            $product = $this->productRepository->getById($product->getId());
            $productStockItem = $product->getExtensionAttributes()->getStockItem();
        }
        $backOrdersStatus = $productStockItem->getBackorders();

        if ($backOrdersStatus != \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO) {
            $productQuantity = $productStockItem->getQty();
            if ($productQuantity > 0) {
                $productAvailability = $inStockValue;
            } else {
                $productAvailability = $outOfStockValue;
            }
        }

        return $productAvailability;
    }

    /**
     * Returns category tree path
     * @param array $categoryIds
     * @param int $storeId
     * @return string
     */
    public function getGoogleFeedCategories($categoryIds, $storeId)
    {
        if (!count($categoryIds)) {
            return '';
        }

        if (empty($this->storeCategories)) {
            $this->_populateStoreCategories($storeId);
        }

        $categoryIds = $this->_filterStoreCategories($categoryIds);
        $categoryId = $categoryIds[0] ?? $this->rootCategoryId;

        $categoryPath = '';
        if (isset($this->storeCategories[$categoryId])) {
            $categoryPath = $this->storeCategories[$categoryId]['path'];
        }

        $categoryImploded = $this->_buildCategoryPath($categoryPath);
        if (count($categoryImploded)) {
            array_unshift( $categoryImploded, 'Home');
            return implode(' > ', $categoryImploded);
        }

        return '';
    }

    /**
     * @param string $categoryPath
     * @return []
     */
    private function _buildCategoryPath($categoryPath)
    {
        $categIds = explode('/', $categoryPath);
        $ignoreCategories = 2;
        if (count($categIds) < 3) {
            $ignoreCategories = 1;
        }
        /* first 2 categories can be ignored, or 1st if root category */
        $categoryIds = array_slice($categIds, $ignoreCategories);
        $categoriesWithNames = [];

        foreach ($categoryIds as $categoriId) {
            if (isset($this->storeCategories[$categoriId])) {
                $categoriesWithNames[] = $this->storeCategories[$categoriId]['name'];
            }
        }

        return $categoriesWithNames;
    }

    /**
     * Get all categories id, name for the current store view
     */
    private function _populateStoreCategories($storeId)
    {
        if (!$this->isProductFeedEnabled($storeId) || !empty($this->storeCategories)) {
            return;
        }

        $this->rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();

        $categories = $this->categoryCollectionFactory->create()
            ->setStoreId($storeId)
            ->addAttributeToFilter('path', ['like' => "1/{$this->rootCategoryId}%"])
            ->addAttributeToSelect('name');

        foreach ($categories as $categ) {
            $this->storeCategories[$categ->getData('entity_id')] = [
                'name' => $categ->getData('name'),
                'path' => $categ->getData('path')
            ];
        }
    }

    /**
     * @param array $categoryIds
     * @return array
     */
    private function _filterStoreCategories($categoryIds)
    {
        $filteredCategoryIds = [];
        foreach ($categoryIds as $categoryId) {
            if (isset($this->storeCategories[$categoryId])) {
                $filteredCategoryIds[] = $categoryId;
            }
        }

        if (count($categoryIds) > 1) {
            $filteredCategoryIds = array_diff_assoc($filteredCategoryIds, [$this->rootCategoryId]);
            $filteredCategoryIds = array_values($filteredCategoryIds);
        }

        return $filteredCategoryIds;
    }


}
