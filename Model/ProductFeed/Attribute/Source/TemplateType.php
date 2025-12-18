<?php
namespace WeltPixel\ProductFeed\Model\ProductFeed\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class TemplateType
 * @package WeltPixel\ProductFeed\Model\ProductFeed\Attribute\Source
 */
class TemplateType implements SourceInterface, OptionSourceInterface
{

    /**
     * Frequencies
     */
    const TYPE_GOOGLE   = 'Google';

    /**
     * @return array
     */
    public function getTemplateTypes()
    {
        return [
            self::TYPE_GOOGLE     => __('Google')
        ];
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions() {
        $result = [];

        foreach ($this->getTemplateTypes() as $index => $value) {
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
        $options = $this->getTemplateTypes();

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
