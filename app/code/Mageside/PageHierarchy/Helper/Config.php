<?php
/**
 * Copyright © 2018 Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\PageHierarchy\Helper;

use Mageside\PageHierarchy\Model\Config\TreeSourceOptionsSelect;

/**
 * Class Config
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Get module settings
     *
     * @param string $key
     * @param string $section
     * @return mixed
     */
    public function getConfigModule($key, $section = 'general')
    {
        return $this->scopeConfig
            ->getValue(
                "mageside_page_hierarchy/{$section}/{$key}",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if ($this->getConfigModule('enabled')
            && $this->isModuleOutputEnabled('Mageside_PageHierarchy')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getTreeDepth()
    {
        if ($this->getConfigModule('tree_depth')) {
            return $this->getConfigModule('tree_depth');
        }

        return 0;
    }

    /**
     * @return TreeSourceOptionsSelect
     */
    public function treeSource()
    {
        if ($this->getConfigModule('tree_source')) {
            return $this->getConfigModule('tree_source');
        }
    }

    /**
     * @return bool
     */
    public function isHierarchyPath()
    {
        if ($this->getConfigModule('hierarchy_path')
            && $this->isModuleOutputEnabled('Mageside_PageHierarchy')
        ) {
            return true;
        }

        return false;
    }

    public function isBreadcrumbs()
    {
        if ($this->getConfigModule('breadcrumbs')
            && $this->isModuleOutputEnabled('Mageside_PageHierarchy')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return false|mixed
     */
    public function getRouteBehavior()
    {
        if ($this->getConfigModule('default_route_behavior')
            && $this->isModuleOutputEnabled('Mageside_PageHierarchy')
        ) {
            return $this->getConfigModule('default_route_behavior');
        }

        return false;
    }

}
