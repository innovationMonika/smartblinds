<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Grouped Options for Magento 2
*/

declare(strict_types=1);

namespace Amasty\GroupedOptions\Model;

class FakeKeyGenerator
{
    public const LAST_POSSIBLE_OPTION_ID = (2 << 30) - 1;
    
    public function generate(int $groupId): int
    {
        return self::LAST_POSSIBLE_OPTION_ID - $groupId;
    }
}
