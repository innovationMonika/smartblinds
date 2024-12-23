<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Plugin\Config\Model\Config\Structure\Element\Dependency;

use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory as FieldFactory;

class FieldFactoryPlugin
{
    public const ARRAY_IDENTIFICATOR = 'am_array:';

    /**
     * @param FieldFactory $subject
     * @param array $arguments
     *
     * @return array
     */
    public function beforeCreate(FieldFactory $subject, array $arguments = [])
    {
        if ($this->isNeedProcess($arguments)) {
            $arguments['fieldData']['value'] = str_replace(
                self::ARRAY_IDENTIFICATOR,
                '',
                $arguments['fieldData']['value']
            );
            $arguments['fieldData']['separator'] = ',';
        }

        return [$arguments];
    }

    /**
     * @param array $arguments
     *
     * @return bool
     */
    protected function isNeedProcess(array $arguments = [])
    {
        return isset($arguments['fieldData']['value'])
            && is_string($arguments['fieldData']['value'])
            && strpos($arguments['fieldData']['value'], self::ARRAY_IDENTIFICATOR) !== false;
    }
}
