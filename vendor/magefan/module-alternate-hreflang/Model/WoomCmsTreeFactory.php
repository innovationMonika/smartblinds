<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model;

/**
 * Class Allow to create and receive WoomCms_Tree object
 * Use ObjectManager as WoomCms Tree cannot be installed together with this extension,
 * so cannot use object factories in the constructor.
 */
class WoomCmsTreeFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Module\Manager 
     */
    private $moduleManager;

    /**
     * WoomCmsTreeFactory constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->objectManager = $objectmanager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param $pageId
     * @return mixed
     */
    public function getTreeByPageId($pageId)
    {
        if ($this->moduleManager->isEnabled('Woom_CmsTree')) {
            try {
                $object = $this->objectManager->get(\Woom\CmsTree\Model\Page\TreeRepository::class)->getByPageId($pageId);
                return $object;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }
}
