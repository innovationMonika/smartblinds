<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList as DL;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveUnusedProductImages extends Command
{
    const OPTION_DRY_RUN = 'dry-run';

    private DirectoryList $directoryList;
    private File $driverFile;
    private ResourceConnection $resourceConnection;

    public function __construct(
        DirectoryList $directoryList,
        File $driverFile,
        ResourceConnection $resourceConnection,
        string $name = null
    ) {
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('smartblinds:catalog:remove-unused-product-images');
        $this->setDescription('Removes unused product images');
        $this->setDefinition([
            new InputOption(
                self::OPTION_DRY_RUN,
                'd',
                InputOption::VALUE_OPTIONAL,
                '',
                0
            )
        ]);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productImagesPath = $this->directoryList->getPath(DL::MEDIA) . '/catalog/product/';
        $fullPaths = $this->driverFile->readDirectoryRecursively($productImagesPath);
        $processedPaths = [];
        foreach ($fullPaths as $fullPath) {
            if (!is_file($fullPath)) {
                continue;
            }
            $dbPath = str_replace($productImagesPath, '/', $fullPath);
            if (strpos($dbPath, '/cache') === 0) {
                continue;
            }
            $processedPaths[$dbPath] = $fullPath;
        }
        $usedImages = $this->loadUsedProductImages();
        foreach ($processedPaths as $dbPath => $fullPath) {
            if (in_array($dbPath, $usedImages)) {
                continue;
            }
            $output->writeln($dbPath);
            if (!$input->getOption(self::OPTION_DRY_RUN)) {
                $this->driverFile->deleteFile($fullPath);
                $output->writeln('Deleted');
            }
        }
        return 0;
    }

    private function loadUsedProductImages()
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['g' => 'catalog_product_entity_media_gallery'], ['g.value'])
            ->joinInner(
                ['l' => 'catalog_product_entity_media_gallery_value_to_entity'],
                'g.value_id = l.value_id',
                []
            );
        return $connection->fetchCol($select);
    }
}
