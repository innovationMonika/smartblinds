<?php
namespace GoMage\Ec\Helper;

class Data extends \Anowave\Ec\Helper\Data
{
    /**
     * Get enhanced conversions variable
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return string
     */
    public function getEnhancedConversionVariable(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $variable = [];

        if ($this->supportEnhancedConversions()) {
            try {
                $shippingAddress = $order->getShippingAddress();
                if ($shippingAddress) {
                    $street = (is_array($shippingAddress->getStreet()))
                        ? implode(" ", $shippingAddress->getStreet())
                        : $shippingAddress->getStreet();
                    $variable['email']         = $shippingAddress->getEmail();
                    $variable['phone_number']  = $shippingAddress->getTelephone() ?? '';
                    $variable['first_name']    = $shippingAddress->getFirstname();
                    $variable['last_name']     = $shippingAddress->getLastname();
                    $variable['street']        = trim($street ?? '');
                    $variable['city']          = $shippingAddress->getCity() ?? '';
                    $variable['region']        = $shippingAddress->getRegionCode() ?? '';
                    $variable['country']       = $shippingAddress->getCountryId() ?? '';
                    $variable['postal_code']   = $shippingAddress->getPostcode() ?? '';
                }
            } catch (\Exception $e) {
                $variable['error'] = __('Shipping address cannot be found');
            }
        }

        return $this->getJsonHelper()->encode($variable);
    }
}
