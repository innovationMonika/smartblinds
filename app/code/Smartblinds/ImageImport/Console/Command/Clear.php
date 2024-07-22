<?php declare(strict_types=1);

namespace Smartblinds\ImageImport\Console\Command;

use Smartblinds\ImageImport\Model\ResourceModel\Images\DropValues\ExecutorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Clear extends Command
{
    private ExecutorInterface $executor;
    private $name;
    private $description;

    public function __construct(
        ExecutorInterface $executor,
        string $name = null,
        string $description = null
    ) {
        $this->executor = $executor;
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
        $this->executor->execute();
        return 0;
    }
}
