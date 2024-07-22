<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\FeeFormInput;

interface FeeFormInputRenderInterface
{
    const VISIBLE_TYPE   = 1;
    const INVISIBLE_TYPE = 0;

    /**
     * Render form input component for the fee
     *
     * @return array
     */
    public function render(): array;
}
