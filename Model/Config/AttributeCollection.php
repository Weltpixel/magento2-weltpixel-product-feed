<?php

namespace WeltPixel\ProductFeed\Model\Config;

class AttributeCollection
{

	/**
	 * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
	 */
	private $_attributeCollectionFactory;

	/**
	 * @param  \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
	 */
	public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory)
	{
		$this->_attributeCollectionFactory = $attributeCollectionFactory;
	}

    /**
     * @return string[]
     */
	public function getAttributesArray()
	{
        $arr = [
            '__empty_value' => __('Empty'),
            '__manual_value' => __('Manual Value'),
            'dynamic_attributes' => $this->getDynamicAttributes(),
            'entity_id' => __('Product Id [entity_id]'),
            'sku'       => __('Sku [sku]')
        ];
		$attributesCollection = $this->_attributeCollectionFactory->create();
		foreach ($attributesCollection as $attribute) {
            $arr[$attribute->getData('attribute_code')] = $attribute->getData('frontend_label') . ' [' . $attribute->getData('attribute_code') . ']';
		}
		return $arr;
	}

    /**
     * @return array
     */
    public function getDynamicAttributes()
    {
        return [
            '__dynamic_product_url' => __('Product Url'),
            '__dynamic_final_price' => __('Final Price'),
            '__dynamic_availability' => __('Availability'),
            '__dynamic_product_type' => __('Product Type / Full Category Path'),
            '__dynamic_base_image_link' => __('Base Image Link'),
            '__dynamic_small_image_link' => __('Small Image Link'),
            '__dynamic_thumbnail_image_link' => __('Thumbnail Image Link'),
            '__dynamic_item_group_id' => __('Item Group ID (Parent SKU for variants)')
        ];
    }
}
