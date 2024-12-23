<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\DiffFinder;

use cogpowered\FineDiff\Diff;
use Magento\Framework\ObjectManagerInterface;

class FineDiffAdapter implements DiffFinderAdapterInterface
{
    /**
     * @var Diff|null
     */
    private $diffFinder;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        //ToDo: Remove after updating library "cogpowered/finediff"
        $currentErrorLevel = error_reporting();
        error_reporting($currentErrorLevel & ~E_DEPRECATED);
        if (class_exists(Diff::class)) {
            $this->diffFinder = $objectManager->create(Diff::class);
        }
        error_reporting($currentErrorLevel);
    }

    public function render(string $fromText, string $toText): string
    {
        if ($this->diffFinder === null) {
            throw new \RuntimeException(
                '\'cogpowered/finediff\' library not found. '
                . 'Please run \'composer require cogpowered/finediff\' command to install it.'
            );
        }

        return $this->diffFinder->render($fromText, $toText);
    }
}
