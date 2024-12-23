<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Escaper;
use Symfony\Component\Console\Helper\ProgressBar;
use Magento\Framework\App\State;
use Magefan\WebP\Model\Filesystem\PubFolder;
use Magento\Framework\App\Area;
use Magefan\WebP\Model\Config\Source\CreationOptions;
use Magefan\WebP\Model\Config;
use Magento\Framework\Exception\LocalizedException;

/**
 * Convert to webp using CLI
 */
class Convert extends Command
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\App\State
     **/
    private $state;

    /**
     * @var PubFolder
     */
    private $pubFolder;

    /**
     * Convert constructor.
     * @param PubFolder $pubFolder
     * @param Escaper $escaper
     * @param State $state
     * @param Config $config
     * @param null $name
     */
    public function __construct(
        PubFolder $pubFolder,
        Escaper $escaper,
        State $state,
        Config $config,
        $name = null
    ) {
        $name = $name ?: 'magefan:webp:convert';
        parent::__construct($name);
        $this->pubFolder = $pubFolder;
        $this->escaper = $escaper;
        $this->state = $state;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                'path',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path'
            )
        ];

        $this->setDescription(__('Convert images to webP by path (path to folder)'))
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
        } catch (LocalizedException $e) {
            $output->writeln((string)__('Something went wrong. %1', $this->escaper->escapeHtml($e->getMessage())));
        }

        $errors = $this->checkEnvironment();

        if ($errors) {
            foreach ($errors as $line) {
                $output->writeln((string)$line);
            }
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        try {
            if ($path = $input->getOption('path')) {
                $output->writeln('<info>' . __('Provided path is %1', '`' . $path . '`') . '</info>');
            } else {
                $path = '';
            }

            $images = $this->pubFolder->getFilesFromFolder($path);

            $progressBar = new ProgressBar($output, count($images));

            $output->writeln((string)__('Converting images in folder %1', $this->pubFolder->getPath($path)));
            $progressBar->start();
            $progressBar->display();

            foreach ($images as $key => $value) {
                $this->pubFolder->convertFiles([$value], CreationOptions::MANUAL);
                $progressBar->advance();
            }

            $progressBar->finish();
            $output->writeln("\n");
            $output->writeln((string)__('Images have been converted successful.'));

            $output->writeln('');

        } catch (LocalizedException $e) {
            $output->writeln((string)__('Something went wrong. %1', $this->escaper->escapeHtml($e->getMessage())));
        }
    }

    /**
     * Checks that application is installed and DI resources are cleared
     *
     * @return string[]
     */
    private function checkEnvironment(): array
    {
        $messages = [];
        if (!$this->config->isEnabled()) {
            $messages[] = __('Magefan WebP extension is disabled. Please enable the module in '
                . '"Stores > Configuration > Magefan Extensions > WebP Optimized Images" to use this command.');
        }

        return $messages;
    }
}
