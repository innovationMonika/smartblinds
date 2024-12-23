<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

declare(strict_types=1);

namespace Amasty\InstagramFeed\Block\Adminhtml\System\Config;

use Amasty\InstagramFeed\Model\ConfigProvider;
use Amasty\InstagramFeed\Model\Instagram\Management;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class GenerateImages extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_InstagramFeed::system/config/generate_button.phtml';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Management
     */
    private $management;

    public function __construct(
        Management $management,
        ConfigProvider $configProvider,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->management = $management;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => $originalData['button_label'],
                'html_id' => $element->getHtmlId()
            ]
        );

        return $this->_toHtml();
    }

    /**
     * @return bool
     */
    public function getIsDisabled()
    {
        return !$this->configProvider->getAccessToken() || !$this->management->isUpdateAvailable();
    }

    /**
     * @return string
     */
    public function getGenerateUrl()
    {
        return $this->getUrl('aminstagramfeed/post/generate');
    }
}
