<?php declare(strict_types=1);

namespace Smartblinds\System\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class SystemCategory extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    public const EMPTY = null;
    public const ROLLER = 'roller';
    public const DUOROLLER = 'duoroller';
    public const VENETIAN = 'venetian_blinds';
    public const HONEYCOMB = 'honeycomb_blinds';

    public const ROLLER_TITLE = 'Roller';
    public const DUOROLLER_TITLE = 'Duo Roller';
    public const VENETIAN_TITLE = 'Venetian blinds';
    public const HONEYCOMB_TITLE = 'Honeycomb blinds';


    public function getAllOptions($withEmpty = true)
    {
        $result = [];
        foreach (self::getOptionArray() as $index => $value) {
            if (!$withEmpty && !$index) {
                continue;
            }
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    public static function getOptionArray()
    {
        return [
            self::EMPTY => __('Empty'),
            self::ROLLER => self::ROLLER_TITLE,
            self::DUOROLLER => self::DUOROLLER_TITLE,
            self::VENETIAN => self::VENETIAN_TITLE,
            self::HONEYCOMB => self::HONEYCOMB_TITLE,
        ];
    }
}
