<?php declare(strict_types=1);

namespace GoMage\MagefanBlog\Model;

use Magefan\Blog\Model\Config as MagefanConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const DEFAULT_PAGE_SIZE = 5;

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isAutoRelatedPosts(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'mfblog/post_view/related_posts/auto',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getPageSize()
    {
        $pageSize = (int) $this->scopeConfig->getValue(
            MagefanConfig::XML_RELATED_POSTS_NUMBER,
            ScopeInterface::SCOPE_STORE
        );
        return $pageSize ?: self::DEFAULT_PAGE_SIZE;
    }
}
