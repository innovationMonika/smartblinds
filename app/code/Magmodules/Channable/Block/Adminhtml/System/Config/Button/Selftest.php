<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\Channable\Block\Adminhtml\System\Config\Button;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Selftest button class
 */
class Selftest extends Field
{

    /**
     * @var string
     */
    protected $_template = 'Magmodules_Channable::system/config/button/selftest.phtml';
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Checker constructor.
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->request = $context->getRequest();
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getSelftestUrl()
    {
        return $this->getUrl('channable/selftest/index');
    }

    /**
     * @return mixed
     */
    public function getButtonHtml()
    {
        try {
            return $this->getLayout()
                ->createBlock(Button::class)
                ->setData(['id' => 'button_test', 'label' => __('Run Selftest')])
                ->toHtml();
        } catch (\Exception $e) {
            return false;
        }
    }
}
