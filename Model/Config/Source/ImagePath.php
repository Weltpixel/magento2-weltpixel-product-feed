<?php

namespace WeltPixel\ProductFeed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ImagePath
 * @package WeltPixel\ProductFeed\Model\Config\Source
 */
class ImagePath implements ArrayInterface
{
    const CACHED_PATH = 'cached_path';
    const FULL_PATH = 'full_path';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::CACHED_PATH,
                'label' => 'Cached Path',
            ),
            array(
                'value' => self::FULL_PATH,
                'label' => 'Full Path',
            )
        );
    }
}
