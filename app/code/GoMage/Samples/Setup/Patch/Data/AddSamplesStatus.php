<?php declare(strict_types=1);

namespace GoMage\Samples\Setup\Patch\Data;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory as StatusFactory;

class AddSamplesStatus implements DataPatchInterface
{
    private StatusResource $statusResource;
    private StatusFactory $statusFactory;

    public function __construct(
        StatusResource $statusResource,
        StatusFactory $statusFactory
    ) {
        $this->statusResource = $statusResource;
        $this->statusFactory = $statusFactory;
    }

    public function apply()
    {
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => 'samples',
            'label'  => 'Samples',
        ]);
        try {
            $this->statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }

        $status->assignState(Order::STATE_COMPLETE, false, false);
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
