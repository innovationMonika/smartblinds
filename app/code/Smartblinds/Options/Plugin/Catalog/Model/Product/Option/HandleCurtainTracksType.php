<?php declare(strict_types=1);

namespace Smartblinds\Options\Plugin\Catalog\Model\Product\Option;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\Factory;
use Smartblinds\Options\Model\Product\Option\Type\CurtainTracksWidth;

class HandleCurtainTracksType
{
    private Factory $optionTypeFactory;

    public function __construct(Factory $optionTypeFactory)
    {
        $this->optionTypeFactory = $optionTypeFactory;
    }

    public function afterGetGroupByType(
        Option $subject,
        $result,
        $type = null
    ) {
        if ($type == CurtainTracksWidth::TYPE_CODE) {
            return CurtainTracksWidth::GROUP_CODE;
        }
        return $result;
    }

    public function aroundGroupFactory(
        Option $subject,
        callable $proceed,
        $type
    ) {
        if ($type == CurtainTracksWidth::TYPE_CODE) {
            return $this->optionTypeFactory->create(CurtainTracksWidth::class);
        }
        return $proceed($type);
    }
}
