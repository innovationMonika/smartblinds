<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Block\Adminhtml\Rule\Edit;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Form\FieldFactory;
use Magento\Ui\Component\Form\Fieldset as BaseFieldset;
use Magento\Store\Model\StoreManagerFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magefan\AlternateHreflang\Api\AlternateHreflangRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magefan\AlternateHreflang\Block\Adminhtml\Rule\Edit\Fieldset;

/**
 * Add Alternate Hreflang Form fields to the edit form
 */
class Form extends BaseFieldset
{
    /**
     * Config path
     */
    const ALTERNATE_HREFLANG_ENABLED = 'alternatehreflang/general/enabled';

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var array
     */
    protected $storeGroup = [];

    /**
     * @var StoreManagerFactory
     */
    protected $_storeManager;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AlternateHreflangRepositoryInterface
     */
    protected $alternateHreflangRepository;

    /**
     * @var array
     */
    protected $factory;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Form constructor.
     * @param ContextInterface $context
     * @param StoreManagerFactory $storeManager
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AlternateHreflangRepositoryInterface $alternateHreflangRepository
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     * @param array $factory
     * @param FieldFactory $fieldFactory
     * @param array $components
     * @param array $data
     * @param null $id
     * @param null $type
     */
    public function __construct(
        ContextInterface $context,
        StoreManagerFactory $storeManager,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AlternateHreflangRepositoryInterface $alternateHreflangRepository,
        Http $request,
        ScopeConfigInterface $scopeConfig,
        array $factory,
        FieldFactory $fieldFactory,
        array $components = [],
        array $data = [],
        $id = null,
        $type = null
    ) {
        parent::__construct($context, $components, $data);
        $this->fieldFactory = $fieldFactory;
        $this->_storeManager = $storeManager;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->alternateHreflangRepository = $alternateHreflangRepository;
        $this->factory = isset($factory['object']) ? $factory['object'] : \Magento\Framework\App\ObjectManager::getInstance()
            ->get($factory['objectClass']);
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->id = $id;
        $this->type = $type;
    }

    /**
     * @return UiComponentInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChildComponents()
    {
        $id = $this->request->getParam($this->id);
        $storeManager = $this->_storeManager->create();

        $model = $this->factory->create()->load($id);

        $filter = $this->filterBuilder
            ->setField('parent_id')
            ->setValue($id)
            ->create();
        $filter1 = $this->filterBuilder
            ->setField('url_key')
            ->setValue($model->getIdentifier() ?: $model->getUrlKey())
            ->create();
        $filter2 = $this->filterBuilder
            ->setField('type')
            ->setValue($this->type)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter, $filter1, $filter2])->create();
        $localization = [];
        try {
            $switcherId = $this->alternateHreflangRepository->getList($searchCriteria)->getItems()[0]['id'];
            if ($switcherId) {
                $localization = $this->alternateHreflangRepository->getById($switcherId)->getLocalization();
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }

        if (\Magefan\AlternateHreflang\Model\Config::CATALOG_CATEGORY_TYPE == $this->type) {
            $notice = 'Please define alternatives for category ONLY when you have multi-website Magento instance and use '
                . 'different category IDs (Entity IDs) for each website (duplicate category for each website). '
                . 'Otherwise alternate hreflang tags will be defined automatically.';
        } else {
            $notice = 'Powered by Magefan Alternate Hreflang Extension.';
        }

        if (!$this->storeGroup) {
            foreach ($storeManager->getWebsites() as $website) {
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    if (count($stores) == 0) {
                        continue;
                    }
                    foreach ($stores as $store) {
                        $this->storeGroup[$store->getId()]['label'] =
                            $store->getName() . ' (' . $website->getName() . '/' . $group->getName() .')';
                        $this->storeGroup[$store->getId()]['formElement'] = 'input';
                        $this->storeGroup[$store->getId()]['notice'] = $notice;
                        $this->storeGroup[$store->getId()]['disabled'] = false ;
                        foreach ($localization as $k => $field) {
                            $object = $this->factory->create()->load($field);
                            $title = $object->getTitle() ?: $object->getName();
                            $this->storeGroup[$k]['value'] = $field . ". " . $title;
                        }
                    }
                }
            }
        }

        foreach ($this->storeGroup as $k => $fieldConfig) {
            $fieldInstance = $this->fieldFactory->create();
            $name = 'localization[' . $k . ']';

            $fieldInstance->setData(
                [
                        'config' => $fieldConfig,
                        'name' => $name
                        ]
            );

            $fieldInstance->prepare();
            $this->addComponent($name, $fieldInstance);
        }

        return parent::getChildComponents();
    }
}
