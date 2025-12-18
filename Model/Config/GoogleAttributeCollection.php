<?php

namespace WeltPixel\ProductFeed\Model\Config;

class GoogleAttributeCollection
{
    /**
     * @return string[]
     */
	public function getAttributesArray()
	{
        $attributes = [
            'basic_product_data' => [
                'id' => 'ID [id]',
                'title' => 'Title [title]',
                'description' => 'Description [description]',
                'link' => 'Link [link]',
                'image_link' => 'Image Link [image_link]',
                'additional_image_link' => 'Additional Image Link [additional_image_link]',
                'virtual_model_link' => 'Virtual Model Link [virtual_model_link]',
                'mobile_link' => 'Mobile Link [mobile_link]'
            ],
            'price_and_availability' => [
                'availability' => 'Availability [availability]',
                'availability_date' => 'Availability Date [availability_date]',
                'cost_of_goods_sold' => 'Cost of goods sold [cost_of_goods_sold]',
                'expiration_date' => 'Expiration Date [expiration_date]',
                'price' => 'Price [price]',
                'sale_price' => 'Sale Price [sale_price]',
                'sale_price_effective_date' => 'Sale Price Effective Date [sale_price_effective_date]',
                'unit_pricing_measure' => 'Unit Pricing Measure [unit_pricing_measure]',
                'unit_pricing_base_measure' => 'Unit Pricing Base Measure [unit_pricing_base_measure]',
                'installment' => 'Installment [installment]',
                'subscription_cost' => 'Subscription Cost [subscription_cost]',
                'loyalty_program' => 'Loyalty Program [loyalty_program]',
                'auto_pricing_min_price' => 'Auto Pricing Min Price [auto_pricing_min_price]'
            ],
            'product_category' => [
                'google_product_category' => 'Google Product Category [google_product_category]',
                'product_type' => 'Product Type [product_type]'
            ],
            'product_identifiers' => [
                'brand' => 'Brand [brand]',
                'gtin' => 'GTIN [gtin]',
                'mpn' => 'MPN [mpn]',
                'identifier_exists' => 'Identifier Exists [identifier_exists]'
            ],
            'detailed_product_description' => [
                'condition' => 'Condition [condition]',
                'adult' => 'Adult [adult]',
                'multipack' => 'Multipack [multipack]',
                'is_bundle' => 'Is Bundle [is_bundle]',
                'certification' => 'Certification [certification]',
                'energy_efficiency_class' => 'Energy Efficiency Class [energy_efficiency_class]',
                'min_energy_efficiency_class' => 'Min Energy Efficiency Class [min_energy_efficiency_class]',
                'max_energy_efficiency' => 'Max Energy Efficiency [max_energy_efficiency]',
                'age_group' => 'Age Group [age_group]',
                'color' => 'Color [color]',
                'gender' => 'Gender [gender]',
                'material' => 'Material [material]',
                'pattern' => 'Pattern [pattern]',
                'size' => 'Size [size]',
                'size_type' => 'Size Type [size_type]',
                'size_system' => 'Size System [size_system]',
                'item_group_id' => 'Item Group ID [item_group_id]',
                'product_length' => 'Product Length [product_length]',
                'product_width' => 'Product Width [product_width]',
                'product_height' => 'Product Height [product_height]',
                'product_weight' => 'Product Weight [product_weight]',
                'product_detail' => 'Product Detail [product_detail]',
                'product_highlight' => 'Product Highlight [product_highlight]'
            ],
            'shopping_campaigns' => [
                'ads_redirect' => 'Ads Redirect [ads_redirect]',
                'custom_label_0' => 'Custom Label 0 [custom_label_0]',
                'custom_label_1' => 'Custom Label 1 [custom_label_1]',
                'custom_label_2' => 'Custom Label 2 [custom_label_2]',
                'custom_label_3' => 'Custom Label 3 [custom_label_3]',
                'custom_label_4' => 'Custom Label 4 [custom_label_4]',
                'promotion_id' => 'Promotion ID [promotion_id]',
                'lifestyle_image_link' => 'Lifestyle Image Link [lifestyle_image_link]'
            ],
            'marketplaces' => [
                'external_seller_id' => 'External Seller ID [external_seller_id]'
            ],
            'destinations' => [
                'excluded_destination' => 'Excluded Destination [excluded_destination]',
                'included_destination' => 'Included Destination [included_destination]',
                'shipping_ads_excluded_country' => 'Shipping Ads Excluded Country [shipping_ads_excluded_country]',
                'pause' => 'Pause [pause]'
            ],
            'shipping' => [
                'shipping_country' => 'Shipping Country [country]',
                'shipping_region' => 'Shipping Region [region]',
                'shipping_postal_code' => 'Shipping Postal Code [postal_code]',
                'shipping_location_id' => 'Shipping Location Id [location_id]',
                'shipping_location_group_name' => 'Shipping Location Group Name [location_group_name]',
                'shipping_service' => 'Shipping Service [service]',
                'shipping_price' => 'Shipping Price [price]',
                'shipping_min_handle_time' => 'Shipping Minimum Handling Time [min_handle_time]',
                'shipping_max_handle_time' => 'Shipping Maximum Handling Time [max_handle_time]',
                'shipping_min_transit_time' => 'Shipping Minimum Transit Time [min_transit_time]',
                'shipping_max_transit_time' => 'Shipping Maximum Transit Time [max_transit_time]',
                'shipping_label' => 'Shipping Label [shipping_label]',
                'shipping_weight' => 'Shipping Weight [shipping_weight]',
                'shipping_length' => 'Shipping Length [shipping_length]',
                'shipping_width' => 'Shipping Width [shipping_width]',
                'shipping_height' => 'Shipping Height [shipping_height]',
                'ships_from_country' => 'Ships From Country [ships_from_country]',
                'max_handling_time' => 'Maximum Handling Time [max_handling_time]',
                'min_handling_time' => 'Minimum Handling Time [min_handling_time]',
                'free_shipping_treshold' => 'Free Shipping Treshold [free_shipping_treshold]'
            ],
            'tax' => [
                'tax_country' => 'Tax Country [country]',
                'tax_region' => 'Tax Region [region]',
                'tax_postal_code' => 'Tax Postal Code [postal_code]',
                'tax_location_id' => 'Tax Location Id [location_id]',
                'tax_rate' => 'Tax Rate [rate]',
                'tax_ship' => 'Tax Ship [ship]',
                'tax_category' => 'Tax Category [tax_category]',
            ],
        ];
		return $attributes;
	}

    /**
     * @return string
    */
    public function getDefaultGoogleFeedMappings()
    {
        $mappingsOptions = [];
        $attributeMappings = [
            'id' => ['sku', ''],
            'title' => ['name', ''],
            'description' => ['short_description', ''],
            'link' => ['__dynamic_product_url', ''],
            'image_link' => ['__dynamic_base_image_link', ''],
            'availability' => ['__dynamic_availability', ''],
            'price' => ['price', ''],
            'gtin' => ['__empty_value', ''],
            'mpn' => ['__empty_value', ''],
            'condition' => ['__empty_value', ''],
            'sale_price' => ['__dynamic_final_price', ''],
            'brand' => ['__empty_value', ''],
            'google_product_category' => ['wpx_feed_google_product_category', ''],
            'product_type' => ['__dynamic_product_type', '']
        ];

        foreach ($attributeMappings as $feedKey => $attribute) {
            $mappingsOptions[] = [
                'google_attribute' => $feedKey,
                'magento_attribute' => $attribute[0],
                'own_value' => $attribute[1]
            ];
        }

        return json_encode($mappingsOptions);
    }
}
