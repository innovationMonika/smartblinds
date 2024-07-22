<?php declare(strict_types=1);

namespace Smartblinds\Cms\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Smartblinds\Cms\Block\System\Config\Form\Field\Column\ControlType;
use Smartblinds\Cms\Block\System\Config\Form\Field\Column\CmsBlock;

class NoticeConfig extends AbstractFieldArray
{
    private $controlTypeRenderer;
    private $cmsBlockRenderer;

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('control_type', [
            'label' => __('System Control Type'),
            'renderer' => $this->getSystemControlTypeRenderer()
        ]);
        $this->addColumn('cms_block', [
            'label' => __('Block'),
            'renderer' => $this->getCmsBlockRenderer()
        ]);
        $this->addColumn('label', [
            'label' => __('Label')
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $systemType = $row->getSystemTypeAlternate();
        if ($systemType !== null) {
            $options['option_' . $this->getSystemTypeAlternateRenderer()
                ->calcOptionHash($systemType)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSystemControlTypeRenderer()
    {
        if (!isset($this->controlTypeRenderer)) {
            $this->controlTypeRenderer = $this->getLayout()->createBlock(
                ControlType::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->controlTypeRenderer;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCmsBlockRenderer()
    {
        if (!isset($this->cmsBlockRenderer)) {
            $this->cmsBlockRenderer = $this->getLayout()->createBlock(
                CmsBlock::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->cmsBlockRenderer;
    }
}
