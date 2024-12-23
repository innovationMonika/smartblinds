<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model\AutocompleteData;

use Magefan\AlternateHreflang\Model\AutocompleteData\AbstractAutocompleteBlog;

/**
 * Class BlogPost
 * Provides Data for Autocomplete Ajax Call
 */
class BlogTag extends AbstractAutocompleteBlog
{
    /**
     * @return string
     */
    public function getIdFieldName(): string
    {
        return 'tag_id';
    }

    /**
     * @return string
     */
    public function getFunctionCreateCollectionName(): string
    {
        return 'createTagCollection';
    }
}
