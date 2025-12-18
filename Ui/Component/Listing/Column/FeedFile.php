<?php
namespace WeltPixel\ProductFeed\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use \WeltPixel\ProductFeed\Helper\Data as FeedHelper;

/**
 * Class FeedFile
 */
class FeedFile extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var FeedHelper
     */
    protected $_feedHelper;


    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param FeedHelper $feedHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        FeedHelper $feedHelper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_feedHelper = $feedHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['file_name']) && isset($item['file_type'])) {
                    $directoryPath = $this->_feedHelper->getFeedDirectory($item);
                    $fullFilePath = $directoryPath . $item['file_name'] . '.' . $item['file_type'];
                    $item['full_file_name'] = $item['file_name'] . '.' . $item['file_type'];

                    if (file_exists($fullFilePath)) {
                        $item['feed_file_url'] = $this->urlBuilder->getBaseUrl() . 'wpfeed/feed/download?id=' . $item['id'];

                        $publicLink = $this->_feedHelper->getFeedPublicLink($item);
                        if ($publicLink) {
                            $item['feed_file_link'] = $publicLink;
                        }
                    }

                }
            }
        }

        return $dataSource;
    }
}
