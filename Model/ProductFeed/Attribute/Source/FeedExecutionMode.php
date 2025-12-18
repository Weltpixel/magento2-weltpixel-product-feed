<?php
namespace WeltPixel\ProductFeed\Model\ProductFeed\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class FeedExecutionMode
 * @package WeltPixel\ProductFeed\Model\ProductFeed\Attribute\Source
 */
class FeedExecutionMode implements SourceInterface, OptionSourceInterface
{

    /**
     * Frequencies
     */
    const MODE_MANUAL   = 'manual';
    const MODE_SCHEDULE   = 'schedule';

    /**
     * @return array
     */
    public function getExecutionModes()
    {
        return [
            self::MODE_MANUAL     => __('Manual'),
            self::MODE_SCHEDULE     => __('By Schedule'),
        ];
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions() {
        $result = [];

        foreach ($this->getExecutionModes() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve Option value text
     *
     * @param string $value
     * @return mixed
     */
    public function getOptionText($value) {
        $options = $this->getExecutionModes();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray() {
        return $this->getAllOptions();
    }

}
