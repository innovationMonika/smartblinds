<?php declare(strict_types=1);

namespace GoMage\BaseSuggestions\ViewModel;

use GoMage\BaseSuggestions\Model\Data\Suggestion;
use GoMage\BaseSuggestions\Model\Data\SuggestionFactory;
use GoMage\BaseSuggestions\Model\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BaseSuggestions implements ArgumentInterface
{
    private Config $config;
    private SuggestionFactory $suggestionFactory;

    public function __construct(
        Config $config,
        SuggestionFactory $suggestionFactory
    ) {
        $this->config = $config;
        $this->suggestionFactory = $suggestionFactory;
    }

    /**
     * @return Suggestion[]
     */
    public function getAll(): array
    {
        $suggestions = [];
        foreach ($this->config->getBaseSuggestions() as $suggestionData) {
            $suggestions[] = $this->suggestionFactory->create(['data' => $suggestionData]);
        }
        return $suggestions;
    }
}
