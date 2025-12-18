<?php
namespace WeltPixel\ProductFeed\Block\Adminhtml\ProductFeed\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use WeltPixel\ProductFeed\Block\Adminhtml\ProductFeed\Edit\GenericButton;

/**
 * Class GenerateFeedButton
 */
class GenerateFeedButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getProductFeedId()) {
            $data = [
                'label' => __('Generate Feed'),
                'class' => 'generate primary',
                'on_click' => 'return false;',
                'sort_order' => 26,
            ];
        }
        return $data;
    }
}
