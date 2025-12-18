<?php
namespace WeltPixel\ProductFeed\Model\Feed\Generator;

use WeltPixel\ProductFeed\Helper\GoogleFeedHelper;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use WeltPixel\ProductFeed\Model\ProductFeed;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use \WeltPixel\ProductFeed\Model\Config\AttributeCollection as MagentoAttributeCollection;

class Google
{
    protected const GOOGLE_NS = 'http://base.google.com/ns/1.0';

    /**
     * @var int
     */
    protected $batchSize = 1000;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * @var string
     */
    protected $feedDirectory = '';

    /**
     * @var GoogleFeedHelper
     */
    protected $googleFeedHelper;

    /**
     * @var \XMLWriter
     */
    protected $xmlWriter;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MagentoAttributeCollection
     */
    protected $magentoAttributeCollection;

    /**
     * @var array
     */
    protected array $dynamicAttributes;

    /**
     * @var mixed
     */
    protected mixed $attributeOptions;

    /**
     * @var string
     */
    protected $priceCurrency;

    /** @var \Magento\Catalog\Helper\Image */
    protected $imageHelper;

    /**
     * @var \Magento\Store\Model\App\Emulation $appEmulation
     */
    protected $appEmulation;

    /**
     * @var bool
     */
    protected $imageFullPath = false;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $catalogProductTypeConfigurable;

    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    protected $catalogProductTypeBundle;

    /**
     * @var array
     */
    protected $parentProducts = [];

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var array
     */
    protected $feedAnalytics;

    private const SHIPPING_ATTRIBUTES = [
        'shipping_country',
        'shipping_region',
        'shipping_postal_code',
        'shipping_location_id',
        'shipping_location_group_name',
        'shipping_service',
        'shipping_price',
        'shipping_min_handle_time',
        'shipping_max_handle_time',
        'shipping_min_transit_time',
        'shipping_max_transit_time'
    ];

    private const TAX_ATTRIBUTES = [
        'tax_country',
        'tax_region',
        'tax_postal_code',
        'tax_location_id',
        'tax_rate',
        'tax_ship'
    ];

    /**
     * @param GoogleFeedHelper $googleFeedHelper
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param StoreManagerInterface $storeManager
     * @param MagentoAttributeCollection $magentoAttributeCollection
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param \Magento\Bundle\Model\Product\Type $catalogProductTypeBundle
     */
    public function __construct(
        GoogleFeedHelper $googleFeedHelper,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        StoreManagerInterface $storeManager,
        MagentoAttributeCollection $magentoAttributeCollection,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Bundle\Model\Product\Type $catalogProductTypeBundle
    ) {
        $this->googleFeedHelper = $googleFeedHelper;
        $this->fileDriver = $fileDriver;
        $this->storeManager = $storeManager;
        $this->magentoAttributeCollection = $magentoAttributeCollection;
        $this->imageHelper = $imageHelper;
        $this->appEmulation = $appEmulation;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->catalogProductTypeBundle = $catalogProductTypeBundle;
    }

