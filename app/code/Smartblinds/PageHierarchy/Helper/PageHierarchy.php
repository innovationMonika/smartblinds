<?php declare(strict_types=1);

namespace Smartblinds\PageHierarchy\Helper;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Mageside\PageHierarchy\Helper\Config;

class PageHierarchy extends \Mageside\PageHierarchy\Helper\PageHierarchy
{
    private $pageRepository;
    private $phConfig;

    public function __construct(
        Context $context,
        CollectionFactory $pageCollectionFactory,
        PageRepositoryInterface $pageRepository,
        Config $phConfig
    ) {
        parent::__construct($context, $pageCollectionFactory, $pageRepository, $phConfig);
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->pageRepository = $pageRepository;
        $this->phConfig = $phConfig;
    }

    public function addCrumbs($id, $breadcrumbs, $currentId = null)
    {
        $page = $this->pageRepository->getById($id);
        $url = $this->phConfig->isHierarchyPath() ?
            $this->getUrl($this->getHierarchyUrlById($id, '', $page)) :
            $page->getIdentifier();
        $info = [
            'label' => __($page->getTitle()),
            'title' => __($page->getTitle()),
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

    public function getHierarchyUrlById($id, $url = '', $page = null)
    {
        if (is_null($page)) {
            $page = $this->pageRepository->getById($id);
        }
        if(empty($url)) {
            $url = $page->getIdentifier();
            if (!empty($page->getParentPageId())) {
                $url = $this->getHierarchyUrlById($page->getParentPageId(), $url);
            }
        }
        return $url;
    }
}
