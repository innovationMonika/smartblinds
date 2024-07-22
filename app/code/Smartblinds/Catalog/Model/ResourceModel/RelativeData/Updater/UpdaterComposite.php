<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Model\ResourceModel\RelativeData\Updater;

class UpdaterComposite implements UpdaterInterface
{
    /** @var UpdaterInterface[]  */
    private array $updaters;

    public function __construct(array $updaters = [])
    {
        $this->updaters = $updaters;
    }

    public function update()
    {
        foreach ($this->updaters as $updater) {
            $updater->update();
        }
    }
}
