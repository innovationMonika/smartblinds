<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmodules\Schema\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magmodules\RichSnippets\Api\Config\RepositoryInterface as RichSnippetsConfigRepository;
use Magmodules\RichSnippets\Api\Log\RepositoryInterface as LogRepository;
use Magmodules\RichSnippets\Api\Product\RepositoryInterface as ProductRepositoryInterface;
use Magmodules\RichSnippets\Service\Product\AggregateRating;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Catalog\Helper\Data as TaxHelper;
use Magento\Customer\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Product provider class
 */
class Repository extends \Magmodules\RichSnippets\Model\Product\Repository
{


    /**
     * Get country path
     */
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var RichSnippetsConfigRepository
     */
    private $config;
    /**
     * @var StockItem
     */
    private $stockItem;
    /**
     * @var ReviewCollectionFactory
     */
    private $reviewCollection;
    /**
     * @var AggregateRating
     */
    private $aggregateRating;
    /**
     * @var Image
     */
    private $imgHelper;
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var DateTime
     */
    private $date;
    /**
     * @var FilterManager
     */
    private $filterManager;
    /**
     * @var LogRepository
     */
    private $logger;
    /**
     * @var PriceHelper
     */
    private $priceHelper;
    /**
     * @var TaxHelper
     */
    private $taxHelper;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var MagentoProductRepository
     */
    private $magentoProductRepository;
    /**
     * @var null
     */
    private $fullChildProduct = null;

        /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

     /**
     * @var StoreManagerInterface
     */
    private $storeManager;


