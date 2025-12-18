<?php

namespace WeltPixel\ProductFeed\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Component\ComponentRegistrar;

class ImportGoogleProductTaxonomy implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ResourceConnection $resourceConnection
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resourceConnection,
        ComponentRegistrarInterface $componentRegistrar
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConnection = $resourceConnection;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $setup->startSetup();

        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'WeltPixel_ProductFeed');
        $taxanomyFilePath = $modulePath . DIRECTORY_SEPARATOR . 'import' .
            DIRECTORY_SEPARATOR . 'google_product_taxanomy.txt';


        if (file_exists($taxanomyFilePath)) {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('weltpixel_productfeed_google_product_category');

            $file = fopen($taxanomyFilePath, 'r');
            if ($file) {
                while (($line = fgets($file)) !== false) {
                    $parts = explode(' - ', $line, 2);
                    if (count($parts) == 2) {
                        $categoryId = trim($parts[0]);
                        $categoryName = trim($parts[1]);

                        $connection->insert($tableName, [
                            'category_id' => $categoryId,
                            'category_name' => $categoryName
                        ]);
                    }
                }
                fclose($file);
            }
        }

        $setup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddGoogleFeedAttributes::class
        ];
    }
}
