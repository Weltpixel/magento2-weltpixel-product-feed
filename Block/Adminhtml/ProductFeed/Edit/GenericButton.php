<?php
namespace WeltPixel\ProductFeed\Block\Adminhtml\ProductFeed\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \WeltPixel\ProductFeed\Model\ProductFeedFactory
     */
    protected $productFeedFactory;

    /**
     * @param Context $context
     * @param \WeltPixel\ProductFeed\Model\ProductFeedFactory $productFeedFactory
     */
    public function __construct(
        Context $context,
        \WeltPixel\ProductFeed\Model\ProductFeedFactory $productFeedFactory
    ) {
        $this->context = $context;
        $this->productFeedFactory = $productFeedFactory;
    }

    /**
     * Return item ID
     *
     * @return int|null
     */
    public function getProductFeedId()
    {
        try {
            /** @var \WeltPixel\ProductFeed\Model\ProductFeed $productFeed */
            $productFeed = $this->productFeedFactory->create();
            return $productFeed->load($this->context->getRequest()->getParam('id'))->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
