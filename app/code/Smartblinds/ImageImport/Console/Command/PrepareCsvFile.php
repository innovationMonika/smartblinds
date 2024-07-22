<?php declare(strict_types=1);

namespace Smartblinds\ImageImport\Console\Command;

use Smartblinds\ImageImport\Model\Csv\Preparer\PreparerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepareCsvFile extends Command
{
    private PreparerInterface $preparer;
    private $name;
    private $description;

    public function __construct(
        PreparerInterface $preparer,
        string $name = null,
        string $description = null
    ) {
        $this->preparer = $preparer;
        $this->name = $name;
        $this->description = $description;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription($this->description);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->preparer->prepare();
        $output->writeln($this->preparer->getCsvUrl());
        $failedImages = $this->preparer->getFailedImages();
        if ($failedImages) {
            $output->writeln('Failed Images');
            foreach ($failedImages as $failedImage) {
                $output->writeln($failedImage);
            }
        }
        return 0;
    }
}
