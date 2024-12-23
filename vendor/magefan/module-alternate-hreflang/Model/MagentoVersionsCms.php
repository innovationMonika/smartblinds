<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model;

use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;

class MagentoVersionsCms
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * NodeData constructor.
     * @param Manager $moduleManager
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Manager $moduleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @return bool|mixed
     */
    public function getObjectFactory()
    {
        if ($this->moduleManager->isEnabled('Magento_VersionsCms')) {
            try {
                $object = $this->objectManager->get(\Magento\VersionsCms\Model\Hierarchy\NodeFactory::class);
                return $object;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * @param $storeId
     * @param $identifier
     * @return string|null
     */
    public function getUrl($storeId, $identifier)
    {
        if ($this->getObjectFactory()) {
            $hierarchyModel = $this->getObjectFactory()->create([
                'data' => ['scope' => \Magento\VersionsCms\Model\Hierarchy\Node::NODE_SCOPE_STORE, 'scope_id' => $storeId]
            ])->getHeritage();

            $nodes = $hierarchyModel->getNodesData();
            $nodeModel = $this->getObjectFactory()->create();

            foreach ($nodes as $node) {
                $nodeData = $nodeModel->load($node['node_id']);

                if (!$nodeData ||
                    $nodeData->getParentNodeId() == null && !$nodeData->getTopMenuVisibility() ||
                    $nodeData->getParentNodeId() != null && $nodeData->getTopMenuExcluded() ||
                    $nodeData->getPageId() && !$nodeData->getPageIsActive()
                ) {
                    continue;
                }

                if ($nodeData->getIdentifier() == $identifier) {
                    return $nodeData->getUrl($storeId);
                }
            }
        }

        return null;
    }

    /**
     * @param $storeId
     * @return \Magento\VersionsCms\Model\ResourceModel\Hierarchy\Node\Collection|null
     */
    public function getNodeCollection($storeId)
    {
        if ($this->getObjectFactory()) {
            $hierarchyModel = $this->getObjectFactory()->create([
                'data' => ['scope' => \Magento\VersionsCms\Model\Hierarchy\Node::NODE_SCOPE_STORE, 'scope_id' => $storeId]
            ])->getHeritage();

            return $hierarchyModel->getNodesCollection();
        }

        return null;
    }
}
