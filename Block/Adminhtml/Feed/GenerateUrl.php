<?php
namespace WeltPixel\ProductFeed\Block\Adminhtml\Feed;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;

/**
 * Class GenerateUrl
 * Block responsible for providing the feed generation URL for the admin interface
 */
class GenerateUrl extends Template
{
    /**
     * @var string
     */
    protected $_template = 'WeltPixel_ProductFeed::feed/generate_url.phtml';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * GenerateUrl constructor.
     *
     * @param Context $context
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get the URL for feed generation
     *
     * @return string
     */
    public function getGenerateUrl(): string
    {
        return $this->urlBuilder->getUrl('weltpixelproductfeed/productfeed/generatefeed');
    }
} 