    /**
     * Generate Google Product Feed XML
     *
     * @param ProductFeed $feed
     * @param ProductCollection $collection
     * @param array $fieldMappings
     * @throws LocalizedException
     * @return string
     */
    public function generateFeed(ProductFeed $feed, ProductCollection $collection, array $fieldMappings)
    {
        try {
            $this->storeId = $feed->getStoreId();
            $this->appEmulation->startEnvironmentEmulation($this->storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
            $store = $this->storeManager->getStore($this->storeId);
            $this->priceCurrency = $store->getCurrentCurrencyCode();
            $this->googleFeedHelper->getImagePath($this->storeId) == \WeltPixel\ProductFeed\Model\Config\Source\ImagePath::FULL_PATH ? $this->imageFullPath = true : $this->imageFullPath = false;
            $this->feedAnalytics = $this->googleFeedHelper->fetchFeedAnalyticsParams($feed);

            $this->batchSize = $this->googleFeedHelper->getBatchSize($this->storeId);
            $this->dynamicAttributes = array_merge(['price', 'special_price'], array_keys($this->magentoAttributeCollection->getDynamicAttributes()));

            $fileName = $this->googleFeedHelper->getFeedFileName($feed);
            $this->feedDirectory = $this->googleFeedHelper->getFeedDirectory($feed);
            $filePath = $this->feedDirectory.$fileName;

            $this->createFeedDirectory();

            // Initialize XML Writer
            $this->xmlWriter = new \XMLWriter();
            $this->xmlWriter->openURI($filePath);
            $this->xmlWriter->setIndent(true);
            $this->xmlWriter->setIndentString('    ');

            // Start XML file with header and root elements
            $this->initializeFeedFile($feed, $store);

            // Process products in batches
            $collection->setPageSize($this->batchSize);
            $lastPage = $collection->getLastPageNumber();


            for ($currentPage = 1; $currentPage <= $lastPage; $currentPage++) {
                $collection->setCurPage($currentPage);
                $this->processProductBatch($collection, $fieldMappings);
                $collection->clear();
            }

            // Close XML root elements
            $this->finalizeFeedFile();

            return $fileName;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Error generating Google feed: %1', $e->getMessage()));
        } finally {
            $this->appEmulation->stopEnvironmentEmulation();
        }
    }

    /**
     * Initialize feed file with XML header and opening tags
     *
     * @param ProductFeed $feed
     * @param StoreManagerInterface $store
     */
    protected function initializeFeedFile($feed, $store)
    {
        $this->xmlWriter->startDocument('1.0', 'UTF-8');

        // Start RSS element with Google namespace
        $this->xmlWriter->startElement('rss');
        $this->xmlWriter->writeAttribute('version', '2.0');
        $this->xmlWriter->writeAttribute('xmlns:g', self::GOOGLE_NS);

        // Start channel element
        $this->xmlWriter->startElement('channel');

        // Write feed information with proper escaping
        $this->xmlWriter->startElement('title');
        $this->xmlWriter->text($this->sanitizeText($feed->getName()));
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement('link');
        $this->xmlWriter->text($this->sanitizeText($store->getBaseUrl()));
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement('description');
        $this->xmlWriter->text($this->sanitizeText('Google Shopping Feed for ' . $store->getName()));
        $this->xmlWriter->endElement();
    }

    /**
     * Process a batch of products and append to feed file
     *
     * @param ProductCollection $collection
     * @param array $fieldMappings
     */
    protected function processProductBatch($collection, $fieldMappings)
    {
        $this->parentProducts = [];
        foreach ($collection as $product) {
            // check if product should be ignored
            $shouldProductBeParsed = $this->shoudlProductBeParsed($product);
            if (!$shouldProductBeParsed) {
                continue;
            }

            $this->xmlWriter->startElement('item');

            $shippingAttributes = [];
            $taxAttributes = [];

            foreach ($fieldMappings as $mappingDetails) {
                $googleAttribute = $mappingDetails['google_attribute'];
                $magentoAttribute = $mappingDetails['magento_attribute'];
                $ownValue = $mappingDetails['own_value'] ?? '';

                switch ($magentoAttribute) {
                    case '__empty_value':
                        $value = '';
                        break;
                    case '__manual_value':
                        $value = $ownValue;
                        break;
                    default:
                        $value = $this->getProductAttributeValue($product, $magentoAttribute);
                        break;
                }

                // Collect shipping attributes
                if (in_array($googleAttribute, self::SHIPPING_ATTRIBUTES)) {
                    $attributeName = str_replace('shipping_', '', $googleAttribute);
                    $shippingAttributes[$attributeName] = $value;
                    continue;
                }

                // Collect tax attributes
                if (in_array($googleAttribute, self::TAX_ATTRIBUTES)) {
                    $attributeName = str_replace('tax_', '', $googleAttribute);
                    $taxAttributes[$attributeName] = $value;
                    continue;
                }

                // Skip writing attribute if value is null (used to omit optional fields)
                if ($value === null) {
                    continue;
                }

                // Skip sale_price if value is 0.00 or invalid
                if ($googleAttribute === 'sale_price' && $this->isInvalidSalePrice($value)) {
                    continue;
                }

                // Write regular attributes
                $this->writeGoogleAttribute($googleAttribute, $value);
            }

            // Write shipping information if exists
            if (!empty($shippingAttributes)) {
                $this->xmlWriter->startElement('g:shipping');
                foreach ($shippingAttributes as $attribute => $value) {
                    $this->writeGoogleAttribute($attribute, $value);
                }
                $this->xmlWriter->endElement(); // g:shipping
            }

            // Write tax information if exists
            if (!empty($taxAttributes)) {
                $this->xmlWriter->startElement('g:tax');
                foreach ($taxAttributes as $attribute => $value) {
                    $this->writeGoogleAttribute($attribute, $value);
                }
                $this->xmlWriter->endElement(); // g:tax
            }

            $this->xmlWriter->endElement(); // item
        }
    }

    /**
     * Write Google attribute with proper namespace
     *
     * @param string $attribute
     * @param string|array $value
     */
    protected function writeGoogleAttribute($attribute, $value)
    {
        if (is_array($value)) {
            // Handle nested attributes
            $this->xmlWriter->startElement('g:' . $attribute);
            foreach ($value as $subAttribute => $subValue) {
                $this->writeGoogleAttribute($subAttribute, $subValue);
            }
            $this->xmlWriter->endElement();
        } else {
            $this->xmlWriter->startElement('g:' . $attribute);

            // Check if the content contains HTML
            if ($this->containsHTML($value)) {
                // Write as CDATA if contains HTML
                $this->xmlWriter->writeCData($this->sanitizeHTML((string)$value));
            } else {
                // Write as regular text if no HTML
                $this->xmlWriter->text($this->sanitizeText((string)$value));
            }

            $this->xmlWriter->endElement();
        }
    }

    /**
     * Check if a string contains HTML
     *
     * @param string|null $string
     * @return bool
     */
    private function containsHTML($string): bool
    {
        if ($string === null) {
            return false;
        }
        return strip_tags((string)$string) !== (string)$string;
    }

    /**
     * Sanitize HTML content
     *
     * @param string|null $html
     * @return string
     */
    private function sanitizeHTML(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        // Remove invalid XML characters
        $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html);

        // Convert special characters to HTML entities where needed
        $html = htmlspecialchars_decode($html, ENT_QUOTES | ENT_XML1);

        // Ensure proper UTF-8 encoding
        if (!mb_check_encoding($html, 'UTF-8')) {
            $html = mb_convert_encoding($html, 'UTF-8', 'auto');
        }

        return $html;
    }

