<?php

namespace GoMage\Breadcrumbs\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class RewriteUrlHome implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    public $moduleDataSetup;
    protected \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory $urlRewriteFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory $urlRewriteFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->urlRewriteFactory = $urlRewriteFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            /*SomeDependency::class*/
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /**
         * @var $urlRewriteModel \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection
         */
        $urlRewriteModel = $this->urlRewriteFactory->create();
        $urlRewriteModel->addFieldToFilter('request_path', 'home');
        if($urlRewriteModel->count() > 0){
            foreach ($urlRewriteModel as $item) {
                $item->setTargetPath(" ");
                $item->setRedirectType("301");
                $item->setEntityId(0);
                $item->setEntityType('');
                $item->save();
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
