<?php declare(strict_types=1);

namespace Smartblinds\ImageImport\Model\ResourceModel\Images;

use Magento\Framework\App\ResourceConnection;

class DropValues
{
    private ResourceConnection $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(array $rows)
    {
        $connection = $this->resourceConnection->getConnection();

        $galleryValuesToDelete = array_keys($rows);
        if ($galleryValuesToDelete) {
            $connection->delete(
                'catalog_product_entity_media_gallery',
                ['value_id IN (?)' => $galleryValuesToDelete]
            );
        }

        $varcharValuesToDelete = array_values(array_filter(array_values($rows)));
        if ($varcharValuesToDelete) {
            $connection->delete(
                'catalog_product_entity_varchar',
                ['value_id IN (?)' => $varcharValuesToDelete]
            );
        }

        return 0;
    }
}