    /**
     * Repository constructor.
     *
     * @param RichSnippetsConfigRepository $config
     * @param StockItem $stockItem
     * @param Image $imgHelper
     * @param ReviewCollectionFactory $reviewCollection
     * @param AggregateRating $aggregateRating
     * @param UrlInterface $url
     * @param DateTime $date
     * @param FilterManager $filterManager
     * @param LogRepository $logger
     * @param PriceHelper $priceHelper
     * @param TaxHelper $taxHelper
     * @param Session $customerSession
     * @param MagentoProductRepository $magentoProductRepository
     */
    public function __construct(
        RichSnippetsConfigRepository $config,
        StockItem $stockItem,
        Image $imgHelper,
        ReviewCollectionFactory $reviewCollection,
        AggregateRating $aggregateRating,
        UrlInterface $url,
        DateTime $date,
        FilterManager $filterManager,
        LogRepository $logger,
        PriceHelper $priceHelper,
        TaxHelper $taxHelper,
        Session $customerSession,
        MagentoProductRepository $magentoProductRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->stockItem = $stockItem;
        $this->reviewCollection = $reviewCollection;
        $this->aggregateRating = $aggregateRating;
        $this->imgHelper = $imgHelper;
        $this->url = $url;
        $this->date = $date;
        $this->filterManager = $filterManager;
        $this->logger = $logger;
        $this->priceHelper = $priceHelper;
        $this->taxHelper = $taxHelper;
        $this->customerSession = $customerSession;
        $this->magentoProductRepository = $magentoProductRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

        parent::__construct(
        $config,
        $stockItem,
        $imgHelper,
        $reviewCollection,
        $aggregateRating,
        $url,
        $date,
        $filterManager,
        $logger,
        $priceHelper,
        $taxHelper,
        $customerSession,
        $magentoProductRepository
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getSchemaData($product): array
    {
        if (!$this->config->getProductEnabled()) {
            return [];
        }

        try {
            return $this->getProductSchema($product);
        } catch (\Exception $exception) {
            $this->logger->addErrorLog('Exception', $exception->getMessage());
            return [];
        }
    }

    /**
     * Get Product Schema data
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getProductSchema($product): array
    {
        $productSnippets = [];
        $children = [];
        $parentAttributes = [];
        switch ($product->getTypeId()) {
            case 'configurable':
                if ($this->config->getProductMultiConfigurable()) {
                    $children = $product->getTypeInstance()->getUsedProducts($product);
                    $parentAttributes = $this->config->getAttributesFromParentConfigurable();
                }
                break;
            case 'bundle':
                if ($this->config->getProductMultiBundle()) {
                    $children = $product->getTypeInstance()
                        ->getSelectionsCollection($product->getTypeInstance()->getOptionsIds($product), $product)
                        ->getItems();
                    $parentAttributes = $this->config->getAttributesFromParentBundle();
                }
                break;
            case 'grouped':
                if ($this->config->getProductMultiGrouped()) {
                    $children = $product->getTypeInstance()->getAssociatedProducts($product);
                    $parentAttributes = $this->config->getAttributesFromParentGrouped();
                }
                break;
        }

        if (empty($children)) {
            return [$this->getProductSchemaData($product)];
        }

        /* @var \Magento\Catalog\Model\Product $child */
        foreach ($children as $child) {
            if ($child->getStatus() == 1) {
                $productSnippets[] = $this->getProductSchemaData($child, $product, $parentAttributes);
            }
        }
        return $productSnippets;
    }

    /**
     * Get Product Schema data
     *
     * @param ProductInterface $product
     * @param ProductInterface|null $parent
     * @param array $parentAttributes
     *
     * @return array
     */
    private function getProductSchemaData($product, $parent = null, $parentAttributes = []): array
    {
        $productSnippets = [
            '@context' => 'http://schema.org',
            '@type' => 'Product'
        ];

        //get name
        if (in_array('title', $parentAttributes)) {
            $title = $parent->getName();
        } else {
            $title = $product->getName();
        }
        if ($title) {
            $productSnippets['name'] = $title;
        }

        //get description
        if (in_array('description', $parentAttributes)) {
            $description = $this->getDescription($parent);
        } else {
            $description = $this->getDescription($product);
        }
        if ($description) {
            $productSnippets['description'] = $description;
        }

        //get brand
        if (in_array('brand', $parentAttributes)) {
            $brand = $this->getBrand($parent);
        } else {
            $brand = $this->getBrand($product);
        }
        if ($brand) {
            $productSnippets['brand'] = [
                '@type' => 'Brand',
                'name' =>  $brand
            ];
        }

        if ($img = $this->getImage($product, $parent)) {
            $productSnippets['image'] = $img;
        }

        if ($offers = $this->getOffers($product)) {
            $productSnippets += $offers;
        }

        if ($aggregatedRating = $this->aggregateRating->execute($product)) {
            $productSnippets += $aggregatedRating;
        }

        if ($lastReview = $this->getLastReview($product, $parent)) {
            $productSnippets += $lastReview;
        }

        if ($extraFields = $this->getExtraFields($product, $parent, $parentAttributes)) {
            $productSnippets += $extraFields;
        }

        return $productSnippets;
    }

    /**
     * @inheritDoc
     */
    public function getDescription($product): string
    {
        $attribute = $this->config->getDescriptionAttribute($this->config->getStoreId());
        return $this->filterManager->stripTags((string)$this->getAttributeValue($product, $attribute));
    }

    /**
     * @param Product $product
     * @return string
     */
    private function getBrand(Product $product): string
    {
        $attribute = $this->config->getBrandAttribute($this->config->getStoreId());
        return $this->filterManager->stripTags((string)$this->getAttributeValue($product, $attribute));
    }

    /**
     * Get Attribute value
     *
     * @param Product $product
     * @param string $attribute
     *
     * @return string
     */
    private function getAttributeValue(Product $product, string $attribute): string
    {
        $value = '';
        try {
            /* @var AbstractAttribute $attr */
            if ($attr = $product->getResource()->getAttribute($attribute)) {
                $value = $product->getData($attribute);
                if ($attr->usesSource()) {
                    $value = $attr->getSource()->getOptionText($value);
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->addErrorLog('LocalizedException', $exception->getMessage());
            return '';
        }

        return (string)$value;
    }

    /**
     * Get Product Image
     *
     * @param Product $product
     * @param Product|null $parent
     *
     * @return string
     */
    private function getImage($product, $parent = null): string
    {
        $img = $this->imgHelper->init($product, 'product_base_image')->getUrl();
        if ($parent && $parent->getImage()) {
            $img = $this->imgHelper->init($parent, 'product_base_image')->getUrl();
        }

        return $img;
    }

    /**
     * Get Product Offer
     *
     * @param Product $product
     * @return array
     */
    private function getOffers($product): array
    {
        $price = $this->getPrice($product);
        if ($price == 0 && $this->config->getProductHideZero()) {
            return [];
        }

        $offers = [
            '@type' => 'Offer',
            'priceCurrency' => $this->config->getCurrencyCode(),
            //'price' => $price,
            'price' => $this->priceHelper->currency($price, false, false),
            'url' => $this->getCurrentUrl(),
            'priceValidUntil' => ($this->getPriceValidUntil($product))
                ? ($this->getPriceValidUntil($product))
                : ($this->date->date('Y-m-d', strtotime(' +100 days')))
        ];
        if ($itemCondition = $this->getItemCondition($product)) {
            $offers['itemCondition'] = $itemCondition;
        }
        if ($availability = $this->getAvailability($product)) {
            $offers['availability'] = $availability;
        }
        if ($sellerName = $this->config->getWebsiteNameValue()) {
            $offers['seller'] = [
                '@type' => 'Organization',
                'name' => $sellerName
            ];
        }

        $offers['shippingDetails'] = $this->getshippingDetails($product);
        $offers['deliveryLeadTime'] = $this->getdeliveryLeadTime( $product);
        $offers['hasMerchantReturnPolicy'] = $this->gethasMerchantReturnPolicy($product);
        return ['offers' => $offers];
    }

    /**
     * Get Product Price
     *
     * @param Product $product
     *
     * @return string
     */
    private function getPrice($product)
    {
        if ($this->config->isUseNonDefaultPriceAttribute()
            && $product->getData($this->config->getNonDefaultPriceAttribute())
        ) {
            $price = $product->getData($this->config->getNonDefaultPriceAttribute());
            if ($this->config->getNonDefaultPriceAttribute() == 'msrp') {
                $price = $this->taxHelper->getTaxPrice($product, $price, true);
            }
        } else {
            try {
                $groupId = $this->customerSession->getCustomerGroupId();
            } catch (\Exception $exception) {
                $groupId = 0;
            }
            $product->setCustomerGroupId($groupId);
            if (in_array($product->getTypeId(), ['grouped', 'configurable', 'bundle'])) {
                /* @var \Magento\Framework\Pricing\Amount\AmountInterface $priceData */
                $priceData = $product->getPriceInfo()
                    ->getPrice('final_price')
                    ->getMinimalPrice();
                $price = $priceData->getValue();
            } else {
                $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
            }
        }

        return number_format((float)$price, 2, '.', '');
    }

    /**
     * Get Current Clean Url
     *
     * @return string
     */
    private function getCurrentUrl(): string
    {
        return preg_replace(
            '/\?.*/',
            '',
            $this->url->getCurrentUrl()
        );
    }

    /**
     * Calculate if price has a valid until
     *
     * @param Product $product
     *
     * @return string|null
     */
    private function getPriceValidUntil($product)
    {
        return ($product->getSpecialPrice() == $product->getFinalPrice())
            ? $product->getSpecialToDate()
            : '';
    }

    /**
     * Get Item Condition of product
     *
     * @param Product $product
     *
     * @return string
     */
    private function getItemCondition($product): string
    {
        switch ($this->config->getProductConditionEnable()) {
            case 1:
                if ($itemCondition = $this->config->getProductConditionDefault()) {
                    return sprintf('http://schema.org/%sCondition', $itemCondition);
                }
                break;
            case 2:
                if ($attribute = $this->config->getProductConditionAttribute()) {
                    $itemCondition = $this->getAttributeValue($product, $attribute);
                    if (!empty($itemCondition)) {
                        return sprintf('http://schema.org/%sCondition', ucfirst($itemCondition));
                    }
                }
                break;
        }
        return '';
    }

    /**
     * Get Product Availability
     *
     * @param Product $product
     *
     * @return string
     */
    private function getAvailability($product): string
    {
        if (!$this->config->getProductStock()) {
            return '';
        }

        if ($product->isAvailable()) {
            return 'http://schema.org/InStock';
        } else {
            return 'http://schema.org/OutOfStock';
        }
    }

    /**
     * Return Last Review Data
     *
     * @param Product $product
     *
     * @return array
     */
    private function getLastReview($product, $parent = null)
    {
        if ($parent != null && ($parentTypeId = $parent->getTypeId())) {
            switch ($parentTypeId) {
                case 'configurable':
                    if ($this->config->getUseReviewsFromParentConfigurable()) {
                        $product = $parent;
                    }
                    break;
                case 'bundle':
                    if ($this->config->getUseReviewsFromParentBundle()) {
                        $product = $parent;
                    }
                    break;
                case 'grouped':
                    if ($this->config->getUseReviewsFromParentGrouped()) {
                        $product = $parent;
                    }
                    break;
            }
        }

        $reviewData = [];
        if (!$this->config->getProductReviewsEnabled()) {
            return $reviewData;
        }

        $lastReview = $this->reviewCollection->create()
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $product->getId())
            ->setPageSize(1)
            ->addRateVotes()
            ->setDateOrder()
            ->getLastItem();

        if (!$lastReview->getData()) {
            return $reviewData;
        } elseif (!$lastReview->getRatingVotes()) {
            return [];
        }

        $totalScore = 0;
        $totalScores = count($lastReview->getRatingVotes());

        foreach ($lastReview->getRatingVotes() as $vote) {
            $totalScore += $vote->getPercent();
        }

        $metric = $this->config->getProductRatingMetric();

        if ($totalScores !== 0) {
            if ($metric == 5) {
                $avgRating = round(($totalScore / $totalScores / 20), 2);
                $bestRating = 5;
            } else {
                $avgRating = round($totalScore / $totalScores);
                $bestRating = 100;
            }
        } else {
            $avgRating = $bestRating = ($metric == 5) ? ($metric) : (100);
        }

        $reviewData = [
            '@type' => 'Review',
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => $avgRating,
                'bestRating' => $bestRating
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $lastReview->getNickname()
            ]
        ];
        return ['review' => $reviewData];
    }

    /**
     * Get Array Data of extra fields set in configuration
     *
     * @param Product $product
     * @param Product|null $parent
     *
     * @return array
     */
    private function getExtraFields($product, $parent = null, $parentAttributes = []): array
    {
        $extraFields = [];
        if (!$attributes = $this->config->getProductAttributesValues()) {
            return $extraFields;
        }

        foreach ($attributes as $attribute) {
            if (in_array($attribute['type'], $parentAttributes)) {
                $value = $this->getAttributeValue($parent, $attribute['attribute']);
            } else {
                $fullProduct = $this->getFullProduct($product->getId());
                $value = $this->getAttributeValue($fullProduct, $attribute['attribute']);
            }

            $label = $attribute['type'];
            if (trim((string)$value)) {
                $extraFields[$label] = trim(strip_tags((string)$value));
            }
        }

        return $extraFields;
    }

    /**
     * Load product with all attributes
     *
     * @param $productId
     * @return ProductInterface|null
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getFullProduct($productId)
    {
        if (!$this->fullChildProduct || ($this->fullChildProduct->getId() != $productId)) {
            $this->fullChildProduct = $this->magentoProductRepository->getById($productId);
        }
        return $this->fullChildProduct;
    }


    private function gethasMerchantReturnPolicy($product){

        $gethasMerchantReturnPolicy = [
            '@type' => 'MerchantReturnPolicy',
            'merchantReturnLink' => 'https://www.smartblinds.nl/algemene-voorwaarden/',
            'applicableCountry' => $this->scopeConfig->getValue(
                self::COUNTRY_CODE_PATH,
                ScopeInterface::SCOPE_WEBSITES
            ),
            'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
            'merchantReturnDays' => 14,
            'description' => 'Klanten kunnen producten binnen de wettelijke termijn van 14 dagen retourneren. Uitzonderingen hierop zijn op-maat gemaakte producten. Bekijk onze voorwaarden voor alle uitzonderingen.',
            'returnMethod'=> 'https://schema.org/ReturnByMail',
            'returnFees'=> 'https://schema.org/FreeReturn',
            'refundType'=> 'FullRefund'
        ];

        return $gethasMerchantReturnPolicy;
    }

    private function getshippingDetails($product){

    $shippingDetails = [
        "@type" =>"OfferShippingDetails",
        "shippingRate" =>[
            "@type" =>  "MonetaryAmount",
            "value" =>  0.00,
            "currency" =>   $this->storeManager->getStore()->getCurrentCurrency()->getCode()
        ],
        "shippingDestination" => [
            "@type" => "DefinedRegion",
            "addressCountry" =>  $this->scopeConfig->getValue(self::COUNTRY_CODE_PATH,ScopeInterface::SCOPE_WEBSITES)
        ],
        "deliveryTime" => [
        "@type" => "ShippingDeliveryTime",
        "handlingTime" => [
          "@type" => "QuantitativeValue",
          "minValue" => 0,
          "maxValue" => 1,
          "unitCode" => "DAY"
        ],
        "transitTime" => [
          "@type" => "QuantitativeValue",
          "minValue" => 1,
          "maxValue" => 5,
          "unitCode" => "DAY"
        ]
      ]
    ];

    return $shippingDetails;
  }

   private function getdeliveryLeadTime($product)
    {
        $minValue = $product->getData('deliveryLeadTimeMin') != '' ? $product->getData('deliveryLeadTimeMin') : 10;
        $maxValue = $product->getData('deliveryLeadTimeMax') != '' ? $product->getData('deliveryLeadTimeMax') : 15;

        $getdeliveryLeadTime = [
            '@type' => 'QuantitativeValue',
            "minValue" => $minValue,
            "maxValue" => $maxValue,
            "unitText"=> "Business Days"
        ];

        return $getdeliveryLeadTime;
    }
}
