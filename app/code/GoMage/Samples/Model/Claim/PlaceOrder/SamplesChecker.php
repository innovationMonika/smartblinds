<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder;

class SamplesChecker
{
    private array $quotes = [];
    private array $orders = [];

    private bool $isCreatingSamplesQuote = false;

    public function hasSamplesEntities(): bool
    {
        return count($this->quotes) || count($this->orders);
    }

    public function isSamplesQuote($quoteId): bool
    {
        return $this->quotes[$quoteId] ?? false;
    }

    public function setSamplesQuote($quoteId)
    {
        $this->quotes[$quoteId] = true;
    }

    public function isSamplesOrder($incrementId): bool
    {
        return $this->orders[$incrementId] ?? false;
    }

    public function setSamplesOrder($incrementId)
    {
        $this->orders[$incrementId] = true;
    }

    public function isCreatingSamplesQuote(): bool
    {
        return $this->isCreatingSamplesQuote;
    }

    public function setIsCreatingSamplesQuote(bool $value) {
        $this->isCreatingSamplesQuote = $value;
    }
}
