<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

declare(strict_types=1);

namespace Amasty\InstagramFeed\Model\Instagram\Operation;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Amasty\InstagramFeed\Model\PostFactory;

class CreatePostFromData
{
    /**
     * @var PostFactory
     */
    private $postFactory;

    public function __construct(PostFactory $postFactory)
    {
        $this->postFactory = $postFactory;
    }

    /**
     * @param array $data
     * @return PostInterface[]
     */
    public function execute(array $data)
    {
        $items = [];

        foreach ($data as $post) {
            $items[] = $this->postFactory->create(['data' => $post]);
        }

        return $items;
    }
}