    /**
     * Sanitize plain text content
     *
     * @param string|null $text
     * @return string
     */
    private function sanitizeText(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        // Remove invalid XML characters
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Ensure proper UTF-8 encoding
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }

        // Trim whitespace
        return trim($text);
    }

    /**
     * Close XML root elements
     */
    protected function finalizeFeedFile()
    {
        $this->xmlWriter->endElement(); // channel
        $this->xmlWriter->endElement(); // rss
        $this->xmlWriter->endDocument();
    }


    /**
     * Create feed directory if it doesn't exist
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function createFeedDirectory()
    {
        if (!$this->fileDriver->isDirectory($this->feedDirectory)) {
            $this->fileDriver->createDirectory($this->feedDirectory);
        }
    }

    /**
     * Get product attribute value
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string|array $attribute
     * @return mixed
     */
    protected function getProductAttributeValue($product, $attribute)
    {
        if ($attribute == '__dynamic_availability') {
            $product = $product->load($product->getId());
        }
        if (in_array($attribute, $this->dynamicAttributes)) {
            // Custom attribute mapping logic
            return $this->getDynamicAttributeValue($product, $attribute);
        }

        $attributeValue = '';
        $attributeOptions = $this->getAttributeOptions($product, $attribute);
        $frontendValue =  $product->getData($attribute);

        // If frontend value is empty/null and product is a simple child of configurable, try parent first
        if (($frontendValue === null || $frontendValue === '' || $frontendValue === false) &&
            $product->getVisibility() == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE &&
            $product->getTypeId() == 'simple') {

            $parentProduct = $this->getParentProduct($product);
            if ($parentProduct) {
                $frontendValue = $parentProduct->getData($attribute);
                // If parent also doesn't have the attribute, keep the original product for attribute options
                if ($frontendValue !== null && $frontendValue !== '' && $frontendValue !== false) {
                    $product = $parentProduct;
                    $attributeOptions = $this->getAttributeOptions($product, $attribute);
                }
            }
        }

        // Now process the frontend value (either from child or inherited from parent)
        if (is_array($frontendValue)) {
            $result = [];
            foreach ($frontendValue as $value) {
                $result[] = ($attributeOptions[$value]) ? $attributeOptions[$value] : null;
            }
            $attributeValue = implode(',', array_filter($result));
        } elseif (isset($attributeOptions[$frontendValue])) {
            $attributeValue = $attributeOptions[$frontendValue];
        } else {
            $attributeValue = $frontendValue ?? '';
        }

        return $attributeValue;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $attribute
     * @return mixed|string
     */

    /**
     * @param $product
     * @param $attribute
     * @return mixed|string
     */
    protected function getDynamicAttributeValue($product, $attribute)
    {
        switch ($attribute) {
            case '__dynamic_product_url':
                return $this->getProductUrl($product);
            case '__dynamic_final_price':
                return sprintf('%.2f', floatval($product->getPriceInfo()->getPrice('final_price')->getValue())) . ' ' . $this->priceCurrency;
            case 'price':
                return sprintf('%.2f', floatval($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue())) . ' ' . $this->priceCurrency;
            case 'special_price':
                return sprintf('%.2f', floatval($product->getPriceInfo()->getPrice('special_price')->getAmount()->getValue())) . ' ' . $this->priceCurrency;
            case '__dynamic_availability':
                return $this->googleFeedHelper->getProductAvailability($product);
            case '__dynamic_product_type':
                $categoryIds = $product->getCategoryIds();
                // If no categories and product is a simple child of configurable, try parent
                if (empty($categoryIds) &&
                    $product->getVisibility() == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE &&
                    $product->getTypeId() == 'simple') {
                    $parentProduct = $this->getParentProduct($product);
                    if ($parentProduct) {
                        $categoryIds = $parentProduct->getCategoryIds();
                    }
                }
                return $this->googleFeedHelper->getGoogleFeedCategories($categoryIds, $this->storeId);
            case '__dynamic_item_group_id':
                return $this->getItemGroupId($product);
            case '__dynamic_base_image_link':
                return $this->getProductImage($product, 'image');
            case '__dynamic_small_image_link':
                return $this->getProductImage($product, 'small_image');
            case '__dynamic_thumbnail_image_link':
                return $this->getProductImage($product, 'thumbnail');
            default:
                return '';
        }
    }

    /**
     * @param $product
     * @return bool
     */
    protected function shoudlProductBeParsed($product)
    {
        $productVisibility = $product->getVisibility();
        if ($productVisibility == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE && $product->getTypeId() == 'simple') {
            $parentIds = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId());
            if (count($parentIds)) {
                return true;
            }
            $parentIds = $this->catalogProductTypeBundle->getParentIdsByChild($product->getId());
            if (count($parentIds)) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Get parent product for a simple product (configurable or bundle)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product|null
     */
    protected function getParentProduct($product)
    {
        if ($product->getVisibility() != \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE ||
            $product->getTypeId() != 'simple') {
            return null;
        }

        // Check for configurable parent
        $parentIds = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId());
        if (count($parentIds) > 0) {
            if (isset($this->parentProducts[$parentIds[0]])) {
                return $this->parentProducts[$parentIds[0]];
            } else {
                $parentProduct = clone $product;
                $parentProduct = $parentProduct->load($parentIds[0]);
                $this->parentProducts[$parentIds[0]] = $parentProduct;
                return $parentProduct;
            }
        }

        // Check for bundle parent
        $parentIds = $this->catalogProductTypeBundle->getParentIdsByChild($product->getId());
        if (count($parentIds) > 0) {
            if (isset($this->parentProducts[$parentIds[0]])) {
                return $this->parentProducts[$parentIds[0]];
            } else {
                $parentProduct = clone $product;
                $parentProduct = $parentProduct->load($parentIds[0]);
                $this->parentProducts[$parentIds[0]] = $parentProduct;
                return $parentProduct;
            }
        }

        return null;
    }

    /**
     * @param $product
     * @return mixed
     */
    protected function getProductUrl($product)
    {
        $baseUrl = '';
        $productVisibility = $product->getVisibility();
        if ($productVisibility == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE && $product->getTypeId() == 'simple') {
            // check if product is part of a configurable product
            $parentIds = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId());

            if (count($parentIds) > 0) {
                $parentProduct = clone $product;
                if (isset($this->parentProducts[$parentIds[0]])) {
                    $parentProduct = $this->parentProducts[$parentIds[0]];
                } else {
                    $parentProduct = $parentProduct->load($parentIds[0]);
                    $this->parentProducts[$parentIds[0]] = $parentProduct;
                }
                $baseUrl = $parentProduct->getProductUrl();
            }

            $parentIds = $this->catalogProductTypeBundle->getParentIdsByChild($product->getId());
            if (count($parentIds) > 0) {
                $parentProduct = clone $product;
                if (isset($this->parentProducts[$parentIds[0]])) {
                    $parentProduct = $this->parentProducts[$parentIds[0]];
                } else {
                    $parentProduct = $parentProduct->load($parentIds[0]);
                    $this->parentProducts[$parentIds[0]] = $parentProduct;
                }
                $baseUrl = $parentProduct->getProductUrl();
            }
        } else {
            $baseUrl = $product->getProductUrl();
        }

        // Append analytics parameters if they exist
        if (!empty($this->feedAnalytics) && !empty($baseUrl)) {
            $queryParams = [];
            foreach ($this->feedAnalytics as $key => $value) {
                if (!empty($value)) {
                    $queryParams[] = rawurlencode($key) . '=' . rawurlencode($value);
                }
            }
            
            if (!empty($queryParams)) {
                $baseUrl .= (strpos($baseUrl, '?') !== false ? '&' : '?') . implode('&', $queryParams);
            }
        }

        return $baseUrl;
    }

    /**
     * @param $product
     * @param $imageType
     * @return string
     */
    protected function getProductImage($product, $imageType) {
        // Check if simple product has no image and has a configurable parent
        $imagePath = $product->getData($imageType);
        if ((!$imagePath || $imagePath == 'no_selection') &&
            $product->getVisibility() == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE &&
            $product->getTypeId() == 'simple') {

            // Try to get parent product image
            $parentIds = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId());
            if (count($parentIds) > 0) {
                $parentProduct = null;
                if (isset($this->parentProducts[$parentIds[0]])) {
                    $parentProduct = $this->parentProducts[$parentIds[0]];
                } else {
                    $parentProduct = clone $product;
                    $parentProduct = $parentProduct->load($parentIds[0]);
                    $this->parentProducts[$parentIds[0]] = $parentProduct;
                }

                // Use parent product for image generation
                if ($parentProduct && $parentProduct->getData($imageType) && $parentProduct->getData($imageType) != 'no_selection') {
                    $product = $parentProduct;
                }
            }
        }

        if ($this->imageFullPath) {
            return $this->getFullImageUrl($product, $imageType);
        }

        $imageId = 'weltpixel_productfeed_' . $imageType;
        return $this->imageHelper->init($product, $imageId)->constrainOnly(FALSE)->keepAspectRatio(TRUE)->keepFrame(TRUE)->getUrl();
        }


    /**
     * Get full image URL for product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageType
     * @return string
     */
    protected function getFullImageUrl($product, $imageType)
    {
        try {
            $store = $this->storeManager->getStore();
            $mediaBaseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            // Get image path
            $imagePath = $product->getData($imageType);

            // Check if simple product has no image and has a configurable parent
            if ((!$imagePath || $imagePath == 'no_selection') &&
                $product->getVisibility() == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE &&
                $product->getTypeId() == 'simple') {

                // Try to get parent product image
                $parentIds = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId());
                if (count($parentIds) > 0) {
                    $parentProduct = null;
                    if (isset($this->parentProducts[$parentIds[0]])) {
                        $parentProduct = $this->parentProducts[$parentIds[0]];
                    } else {
                        $parentProduct = clone $product;
                        $parentProduct = $parentProduct->load($parentIds[0]);
                        $this->parentProducts[$parentIds[0]] = $parentProduct;
                    }

                    // Use parent product image path
                    if ($parentProduct && $parentProduct->getData($imageType) && $parentProduct->getData($imageType) != 'no_selection') {
                        $imagePath = $parentProduct->getData($imageType);
                    }
                }
            }

            // Return empty if no image
            if (!$imagePath || $imagePath == 'no_selection') {
                return '';
            }

            // Clean up image path
            $imagePath = ltrim(str_replace('\\', '/', $imagePath), '/');

            // For product images, prepend catalog/product
            if (strpos($imagePath, 'catalog/product') !== 0) {
                $imagePath = 'catalog/product/' . $imagePath;
            }

            // Construct full URL
            return $mediaBaseUrl . $imagePath;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attribute
     * @return array
     */
    protected function getAttributeOptions($product, $attribute)
    {
        if (empty($this->attributeOptions[$attribute])) {
            try {
                $options = $product->getResource()->getAttribute($attribute)->getSource()->getAllOptions();
                foreach ($options as $option) {
                    $this->attributeOptions[$attribute][$option['value']] = $option['label'];
                }
            } catch (\Exception $ex) {
            }
        }

        return $this->attributeOptions[$attribute];
    }

    /**
     * Get item group ID for product variants
     * Returns parent product SKU for simple products that are children of configurable products
     * Returns null for standalone products or parent products (to omit the field from feed)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string|null
     */
    protected function getItemGroupId($product)
    {
        // Only return item_group_id for simple products that are not visible individually
        if ($product->getVisibility() != \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE ||
            $product->getTypeId() != 'simple') {
            return null;
        }

        // Get parent product
        $parentProduct = $this->getParentProduct($product);
        if (!$parentProduct) {
            return null;
        }

        // Return parent product SKU as the item_group_id
        return $parentProduct->getSku();
    }

    /**
     * Check if sale_price value is invalid (0.00 or empty)
     * Google Shopping doesn't accept sale_price with value 0.00
     *
     * @param string $value
     * @return bool
     */
    protected function isInvalidSalePrice($value)
    {
        if (empty($value)) {
            return true;
        }

        // Extract numeric value from price string (e.g., "0.00 USD" -> 0.00)
        $numericValue = floatval($value);

        // Consider price invalid if it's 0 or negative
        return $numericValue <= 0;
    }
}
