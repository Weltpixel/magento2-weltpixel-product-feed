<?php

namespace WeltPixel\ProductFeed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class StorageFolder
 * @package WeltPixel\ProductFeed\Model\Config\Source
 */
class StorageFolder implements ArrayInterface
{
    const STORAGE_FOLDER_VAR = 'var';
    const STORAGE_FOLDER_MEDIA = 'media';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::STORAGE_FOLDER_VAR,
                'label' => 'Use Var Directory',
            ),
            array(
                'value' => self::STORAGE_FOLDER_MEDIA,
                'label' => 'Use Media Directory',
            )
        );
    }
}
