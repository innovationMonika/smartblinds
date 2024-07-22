<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmodules\Schema\Plugin\Magmodules\RichSnippets\Model\Poduct;

class Repository
{

    public function aroundGetOffers(
        \Magmodules\RichSnippets\Model\Poduct\Repository $subject,
        \Closure $proceed
    ) {
        $result = $proceed();
        return $result;
    }
}

