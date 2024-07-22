<?php

namespace GoMage\Samples\Plugin\Sales\Block\Adminhtml\Order\View\Info;

use GoMage\Samples\Model\Config;
use Magento\Framework\Escaper;
use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Magento\Sales\Model\Order\Address;

class AddSamplesFormId
{
    private Config $config;
    private Escaper $escaper;

    public function __construct(
        Config $config,
        Escaper $escaper
    ) {
        $this->config = $config;
        $this->escaper = $escaper;
    }

    public function afterGetFormattedAddress(
        Info $subject,
        $result,
        Address $address
    ) {
        $isShipping = $address->getAddressType() == 'shipping';
        $isSamples = $address->getOrder()->getStatus() == $this->config->getOrderStatus();
        $samplesFormId = $address->getData('samples_form_id');
        if ($isSamples && $isShipping && $samplesFormId) {
            $result .= '<br>Samples Form ID: ' .
                $this->escaper->escapeHtml($samplesFormId);
        }
        return $result;
    }
}
