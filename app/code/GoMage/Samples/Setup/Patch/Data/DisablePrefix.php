<?php declare(strict_types=1);

namespace GoMage\Samples\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DisablePrefix implements DataPatchInterface
{
    private ResourceConnection $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function apply()
    {
        $this->resourceConnection->getConnection()
            ->update('eav_attribute', ['is_required' => 0], ['attribute_code = ?' => 'prefix']);
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
