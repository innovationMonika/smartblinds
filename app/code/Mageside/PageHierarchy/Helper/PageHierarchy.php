<?php
/**
 * Copyright © 2018 Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\PageHierarchy\Helper;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

/**
 * Class Config
 */
class PageHierarchy extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * type area
     */
    const FRONT = 1;
    const ADMIN = 2;

    /**
     * required keys
     */
    const PAGE_ID = 1;
    const PARENT_PAGE_ID = 2;

    /**
     * @var CollectionFactory
     */
    protected $pageCollectionFactory;

    protected $config;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var Config
     */
    private $phConfig;

    /**
     * PageHierarchy constructor.
     * @param Context $context
     * @param CollectionFactory $pageCollectionFactory
     * @param PageRepositoryInterface $pageRepository
     * @param Config $phConfig
     */
    public function __construct(
        Context $context,
        CollectionFactory $pageCollectionFactory,
        PageRepositoryInterface $pageRepository,
        Config $phConfig
    ) {
        parent::__construct($context);
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->pageRepository = $pageRepository;
        $this->phConfig = $phConfig;
    }

    /**
     * @return array
     */
    public function getRequiredKeys()
    {
        return [self::PAGE_ID => 'page_id',
            self::PARENT_PAGE_ID => 'parent_page_id'];
    }

    /**
     * @param $key
     * @return mixed|string|null
     */
    public function getRequiredKey($key)
    {
        return isset($this->getRequiredKeys()[$key]) ? $this->getRequiredKeys()[$key] : null;
    }

    /**
     * @param $area
     * @param Config|null $config
     * @param int $rootId
     * @param int $storeId
     * @return array
     */
    public function getArrayHierarchy($area, Config $config = null, $rootId = 0, $storeId = 0)
    {
        $collectionHierarchy = [];
        $collectionGroupByParent = $this->getCollectionByParentId($this->getKeysField($area), $area == self::ADMIN, $storeId);
        switch ($area) {
            case self::FRONT:
                if (!isset($config) || !$config->isEnabled()) {
                    return [];
                }

                $depth = $config->getTreeDepth();
                foreach ($collectionGroupByParent as $k => $list) {
                    if ($k == $rootId) {
                        $collectionHierarchy[] = $this->createTree($collectionGroupByParent, $list, $depth);
                    }
                }
                return $collectionHierarchy;
            case self::ADMIN:
                foreach ($collectionGroupByParent as $k => $list) {
                    if ($k == $rootId) {
                        $collectionHierarchy[] = $this->createTree($collectionGroupByParent, $list);
                    }
                }
                return $collectionHierarchy;
            default:
                return [];
        }
    }

    /**
     * @param $list
     * @param $parentsRoot
     * @param int $depth
     * @param int $level
     * @return array
     */
    protected function createTree($list, $parentsRoot, $depth = 0, $level = 0)
    {
        $tree = [];
        $pageIdField = $this->getRequiredKey(self::PAGE_ID);
        if (!empty($pageIdField)) {
            foreach ($parentsRoot as $k => $l) {
                if (isset($list[$l[$pageIdField]])) {
                    $level++;
                    if ($depth == 0 || $level != $depth) {
                        $l['children'] = $this->createTree($list, $list[$l[$pageIdField]], $depth, $level);
                    }
                }

                if ($this->phConfig->isHierarchyPath()) {
                    $l['identifier'] = $this->getHierarchyUrlById($l['page_id']);
                }

                $tree[] = $l;
            }
        }
        return $tree;
    }

    /**
     * @param $area
     * @return array
     */
    protected function getKeysField($area)
    {
        $keys = $this->getRequiredKeys();
        switch ($area) {
            case self::ADMIN:
                $keys[] = 'title';
                $keys[] = 'store_id';
                break;
            case self::FRONT:
                $keys[] = 'title';
                $keys[] = 'identifier';
                break;
            default:
                break;
        }
        return array_fill_keys($keys, '');
    }

    /**
     * @param $items
     * @param bool $isAdminArea
     * @param int $storeId
     * @return array
     */
    protected function getCollectionByParentId($items, $isAdminArea = false, $storeId = 0)
    {
        $cmsCollection = $this->pageCollectionFactory->create();
        $cmsCollection->addOrder('hr_sort_order', 'asc');
        $collectionGroupByParent = [];

        foreach ($cmsCollection as $page) {
            if (!$isAdminArea && !$page->getData('include_in_menu_hierarchy')) {
                continue;
            }

            if (!$this->existStore($page, $storeId)) {
                continue;
            }

            foreach (array_keys($items) as $k) {
                if (isset($page[$k])) {
                    $items[$k] = $page->getData($k);
                }
            }
            $parentField = $this->getRequiredKey(self::PARENT_PAGE_ID);
            if (!empty($parentField)) {
                $collectionGroupByParent[$page->getData($this->getRequiredKey(self::PARENT_PAGE_ID))][] = $items;
            }
        }
        return $collectionGroupByParent;
    }

    /**
     * @param $page
     * @param $storeId
     * @return bool
     */
    protected function existStore($page, $storeId)
    {
        return $storeId == 0 || in_array(0, $page->getData('store_id'))
            || in_array($storeId, $page->getData('store_id'));
    }

    /**Mageside\PageHierarchy\Helper\PageHierarchy
     * @param int $id
     * @param int $prentId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hisParentById(int $id, int $prentId)
    {
        $page = $this->pageRepository->getById($id);
        if ($page->getParentPageId() == $prentId) {
            return true;
        }

        return false;
    }


    /**Mageside\PageHierarchy\Helper\PageHierarchy
     * @param int $id
     * @param int $prentId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hisParents(int $id)
    {
        $page = $this->pageRepository->getById($id);
        if ($page->getParentPageId()) {
            return true;
        }

        return false;
    }

    /**
     * @param $id
     * @param string $url
     * @param null $page
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHierarchyUrlById($id, $url = '', $page = null)
    {
        if (is_null($page)) {
            $page = $this->pageRepository->getById($id);
        }
        $url = $page->getIdentifier();
        if (!empty($page->getParentPageId())) {
            $url = $this->getHierarchyUrlById($page->getParentPageId(), $url) . "/" . $url;
        }
        return $url;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    /**
     * @param $id
     * @param $breadcrumbs
     * @param null $currentId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addCrumbs($id, $breadcrumbs, $currentId = null)
    {
        $page = $this->pageRepository->getById($id);
        $url = $this->phConfig->isHierarchyPath() ? $this->getUrl($this->getHierarchyUrlById($id, '', $page)) : $page->getIdentifier();
        $info = [
            'label' => $page->getTitle(),
            'title' => $page->getTitle(),
            'link' => $url
        ];

        if (!empty($page->getParentPageId())) {
            if ($currentId == $id) {
                $info['link'] = '';
            }

            $this->addCrumbs($page->getParentPageId(), $breadcrumbs, $currentId);
            //create
            $breadcrumbs->addCrumb($page->getIdentifier(), $info);
        } else {

            $breadcrumbs->addCrumb('cms_page', $info);
        }

    }
}
