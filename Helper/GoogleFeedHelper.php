<?php

namespace WeltPixel\ProductFeed\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GoogleFeedHelper extends \WeltPixel\ProductFeed\Helper\Data
{
    /**
     * @param $optionsString
     * @param $selectedValue
     * @return false|string
     */
    public function markSelectedOptions($optionsString, $selectedValue)
    {
        $dom = new \DOMDocument();
        $dom->loadHTML('<select>' . $optionsString . '</select>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $select = $dom->getElementsByTagName('select')->item(0);

        foreach ($select->getElementsByTagName('option') as $option) {
            if ($option->getAttribute('value') == $selectedValue) {
                $option->setAttribute('selected', 'selected');
            } else {
                $option->removeAttribute('selected');
            }
        }

        $options = '';
        foreach ($select->childNodes as $child) {
            $options .= $dom->saveHTML($child);
        }

        return $options;
    }

    /**
     * @param $attributesArray
     * @return string
     */
    public function generateSelectOptionsFromArrayAttributes($attributesArray)
    {
        $attributesSelectOptions = '';
        foreach ($attributesArray as $attributeCode => $attributeName) {
            if (is_array($attributeName)) {
                $optGroupLabel = implode(' ', array_map('ucfirst', explode('_', $attributeCode)));
                $attributesSelectOptions .= '<optgroup label="' . $optGroupLabel . '" data-optgroup-name="' . $optGroupLabel . '">';
                foreach ($attributeName as $subAttributeCode => $subAttributeName) {
                    $attributesSelectOptions .= '<option value="' . $subAttributeCode . '">' . $subAttributeName . '</option>';
                }
                $attributesSelectOptions .= '</optgroup>';
            } else {
                $attributesSelectOptions .= '<option value="' . $attributeCode . '">' . $attributeName . '</option>';
            }
        }

        return $attributesSelectOptions;
    }

    /**
     * @return array
     */
    public function getRequiredGoogleAttributes()
    {
        return [
            'id',
            'title',
            'description',
            'link',
            'image_link',
            'availability',
            'price',
            'brand',
            'gtin',
            'mpn',
            'condition'
        ];
    }
}
