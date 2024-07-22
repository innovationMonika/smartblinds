<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Console\Command;

use Smartblinds\Catalog\Model\ResourceModel\RelativeData\Updater\UpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRelativeData extends Command
{
    private UpdaterInterface $updater;

    public function __construct(
        UpdaterInterface $updater,
        string $name = null
    ) {
        $this->updater = $updater;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('smartblinds:catalog:update-relative-data');
        $this->setDescription('Updates relative data');
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->updater->update();
        return 0;
    }
}
