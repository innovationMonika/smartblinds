<?php declare(strict_types=1);

namespace Smartblinds\Cms\Block\System\Config\Form\Field\Column;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Smartblinds\Cms\Model\Config\Source\CmsBlocks as SourceCmsBlocks;

class CmsBlock extends Select
{
    /**
     * @var SourceCmsBlocks
     */
    protected $sourceCmsBlocks;

    /**
     * @param SourceCmsBlocks $sourceCmsBlocks
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        SourceCmsBlocks $sourceCmsBlocks,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sourceCmsBlocks = $sourceCmsBlocks;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @param $value
     * @return CmsBlock
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSourceOptions(): array
    {
        return $this->sourceCmsBlocks->toOptionArray();
    }
}
