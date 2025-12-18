<?php
namespace WeltPixel\ProductFeed\Model\ProductFeed\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class FeedCronTime
 * @package WeltPixel\ProductFeed\Model\ProductFeed\Attribute\Source
 */
class FeedCronTime implements SourceInterface, OptionSourceInterface
{

    /**
     * @return array
     */
    public function getFeedTimes()
    {
        $options = [];
        $startDelay = 0;
        $startTime = strtotime('12:00 AM');
        $endTime = strtotime('11:59 PM');
        $time = $startTime;

        while ($time <= $endTime) {
            $formattedTime = date('h:i A', $time);
            $options[$startDelay] = $formattedTime;
            $startDelay += 30;
            $time = strtotime('+30 minutes', $time); // Increment by 30 minutes
        }

        return $options;
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions() {
        $result = [];

        foreach ($this->getFeedTimes() as $index => $value) {
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
        $options = $this->getFeedTimes();

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
