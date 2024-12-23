<?php
declare(strict_types=1);

namespace Smartblinds\Catalog\Plugin\Product;

use Magmodules\RichSnippets\Model\Product\Repository as RichSnippetsRepository;

class RepositoryPlugin
{
    /**
     * @param RichSnippetsRepository $subject
     * @param array $offers
     * @return array
     */
    public function afterGetOffers(RichSnippetsRepository $subject, array $offers): array
    {
        $customAttributeValue = "call plugin";
        if ($customAttributeValue) {
            $offers['customAttribute'] = $customAttributeValue;
        }

        // Add any other desired changes

        return $offers;
    }
}