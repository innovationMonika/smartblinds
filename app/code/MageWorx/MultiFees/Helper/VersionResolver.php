<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Helper;

use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\App\Helper\Context;

class VersionResolver extends AbstractHelper
{
    /**
     * @var ProductMetadataInterface $productMetadata
     */
    protected $productMetadata;

    /**
     * @var string|int
     */
    protected $moduleVersion;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * VersionResolver constructor.
     *
     * @param ProductMetadataInterface $productMetadata
     * @param Context $context
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        Context $context,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        JsonHelper $jsonHelper
    ) {
        $this->productMetadata    = $productMetadata;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory        = $readFactory;
        $this->jsonHelper         = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Check module version according to conditions
     *
     * @param string $fromVersion
     * @param string $toVersion
     * @param string $fromOperator
     * @param string $toOperator
     * @param string $moduleName
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function checkModuleVersion(
        string $moduleName,
        string $fromVersion,
        string $toVersion = '',
        string $fromOperator = '>=',
        string $toOperator = '<'
    ) {
        if (empty($this->moduleVersion[$moduleName])) {
            $this->moduleVersion[$moduleName] = $this->getModuleVersion($moduleName);
        }

        $fromCondition = version_compare($this->moduleVersion[$moduleName], $fromVersion, $fromOperator);
        if ($toVersion === '') {
            return $fromCondition;
        }

        return $fromCondition && version_compare($this->moduleVersion[$moduleName], $toVersion, $toOperator);
    }

    /**
     * @param string $moduleName
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getModuleVersion(string $moduleName): string
    {
        $path             = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $directoryRead    = $this->readFactory->create($path);
        $composerJsonData = $directoryRead->readFile('composer.json');
        $data             = $this->jsonHelper->unserialize($composerJsonData);

        if ($data && is_array($data)) {
            return !empty($data['version']) ? (string)$data['version'] : '0';
        }

        return !empty($data->version) ? (string)$data->version : '0';
    }
}
