<?php
namespace WeltPixel\ProductFeed\Model\ProductFeed\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class FileType
 * @package WeltPixel\ProductFeed\Model\ProductFeed\Attribute\Source
 */
class FileType implements SourceInterface, OptionSourceInterface
{

    /**
     * Frequencies
     */
    const FILE_XML   = 'xml';

    /**
     * @return array
     */
    public function getFileTypes()
    {
        return [
            self::FILE_XML     => __('XML')
        ];
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions() {
        $result = [];

        foreach ($this->getFileTypes() as $index => $value) {
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
        $options = $this->getFileTypes();

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
