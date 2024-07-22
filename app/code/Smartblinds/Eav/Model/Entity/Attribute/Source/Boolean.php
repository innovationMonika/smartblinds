<?php declare(strict_types=1);

namespace Smartblinds\Eav\Model\Entity\Attribute\Source;

class Boolean extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{
    public function getAllOptions()
    {
        return array_reverse(parent::getAllOptions());
    }
}
