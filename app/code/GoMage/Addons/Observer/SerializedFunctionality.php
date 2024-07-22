<?php

namespace GoMage\Addons\Observer;

use GoMage\Addons\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SerializedFunctionality implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param RequestInterface $request
     * @param Data $helper
     */
    public function __construct(
        RequestInterface $request,
        Data $helper
    ) {
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var $product Product */
        $product = $observer->getEvent()->getDataObject();
        $post = $this->request->getPost()['product'];

        $this->helper->setObjectData($product, $post);
    }
}
