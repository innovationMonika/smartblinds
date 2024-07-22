<?php
/**
 * Anowave Magento 2 Google Tag Manager Enhanced Ecommerce (UA) Tracking
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Anowave license that is
 * available through the world-wide-web at this URL:
 * https://www.anowave.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category 	Anowave
 * @package 	Anowave_Ec
 * @copyright 	Copyright (c) 2023 Anowave (https://www.anowave.com/)
 * @license  	https://www.anowave.com/license-agreement/
 */

namespace Anowave\Ec\Block;

class Plugin
{
    /**
     * @var string
     */
    const BLOCK = 'Anowave\Ec\Block\Track';
    
	/**
	 * Helper
	 *
	 * @var \Anowave\Ec\Helper\Data
	 */
	protected $helper = null;
	
	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $registry = null;
	
	/**
	 * @var \Magento\Catalog\Api\ProductRepositoryInterface
	 */
	protected $productRepository = null;
	
	/**
	 * @var \Magento\Catalog\Model\CategoryRepository
	 */
	protected $categoryRepository;
	
	/**
	 * @var \Anowave\Ec\Helper\Attributes
	 */
	protected $attributes;
	
	/**
	 * @var \Anowave\Ec\Helper\Bridge
	 */
	protected $bridge;
	
	/**
	 * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
	 */
	protected $stockItemRepository;
	
	/**
	 * @var \Anowave\Ec\Model\SwatchAttributeType
	 */
	protected $swatchTypeChecker;
	
	/**
	 * @var \Anowave\Ec\Model\Apply
	 */
	protected $canApply = false;
	
	/**
	 * @var \Anowave\Ec\Model\Cache
	 */
	protected $cache = null;
	
	/**
	 * @var \Anowave\Ec\Helper\Datalayer
	 */
	protected $dataLayer = null;
	
	/**
	 * @var \Anowave\Ec\Helper\Dom
	 */
	protected $dom;
	
	/**
	 * Constructor 
	 * 
	 * @param \Magento\Framework\Registry $registry
	 * @param \Anowave\Ec\Helper\Data $helper
	 * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
	 * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
	 * @param \Anowave\Ec\Model\Apply $apply
	 * @param \Anowave\Ec\Model\Cache $cache
	 * @param \Anowave\Ec\Helper\Datalayer $dataLayer
	 * @param \Anowave\Ec\Helper\Attributes $attributes
	 * @param \Anowave\Ec\Helper\Bridge $bridge
	 * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
	 * @param \Anowave\Ec\Model\SwatchAttributeType $swatchTypeChecker
	 * @param \Anowave\Ec\Helper\Dom $dom
	 */
	public function __construct
	(
		\Magento\Framework\Registry $registry, 
		\Anowave\Ec\Helper\Data $helper,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Catalog\Model\CategoryRepository $categoryRepository,
		\Anowave\Ec\Model\Apply $apply,
		\Anowave\Ec\Model\Cache $cache,
		\Anowave\Ec\Helper\Datalayer $dataLayer,
		\Anowave\Ec\Helper\Attributes $attributes,
		\Anowave\Ec\Helper\Bridge $bridge,
		\Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
		\Anowave\Ec\Model\SwatchAttributeType $swatchTypeChecker,
	    \Anowave\Ec\Helper\Dom $dom
	) 
	{	
		/**
		 * Set helper 
		 * 
		 * @var \Anowave\Ec\Helper\Data $helper
		 */
		$this->helper = $helper;
		
		
		/**
		 * Set registry 
		 * 
		 * @var \Magento\Framework\Registry $registry
		 */
		$this->registry = $registry;
		
		/**
		 * Set product repository
		 * 
		 * @var unknown
		 */
		$this->productRepository = $productRepository;
		
		/**
		 * Set category repository 
		 * 
		 * @var \Magento\Catalog\Model\CategoryRepository $categoryRepository
		 */
		$this->categoryRepository = $categoryRepository;
		
		/**
		 * Set stock item repository 
		 * 
		 * @var \Anowave\Ec\Block\Plugin $stockItemRepository
		 */
		$this->stockItemRepository = $stockItemRepository;
		
		/**
		 * Set swatch type checker 
		 * 
		 * @var \Anowave\Ec\Model\SwatchAttributeTypee $swatchTypeChecker
		 */
		$this->swatchTypeChecker = $swatchTypeChecker;
		
		/**
		 * Check if tracking should be applied
		 * 
		 * @var Boolean
		 */
		$this->canApply = $apply->canApply
		(
			$this->helper->filter(static::BLOCK)
		);
		
		/**
		 * @var \Anowave\Ec\Model\Cache
		 */
		$this->cache = $cache;
		
		/**
		 * Set dataLayer 
		 * 
		 * @var \Anowave\Ec\Helper\Datalayer
		 */
		$this->dataLayer = $dataLayer;
		
		/**
		 * Set attributes
		 * 
		 * @var \Anowave\Ec\Helper\Attributes $attributes
		 */
		$this->attributes = $attributes;
		
		/**
		 * Set bridge 
		 * 
		 * @var \Anowave\Ec\Helper\Bridge $bridge
		 */
		$this->bridge = $bridge;
		
		/**
		 * Set DOM helper 
		 * 
		 * @var \Anowave\Ec\Helper\Dom $dom
		 */
		$this->dom = $dom;
	}
	
	/**
	 * Block output modifier 
	 * 
	 * @param \Magento\Framework\View\Element\Template $block
	 * @param string $html
	 * 
	 * @return string
	 */
	public function afterToHtml($block, $content) 
	{
		if ($this->helper->isActive() && $this->canApply)
		{
			switch($block->getNameInLayout())
			{
				case 'product.info.addtocart':
				case 'product.info.addtocart.additional':
				case 'product.info.addtocart.bundle':                               return $this->augmentAddCartBlock($block, $content);
				case 'category.products.list': 										return $this->augmentListBlock($block, $content);
				case 'related':
				case 'catalog.product.related':										return $this->augmentListRelatedBlock($block, $content);
				case 'checkout.cart.crosssell':
				case 'catalog.product.crosssell':									return $this->augmentListCrossSellBlock($block, $content);
				case 'product.info.upsell':											return $this->augmentListUpsellBlock($block, $content);
				case 'view.addto.wishlist':	
				case 'product.info.addtowishlist':                                  return $this->augmentWishlistBlock($block, $content);
				case 'wishlist_sidebar':											return $this->augmentWishlistSidebarBlock($block, $content);
				case 'view.addto.compare':											return $this->augmentCompareBlock($block, $content);
				case 'checkout.cart':												return $this->augmentCartBlock($block, $content);
				case 'checkout.root': 												return $this->augmentCheckoutBlock($block, $content);
				case 'product_list_toolbar':                                        return $this->augmentToolbarBlock($block, $content);
				case 'checkout.cart.item.renderers.simple.actions.remove':
				case 'checkout.cart.item.renderers.bundle.actions.remove':
				case 'checkout.cart.item.renderers.virtual.actions.remove':
				case 'checkout.cart.item.renderers.default.actions.remove':
				case 'checkout.cart.item.renderers.grouped.actions.remove':
				case 'checkout.cart.item.renderers.downloadable.actions.remove':
				case 'checkout.cart.item.renderers.configurable.actions.remove':    return $this->augmentRemoveCartBlock($block, $content);
				case 'ec_noscript':													return $this->augmentAmp($block, $content);
					default:
						switch(true) 
						{
							case $block instanceof \Magento\Catalog\Block\Product\Widget\NewWidget: 	return $this->augmentWidgetBlock($block, $content);
							case $block instanceof \Magento\CatalogWidget\Block\Product\ProductsList:	return $this->augmentWidgetListBlock($block, $content);
						}
						break;
			}
		}
		
		return $content;
	}

	/**
	 * Modify checkout output 
	 * 
	 * @param AbstractBlock $block
	 * @param string $content
	 * 
	 * @return string
	 */
	public function augmentCheckoutBlock($block, $content)
	{
		return $content .= $block->getLayout()->createBlock(static::BLOCK)->setTemplate('checkout.phtml')->setData
		(
			[
				'checkout_push' => $this->helper->getCheckoutPush($block, $this->registry)
			]
		)
		->toHtml();
	}
	
	/**
	 * Modify toolbar block 
	 * 
	 * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $block
	 * @param string $content
	 * @return string
	 */
	public function augmentToolbarBlock(\Magento\Catalog\Block\Product\ProductList\Toolbar $block, $content)
	{
	    list($doc, $dom) = $this->getDom();
	    
	    $dom->loadHTML($content);
	    
	    $query = new \DOMXPath($dom);
	    
	    foreach ($query->query('.//a[contains(@class,"modes-mode")]') as $element)
	    {
	        $element->setAttribute('data-event', \Anowave\Ec\Helper\Constants::EVENT_SWITCH_MODE);
	    }
	    
	    return $this->dom->getDOMContent($dom, $doc);
	}
	
	/**
	 * Modify cart output
	 *
	 * @param AbstractBlock $block
	 * @param string $content
	 * 
	 * @return string
	 */
	public function augmentCartBlock($block, $content)
	{
		return $content .= $block->getLayout()->createBlock(static::BLOCK)->setTemplate('cart.phtml')->setData
		(
			[
				'cart_push' => $this->helper->getCartPush($block, $this->registry)
			]
		)
		->toHtml();
	}

	/**
	 * Modify NewProduct widget 
	 * 
	 * @param \Magento\Catalog\Block\Product\Widget\NewWidget $block
	 * @param string $content
	 * @return string
	 */
	public function augmentWidgetBlock(\Magento\Catalog\Block\Product\Widget\NewWidget $block, $content)
	{   
		/**
		 * Load cache
		 *
		 * @var string
		 */
		$cache = $this->cache->load(\Anowave\Ec\Model\Cache::CACHE_LISTING_WIDGET);
		
		if ($cache)
		{
			return $cache;
		}
		
		/**
		 * Retrieve list of impression product(s)
		 *
		 * @var array
		 */
		$products = [];
		
		$collection = $block->getProductCollection();
		
		if (!$collection)
		{
			return $content;
		}
		
		foreach ($collection as $product)
		{
			$products[] = $product;
		}
		
		list($doc, $dom) = $this->getDom();
		
		$dom->loadHTML($content);
		
		$query = new \DOMXPath($dom);
		
		/**
		 * Default starting position
		 * 
		 * @var integer $position
		 */
		$position = 1;
		
		/**
		 * Default category 
		 * 
		 * @var \Magento\Framework\Phrase $category
		 */
		$category = __('New products')->__toString();
		
		/**
		 * Impression push
		 * 
		 * @var array $impressions
		 */
		$impressions = 
		[
			'event' 	=> 'widgetViewNonInteractive',
			'ecommerce' => 
			[
				'currencyCode' 	=> $this->helper->getCurrency(),
				'actionField'	=> 
				[
					'list' => $category
				],
				'impressions' 	=> []
			]
		];
		
		foreach ($query->query($this->helper->getListWidgetSelector()) as $key => $element)
		{
			if (isset($products[$key]))
			{
				foreach ($query->query($this->helper->getListWidgetClickSelector(), $element) as $a)
				{
					$click = $a->getAttribute('onclick');
					
					$this->setAttributes($a, $products[$key],
				    [
				        'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_PRODUCT_CLICK,
				        'data-category'    => $category,
				        'data-list'        => $category,
				        'data-position'    => $position,
				        'data-block'       => $block->getNameInLayout(),
				        'data-widget'      => $block->getCacheKey(),
				        'data-click'       => $click
				    ]);

					/**
					 * Create transport object
					 *
					 * @var \Magento\Framework\DataObject $transport
					 */
					$transport = new \Magento\Framework\DataObject
					(
						[
							'attributes' => $this->attributes->getAttributes()
						]
					);
					
					/**
					 * Notify others
					 */
					$this->helper->getEventManager()->dispatch('ec_get_widget_click_attributes', ['transport' => $transport]);
					
					/**
					 * Get response
					 */
					$attributes = $transport->getAttributes();

					$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
					
					$impressions['ecommerce']['impressions'][] = 
					[
					    'id' 			=> $this->helper->getIdentifier($products[$key]),
						'name' 			=> $products[$key]->getName(),
						'price' 		=> $this->helper->getPrice($products[$key]),
						'category' 		=> $category,
						'list' 			=> $category,
						'brand' 		=> $this->helper->getBrand($products[$key]),
						'quantity' 		=> 1,
						'position' 		=> $position,
						'store' 		=> $this->helper->getStoreName()
					];
				}
				
				/**
				 * Apply direct "Add to cart" tracking for listings
				 */
				if ('' !== $selector = $this->helper->getListWidgetCartCategorySelector())
				{
					if (!in_array($products[$key]->getTypeId(),[\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE]))
					{
						foreach (@$query->query($selector, $element) as $a)
						{
							$click = $a->getAttribute('onclick');
							
							$this->setAttributes($a, $products[$key],
						    [
						        'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_ADD_TO_CART,
						        'data-category'    => $category,
						        'data-list'        => $category,
						        'data-position'    => $position,
						        'data-block'       => $block->getNameInLayout(),
						        'data-widget'      => $block->getCacheKey(),
						        'data-click'       => $click
						    ]);
							
							/**
							 * Create transport object
							 *
							 * @var \Magento\Framework\DataObject $transport
							 */
							$transport = new \Magento\Framework\DataObject
							(
								[
									'attributes' => $this->attributes->getAttributes()
								]
							);
							
							/**
							 * Notify others
							 */
							$this->helper->getEventManager()->dispatch('ec_get_widget_add_list_attributes', ['transport' => $transport]);
							
							/**
							 * Get response
							 */
							$attributes = $transport->getAttributes();
							
							$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
						}
					}
				}
			}
			
			$position++;
		}
		
		$content = $this->dom->getDOMContent($dom, $doc);

		/**
		 * Save cache
		 */
		$this->cache->save($content, \Anowave\Ec\Model\Cache::CACHE_LISTING_WIDGET);
		
		return $content;
	}
	
	/**
	 * Modify Widget List widget
	 *
	 * @param \Magento\Catalog\Block\Product\Widget\NewWidget $block
	 * @param string $content
	 * @return string
	 */
	public function augmentWidgetListBlock(\Magento\CatalogWidget\Block\Product\ProductsList $block, $content)
	{ 
		/**
		 * Load cache
		 *
		 * @var string
		 */
		$cache = $this->cache->load(\Anowave\Ec\Model\Cache::CACHE_LISTING_PRODUCT_WIDGET . $block->getNameInLayout());
		
		if ($cache)
		{
			return $cache;
		}
		
		/**
		 * Retrieve list of impression product(s)
		 *
		 * @var array
		 */
		$products = [];
		
		$collection = $block->getProductCollection();
		
		if (!$collection)
		{
		    $block->setProductCollection($block->createCollection());
		    
		    $collection = $block->getProductCollection();
		    
		    if (!$collection)
		    {
		        return $content;
		    }
		}
		
		foreach ($collection as $product)
		{
			$products[] = $product;
		}
		
		list($doc, $dom) = $this->getDom();
		
		$dom->loadHTML($content);
		
		$query = new \DOMXPath($dom);
		
		/**
		 * Default starting position
		 *
		 * @var integer $position
		 */
		$position = 1;
		
		/**
		 * Default category
		 *
		 * @var \Magento\Framework\Phrase $category
		 */
		$list = __($block->getTitle())->__toString();
		
		if (!$list)
		{
		    $list = __('Homepage Product List');
		}
		
		/**
		 * Impression push
		 *
		 * @var array $impressions
		 */
		$impressions =
		[
			'event' 	=> 'widgetViewNonInteractive',
			'ecommerce' =>
			[
				'currencyCode' 	=> $this->helper->getCurrency(),
				'actionField'	=>
				[
					'list' => $list
				],
				'impressions' 	=> []
			]
		];
		
		foreach ($query->query($this->helper->getListWidgetSelector()) as $key => $element)
		{
			if (isset($products[$key]))
			{
			    /**
			     * Get all product categories
			     */
			    $categories = $this->helper->getCurrentStoreProductCategories($products[$key]);
			    
			    if (!$categories)
			    {
			        if (null !== $root = $this->helper->getStoreRootDefaultCategoryId())
			        {
			            $categories[] = $root;
			        }
			    }
			    
			    if ($categories)
			    {
			        /**
			         * Load last category
			         */
			        
			        $category = $this->categoryRepository->get
			        (
			            end($categories)
		            );
			    }
			    else
			    {
			        $category = null;
			    }
			    
			    if ($category)
			    {
			        $category_name = $this->helper->getCategoryDetailList($products[$key], $category);
			    }
			    else
			    {
			        $category_name = $list;
			    }
			    
				foreach ($query->query($this->helper->getListWidgetClickSelector(), $element) as $a)
				{
					$click = $a->getAttribute('onclick');
					
					$this->setAttributes($a, $products[$key],
				    [
				        'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_PRODUCT_CLICK,
				        'data-category'    => $category_name,
				        'data-list'        => $list,
				        'data-position'    => $position,
				        'data-block'       => $block->getNameInLayout(),
				        'data-widget'      => $block->getCacheKey(),
				        'data-click'       => $click
				    ]);

					/**
					 * Create transport object
					 *
					 * @var \Magento\Framework\DataObject $transport
					 */
					$transport = new \Magento\Framework\DataObject
					(
						[
							'attributes' => $this->attributes->getAttributes()
						]
					);
					
					/**
					 * Notify others
					 */
					$this->helper->getEventManager()->dispatch('ec_get_widget_click_attributes', ['transport' => $transport]);
					
					/**
					 * Get response
					 */
					$attributes = $transport->getAttributes();
					
					$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
					
					$impressions['ecommerce']['impressions'][] =
					[
					    'id' 			=> $this->helper->getIdentifier($products[$key]),
						'name' 			=> $products[$key]->getName(),
						'price' 		=> $this->helper->getPrice($products[$key]),
					    'category' 		=> $category_name,
						'list' 			=> $list,
						'brand' 		=> $this->helper->getBrand($products[$key]),
						'quantity' 		=> 1,
						'position' 		=> $position,
						'store' 		=> $this->helper->getStoreName()
					];
				}
				
				/**
				 * Apply direct "Add to cart" tracking for listings
				 */
				if ('' !== $selector = $this->helper->getListWidgetCartCategorySelector())
				{
					if (!in_array($products[$key]->getTypeId(),[\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE]))
					{    
						foreach (@$query->query($selector, $element) as $a)
						{
							$click = $a->getAttribute('onclick');
							
							$this->setAttributes($a, $products[$key],
						    [
						        'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_ADD_TO_CART,
						        'data-category'    => $category_name,
						        'data-list'        => $list,
						        'data-position'    => $position,
						        'data-block'       => $block->getNameInLayout(),
						        'data-widget'      => $block->getCacheKey(),
						        'data-click'       => $click
						    ]);
							
							/**
							 * Create transport object
							 *
							 * @var \Magento\Framework\DataObject $transport
							 */
							$transport = new \Magento\Framework\DataObject
							(
								[
									'attributes' => $this->attributes->getAttributes()
								]
							);
							
							/**
							 * Notify others
							 */
							$this->helper->getEventManager()->dispatch('ec_get_widget_add_list_attributes', ['transport' => $transport]);
							
							/**
							 * Get response
							 */
							$attributes = $transport->getAttributes();
							
							$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
						}
					}
				}
			}
			
			$position++;
		}
		
		$content = $this->dom->getDOMContent($dom, $doc);
		
		/**
		 * Save cache
		 */
		$this->cache->save($content, \Anowave\Ec\Model\Cache::CACHE_LISTING_PRODUCT_WIDGET . $block->getNameInLayout());
		
		return $content;
	}
	
	/**
	 * Modify categories listing output
	 *
	 * @param AbstractBlock $block
	 * @param string $content
	 */
	public function augmentListBlock($block, $content)
	{	
		/**
		 * Load cache
		 * 
		 * @var string
		 */
		$cache = $this->cache->load(\Anowave\Ec\Model\Cache::CACHE_LISTING . $block->getNameInLayout());

		if ($cache)
		{
			return $cache;
		}
		
		/**
		 * Retrieve list of impression product(s)
		 * 
		 * @var array
		 */
		$products = [];
		
		foreach ($block->getLoadedProductCollection() as $product)
		{
			$products[] = $product;
		}
		
		if ($this->helper->usePlaceholders())
		{
			$placeholders = $this->applyPlaceholders($content);
		}
		
		/**
		 * Append tracking
		 */
		list($doc, $dom) = $this->getDom();
		
		$dom->loadHTML($content);

		$query = new \DOMXPath($dom);
		
		/**
		 * Set default position 
		 * 
		 * @var integer $position
		 */
		$position = 1;
		
		try 
		{
		    /**
		     * Get page size 
		     * 
		     * @var int $size
		     */
		    $size = $block->getLoadedProductCollection()->getPageSize();
		    
		    /**
		     * Get current page
		     * 
		     * @var int $page
		     */
		    $page = $block->getLoadedProductCollection()->getCurPage();
		    
		    if ($size && $page)
		    {
		        if ($page > 1)
		        {
		            $position = 1 + (($page - 1) * $size);
		        }
		    }
		    else 
		    {
		        throw new \Exception('Fallback to default pagination');
		    }
		}
		catch (\Exception $e)
		{
		    $page = (int) $this->helper->getRequest()->getParam('p');
		    
		    if ($page)
		    {
		        $size = (int) $this->helper->getConfig('catalog/frontend/grid_per_page');
		        
		        if ($size && $page)
		        {
		            if ($page > 1)
		            {
		                $position = 1 + (($page - 1) * $size);
		            }
		        }
		    }
		    else 
		    {
		        $position = 1;
		    }
		    
		    $position = 1;
		}

		foreach ($query->query($this->helper->getListSelector()) as $key => $element)
		{
			if (isset($products[$key]))
			{
				/**
				 * Get current category
				 *  
				 * @var object
				 */
				$category = $this->registry->registry('current_category');
				
				if (!$category)
				{
				    $categories = (array) $products[$key]->getCategoryIds();
				    
				    if (!$categories)
				    {
				        $categories[] = $this->helper->getStoreRootDefaultCategoryId();
				    }
				    
				    if ($categories)
				    {
				        $category = $this->categoryRepository->get
				        (
				            end($categories)
			            );
				    }
				}

				/**
				 * Add data-* attributes used for tracking dynamic values
				 */
				foreach ($query->query($this->helper->getListClickSelector(), $element) as $a)
				{
					$click = $a->getAttribute('onclick');
						
					$this->setAttributes($a, $products[$key], 
					[
					    'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_PRODUCT_CLICK,
					    'data-category'    => $this->helper->getCategory($category),
					    'data-list'        => $this->helper->getCategoryList($category),
					    'data-position'    => $position,
					    'data-click'       => $click
					]);
					
					/**
					 * Create transport object
					 *
					 * @var \Magento\Framework\DataObject $transport
					 */
					$transport = new \Magento\Framework\DataObject
					(
						[
							'attributes' => $this->attributes->getAttributes()
						]
					);
					
					/**
					 * Notify others
					 */
					$this->helper->getEventManager()->dispatch('ec_get_click_attributes', ['transport' => $transport]);
					
					/**
					 * Get response
					 */
					$attributes = $transport->getAttributes();
					
					$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
				}
				
				/**
				 * Create transport object
				 *
				 * @var \Magento\Framework\DataObject $transport
				 */
				$transport = new \Magento\Framework\DataObject
				(
				    [
				        'attributes' =>
				        [
				            'id'     => $this->helper->getIdentifier($products[$key]),
				            'name'   => $products[$key]->getName(),
				            'price'  => $this->helper->getPrice
				            (
				                $products[$key]
			                )
				        ],
				        'product' => $products[$key]
				    ]
			    );
				
				/**
				 * Notify others
				 */
				$this->helper->getEventManager()->dispatch('ec_get_add_wishlist_attributes', ['transport' => $transport]);
				
				
				/**
				 * Apply direct "Add to Wishlist" tracking for listings
				 */
				foreach ($query->query($this->helper->getListWishlistSelector(), $element) as $a)
				{
				    $this->setAttributes($a, $products[$key],
			        [
			            'data-event'             => \Anowave\Ec\Helper\Constants::EVENT_ADD_TO_WISHLIST,
			            'data-event-attributes'  => $this->helper->getJsonHelper()->encode($transport->getAttributes(), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS),
			            'data-category'          => $this->helper->getCategory($category),
			            'data-list'              => $this->helper->getCategoryList($category),
			            'data-position'          => $position
			        ]);
				}
				
				/**
				 * Apply direct "Add to Compare" tracking for listings
				 */
				foreach ($query->query($this->helper->getListCompareSelector(), $element) as $a)
				{
				    $this->setAttributes($a, $products[$key],
				        [
				            'data-event'             => \Anowave\Ec\Helper\Constants::EVENT_ADD_TO_COMPARE,
				            'data-event-attributes'  => $this->helper->getJsonHelper()->encode($transport->getAttributes(), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS),
				            'data-category'          => $this->helper->getCategory($category),
				            'data-list'              => $this->helper->getCategoryList($category),
				            'data-position'          => $position
				        ]);
				}
				
				/**
				 * Apply direct "Add to cart" tracking for listings
				 */
				if ('' !== $selector = $this->helper->getCartCategorySelector())
				{
					/**
					 * Skip tracking for configurable and grouped products from listings
					 */
					if (!in_array($products[$key]->getTypeId(), 
					[
						\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
					]))
					{
						foreach (@$query->query($selector, $element) as $a)
						{
							$click = $a->getAttribute('onclick');
						
							$this->setAttributes($a, $products[$key],
						    [
						        'data-category'    => $this->helper->getCategory($category),
						        'data-list'        => $this->helper->getCategoryList($category),
						        'data-position'    => $position,
						        'data-click'       => $click
						    ]);

							if (\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE === $products[$key]->getTypeId())
							{
								try 
								{
									/**
									 * Presume swatch only by default 
									 * 
									 * @var bool $swatch
									 */
									$swatch = true; 
									
									$swatch_attributes = [];
									
									$configurableAttributes = $products[$key]->getTypeInstance()->getConfigurableAttributes($products[$key]);
									
									foreach ($configurableAttributes as $attribute)
									{
										if (!$this->swatchTypeChecker->isSwatchAttribute($attribute->getProductAttribute()))
										{
											$swatch = false;
										}
										else
										{
											$swatch_attributes[] = 
											[
												'attribute_id' 		=> $attribute->getProductAttribute()->getAttributeId(),
												'attribute_code' 	=> $attribute->getProductAttribute()->getAttributeCode(),
												'attribute_label' 	=> $this->helper->useDefaultValues() ? $attribute->getProductAttribute()->getFrontendLabel() : $attribute->getProductAttribute()->getStoreLabel()
											];
										}
									}
									
									if ($swatch)
									{
									    $a->setAttribute('data-event', \Anowave\Ec\Helper\Constants::EVENT_ADD_TO_CART_SWATCH);
										
										/**
										 * Product has swatch attributes only
										 */
										$a->setAttribute('data-swatch', $this->helper->getJsonHelper()->encode($swatch_attributes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
									}
									
								}
								catch (\Exception $e){}
							}
							else 
							{
							    $a->setAttribute('data-event',\Anowave\Ec\Helper\Constants::EVENT_ADD_TO_CART);
							}
							
							/**
							 * Create transport object
							 *
							 * @var \Magento\Framework\DataObject $transport
							 */
							$transport = new \Magento\Framework\DataObject
							(
								[
									'attributes' => $this->attributes->getAttributes()
								]
							);
							
							/**
							 * Notify others
							 */
							$this->helper->getEventManager()->dispatch('ec_get_add_list_attributes', ['transport' => $transport]);
							
							/**
							 * Get response
							 */
							$attributes = $transport->getAttributes();
							
							$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
						}
					}
				}
			}
			
			$position++;
		}
		
		$content = $this->dom->getDOMContent($dom, $doc);
		
		if ($this->helper->usePlaceholders())
		{
			if (isset($placeholders))
			{
				$content = $this->restorePlaceholders($content, $placeholders);
			}
		}
		
		/**
		 * Save cache
		 */
		$this->cache->save($content, \Anowave\Ec\Model\Cache::CACHE_LISTING . $block->getNameInLayout());
		
		return $content;
	}
	
	/**
	 * Augment cross sells
	 *
	 * @param unknown $block
	 * @param unknown $content
	 * @return string|unknown
	 */
	public function augmentListCrossSellBlock($block, $content)
	{
		/**
		 * Remove empty spaces
		 */
		$content = trim($content);
		
		if (!strlen($content))
		{
			return $content;
		}
		
		/**
		 * Load cache
		 *
		 * @var string
		 */
		$cache = $this->cache->load(\Anowave\Ec\Model\Cache::CACHE_LISTING . $block->getNameInLayout());
		
		if ($cache)
		{
			return $cache;
		}
		
		/**
		 * Retrieve list of impression product(s)
		 *
		 * @var array
		 */
		$products = [];
		
		if ($block->getItems())
		{
    		foreach ($block->getItems() as $product)
    		{
    			$products[] = $product;
    		}
		}
		
		/**
		 * Append tracking
		 */
		list($doc, $dom) = $this->getDom();
		
		$dom->loadHTML($content);
		
		$query = new \DOMXPath($dom);
		
		$position = 1;
		
		foreach ($query->query($this->helper->getListCrossSellSelector()) as $key => $element)
		{
			if (isset($products[$key]))
			{
				/**
				 * Get all product categories
				 */
				$categories = $this->helper->getCurrentStoreProductCategories($products[$key]);
				
				if (!$categories)
				{
					if (null !== $root = $this->helper->getStoreRootDefaultCategoryId())
					{
						$categories[] = $root;
					}
				}
				
				if ($categories)
				{
					/**
					 * Load last category
					 */
					
					$category = $this->categoryRepository->get
					(
						end($categories)
					);
				}
				else
				{
					$category = null;
				}
				
				/**
				 * Add data-* attributes used for tracking dynamic values
				 */
				foreach ($query->query($this->helper->getListClickSelector(), $element) as $a)
				{
					$click = $a->getAttribute('onclick');
					
					$this->setAttributes($a, $products[$key],
				    [
				        'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_PRODUCT_CLICK,
				        'data-list'        => \Anowave\Ec\Helper\Constants::LIST_CROSS_SELL,
				        'data-position'    => $position,
				        'data-block'       => $block->getNameInLayout(),
				        'data-click'       => $click
				    ]);

					if ($category)
					{
						$a->setAttribute('data-category', $this->helper->getCategoryDetailList($products[$key], $category));
					}
					
					/**
					 * Create transport object
					 *
					 * @var \Magento\Framework\DataObject $transport
					 */
					$transport = new \Magento\Framework\DataObject
					(
						[
							'attributes' => $this->attributes->getAttributes()
						]
					);
					
					/**
					 * Notify others
					 */
					$this->helper->getEventManager()->dispatch('ec_get_click_list_attributes', ['transport' => $transport]);
					
					/**
					 * Get response
					 */
					$attributes = $transport->getAttributes();
					
					$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
				}
				
				/**
				 * Track "Add to cart" from Related products
				 */
				if ('' !== $selector = $this->helper->getCartCategorySelector())
				{
					foreach (@$query->query($selector, $element) as $a)
					{
						$click = $a->getAttribute('onclick');
						
						$this->setAttributes($a, $products[$key],
					    [
					        'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_ADD_TO_CART,
					        'data-category'    => $this->helper->getCategory($category),
					        'data-list'        => \Anowave\Ec\Helper\Constants::LIST_RELATED,
					        'data-position'    => $position,
					        'data-block'       => $block->getNameInLayout(),
					        'data-click'       => $click
					    ]);
						
						/**
						 * Create transport object
						 *
						 * @var \Magento\Framework\DataObject $transport
						 */
						$transport = new \Magento\Framework\DataObject
						(
							[
								'attributes' => $this->attributes->getAttributes()
							]
						);
						
						/**
						 * Notify others
						 */
						$this->helper->getEventManager()->dispatch('ec_get_add_list_attributes', ['transport' => $transport]);
						
						/**
						 * Get response
						 */
						$attributes = $transport->getAttributes();
						
						$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
					}
				}
			}
			
			$position++;
		}
		
		$content = $this->dom->getDOMContent($dom, $doc);
		
		/**
		 * Save cache
		 */
		$this->cache->save($content, \Anowave\Ec\Model\Cache::CACHE_LISTING . $block->getNameInLayout());
		
		return $content;
	}
	
	/**
	 * Modify categories listing output
	 *
	 * @param AbstractBlock $block
	 * @param string $content
	 */
	public function augmentListRelatedBlock($block, $content)
	{		
		/**
		 * Remove empty spaces
		 */
		$content = trim($content);
		
		if (!strlen($content))
		{
			return $content;
		}
		
		/**
		 * Load cache
		 *
		 * @var string
		 */
		$cache = $this->cache->load(\Anowave\Ec\Model\Cache::CACHE_LISTING . $block->getNameInLayout());
		
		if ($cache)
		{
			return $cache;
		}
		
		/**
		 * Retrieve list of impression product(s)
		 *
		 * @var array
		 */
		$products = [];
		
		foreach ($this->bridge->getLoadedItems($block) as $product)
		{
		    $products[] = $product;
		}

		/**
		 * Append tracking
		 */
		list($doc, $dom) = $this->getDom();
		
		$dom->loadHTML($content);
		
		$query = new \DOMXPath($dom);
		
		$position = 1;
		
		foreach ($query->query($this->helper->getListSelector()) as $key => $element)
		{
			if (isset($products[$key]))
			{
				/**
				 * Get all product categories
				 */
				$categories = $this->helper->getCurrentStoreProductCategories($products[$key]);
				
				if (!$categories)
				{
					if (null !== $root = $this->helper->getStoreRootDefaultCategoryId())
					{
						$categories[] = $root;
					}
				}
				
				if ($categories)
				{
					/**
					 * Load last category
					 */
					
					$category = $this->categoryRepository->get
					(
						end($categories)
					);
				}
				else 
				{
					$category = null;
				}

				/**
				 * Add data-* attributes used for tracking dynamic values
				 */
				foreach ($query->query($this->helper->getListClickSelector(), $element) as $a)
				{
					$click = $a->getAttribute('onclick');
				
					$this->setAttributes($a, $products[$key],
				    [
				        'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_PRODUCT_CLICK,
				        'data-list'        => \Anowave\Ec\Helper\Constants::LIST_RELATED,
				        'data-position'    => $position,
				        'data-block'       => $block->getNameInLayout(),
				        'data-click'       => $click
				    ]);

					if ($category)
					{
						$a->setAttribute('data-category', $this->helper->getCategoryDetailList($products[$key], $category));
					}
					
					/**
					 * Create transport object
					 *
					 * @var \Magento\Framework\DataObject $transport
					 */
					$transport = new \Magento\Framework\DataObject
					(
						[
							'attributes' => $this->attributes->getAttributes()
						]
					);
					
					/**
					 * Notify others
					 */
					$this->helper->getEventManager()->dispatch('ec_get_click_list_attributes', ['transport' => $transport]);
					
					/**
					 * Get response
					 */
					$attributes = $transport->getAttributes();
					
					$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
				}

				/**
				 * Track "Add to cart" from Related products
				 */
				if ('' !== $selector = $this->helper->getCartCategorySelector())
				{
					foreach (@$query->query($selector, $element) as $a)
					{
						$click = $a->getAttribute('onclick');
						
						$this->setAttributes($a, $products[$key],
					    [
					        'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_ADD_TO_CART,
					        'data-category'    => $this->helper->getCategory($category),
					        'data-list'        => \Anowave\Ec\Helper\Constants::LIST_RELATED,
					        'data-position'    => $position,
					        'data-block'       => $block->getNameInLayout(),
					        'data-click'       => $click
					    ]);

						/**
						 * Create transport object
						 *
						 * @var \Magento\Framework\DataObject $transport
						 */
						$transport = new \Magento\Framework\DataObject
						(
							[
								'attributes' => $this->attributes->getAttributes()
							]
						);
						
						/**
						 * Notify others
						 */
						$this->helper->getEventManager()->dispatch('ec_get_add_list_attributes', ['transport' => $transport]);
						
						/**
						 * Get response
						 */
						$attributes = $transport->getAttributes();
						
						$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
					}
				}
			}
			
			$position++;
		}
		
		$content = $this->dom->getDOMContent($dom, $doc);
		
		/**
		 * Save cache
		 */
		$this->cache->save($content, \Anowave\Ec\Model\Cache::CACHE_LISTING . $block->getNameInLayout());
		
		return $content;
	}
	
	/**
	 * Modify categories listing output
	 *
	 * @param AbstractBlock $block
	 * @param string $content
	 */
	public function augmentWishlistBlock($block, $content)
	{
		/**
		 * Append tracking
		 */
	    list($doc, $dom) = $this->getDom();
		
		$dom->loadHTML($content);
		
		$query = new \DOMXPath($dom);
		
		foreach ($query->query($this->helper->getWishlistSelector()) as $key => $element)
		{
		    $element->setAttribute('data-event', \Anowave\Ec\Helper\Constants::EVENT_ADD_TO_WISHLIST);
			
			if ($this->getCurrentProduct())
			{
			    
			    /**
			     * Create transport object
			     *
			     * @var \Magento\Framework\DataObject $transport
			     */
			    $transport = new \Magento\Framework\DataObject
			    (
			        [
			            'attributes' => 
			            [
			                'id'     => $this->helper->getIdentifier($this->getCurrentProduct()),
			                'name'   => $this->getCurrentProduct()->getName(),
			                'price'  => $this->helper->getPrice
			                (
			                    $this->getCurrentProduct()
		                    )
			            ],
			            'product' => $this->getCurrentProduct()
			        ]
		        );
			    
			    /**
			     * Notify others
			     */
			    $this->helper->getEventManager()->dispatch('ec_get_add_wishlist_attributes', ['transport' => $transport]);
			   
			    /**
			     * Set event attributes
			     */
			    $element->setAttribute('data-event-attributes', $this->helper->getJsonHelper()->encode($transport->getAttributes(), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS));
			  
			    /**
			     * Set event label
			     */
				$element->setAttribute('data-event-label', $this->helper->escapeDataArgument($this->getCurrentProduct()->getName()));
			}
		}
		
		return $this->dom->getDOMContent($dom, $doc);
	}
	
	/**
	 * Modify wishlist sidebar
	 *
	 * @param AbstractBlock $block
	 * @param string $content
	 */
	public function augmentWishlistSidebarBlock($block, $content)
	{
		return $content;
	}
	
	/**
	 * Modify categories listing output
	 *
	 * @param AbstractBlock $block
	 * @param string $content
	 */
	public function augmentCompareBlock($block, $content)
	{
		/**
		 * Append tracking
		 */
	    list($doc, $dom) = $this->getDom();
		
		$dom->loadHTML($content);
		
		$query = new \DOMXPath($dom);
		
		foreach ($query->query($this->helper->getCompareSelector()) as $key => $element)
		{
		    $element->setAttribute('data-event', \Anowave\Ec\Helper\Constants::EVENT_ADD_TO_COMPARE);
			
			if ($this->getCurrentProduct())
			{
			    /**
			     * Create transport object
			     *
			     * @var \Magento\Framework\DataObject $transport
			     */
			    $transport = new \Magento\Framework\DataObject
			    (
			        [
			            'attributes' =>
			            [
			                'id'     => $this->helper->getIdentifier($this->getCurrentProduct()),
			                'name'   => $this->getCurrentProduct()->getName(),
			                'price'  => $this->helper->getPrice
			                (
			                    $this->getCurrentProduct()
		                    )
			            ],
			            'product' => $this->getCurrentProduct()
			        ]
		        );
			    
			    /**
			     * Notify others
			     */
			    $this->helper->getEventManager()->dispatch('ec_get_add_compare_attributes', ['transport' => $transport]);
			    
			    /**
			     * Set event attributes
			     */
			    $element->setAttribute('data-event-attributes', $this->helper->getJsonHelper()->encode($transport->getAttributes(), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS));
			    
				$element->setAttribute('data-event-label', $this->helper->escapeDataArgument($this->getCurrentProduct()->getName()));
			}
		}
		
		return $this->dom->getDOMContent($dom, $doc);
	}
	
	/**
	 * Modify categories listing output
	 *
	 * @param AbstractBlock $block
	 * @param string $content
	 */
	public function augmentListUpsellBlock($block, $content)
	{
		$content = trim($content);
		
		if (!strlen($content))
		{
			return $content;
		}
		
		/**
		 * Load cache
		 *
		 * @var string
		 */
		$cache = $this->cache->load(\Anowave\Ec\Model\Cache::CACHE_LISTING . $block->getNameInLayout());
		
		if ($cache)
		{
			return $cache;
		}
		
		/**
		 * Retrieve list of impression product(s)
		 *
		 * @var array
		 */
		$products = [];
		
		if ($block->getItems())
		{
			foreach ($block->getItems() as $product)
			{
				$products[] = $product;
			}
		}

		/**
		 * Append tracking
		 */
		list($doc, $dom) = $this->getDom();
		
		$dom->loadHTML($content);
		
		$query = new \DOMXPath($dom);
		
		$position = 1;
		
		foreach ($query->query($this->helper->getListSelector()) as $key => $element)
		{
			if (isset($products[$key]))
			{
				/**
				 * Get all product categories
				 */
				$categories = $this->helper->getCurrentStoreProductCategories($products[$key]);
				
				if (!$categories)
				{
					if (null !== $root = $this->helper->getStoreRootDefaultCategoryId())
					{
						$categories[] = $root;
					}
				}
				
				if ($categories)
				{
					/**
					 * Load last category
					 */
					$category = $this->categoryRepository->get
					(
						end($categories)
					);
				}
				else
				{
					$category = null;
				}
				
				/**
				 * Add data-* attributes used for tracking dynamic values
				 */
				foreach ($query->query($this->helper->getListClickSelector(), $element) as $a)
				{
					$click = $a->getAttribute('onclick');
					
					$this->setAttributes($a, $products[$key],
				    [
				        'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_PRODUCT_CLICK,
				        'data-position'    => $position,
				        'data-block'       => $block->getNameInLayout(),
				        'data-click'       => $click
				    ]);
					
					if ($category)
					{
						$a->setAttribute('data-category', $this->helper->getCategoryDetailList($products[$key], $category));
					}
					
					/**
					 * Create transport object
					 *
					 * @var \Magento\Framework\DataObject $transport
					 */
					$transport = new \Magento\Framework\DataObject
					(
						[
							'attributes' => $this->attributes->getAttributes()
						]
					);
					
					/**
					 * Notify others
					 */
					$this->helper->getEventManager()->dispatch('ec_get_click_list_attributes', ['transport' => $transport]);
					
					/**
					 * Get response
					 */
					$attributes = $transport->getAttributes();
					
					$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
				}
				
				/**
				 * Track "Add to cart" from Related products
				 */
				if ('' !== $selector = $this->helper->getCartCategorySelector())
				{
					foreach (@$query->query($selector, $element) as $a)
					{
						$click = $a->getAttribute('onclick');
						
						$this->setAttributes($a, $products[$key],
					    [
					        'data-event'       => \Anowave\Ec\Helper\Constants::EVENT_PRODUCT_CLICK,
					        'data-position'    => $position,
					        'data-category'    => $this->helper->getCategory($category),
					        'data-list'        => $this->helper->getCategoryList($category),
					        'data-block'       => $block->getNameInLayout(),
					        'data-click'       => $click
					    ]);
						
						/**
						 * Create transport object
						 *
						 * @var \Magento\Framework\DataObject $transport
						 */
						$transport = new \Magento\Framework\DataObject
						(
							[
								'attributes' => $this->attributes->getAttributes()
							]
						);
						
						/**
						 * Notify others
						 */
						$this->helper->getEventManager()->dispatch('ec_get_add_list_attributes', ['transport' => $transport]);
						
						/**
						 * Get response
						 */
						$attributes = $transport->getAttributes();
						
						$a->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
					}
				}
			}
			
			$position++;
		}
		
		$content = $this->dom->getDOMContent($dom, $doc);
		
		/**
		 * Save cache
		 */
		$this->cache->save($content, \Anowave\Ec\Model\Cache::CACHE_LISTING . $block->getNameInLayout());
		
		return $content;
	}
	
	/**
	 * Modify remove from cart output
	 *
	 * @param AbstractBlock $block
	 * @param string $content
	 * 
	 * @return string
	 */
	
	public function augmentRemoveCartBlock($block, $content)
	{
		/**
		 * Append tracking
		 */
	    list($doc, $dom) = $this->getDom();
		
		@$dom->loadHTML($content);
		
		/**
		 * Modify DOM
		 */
		
		$x = new \DOMXPath($dom);
		
		foreach ($x->query($this->helper->getDeleteSelector()) as $element)
		{
			/**
			 * Get all product categories
			 */
			$categories = $this->helper->getCurrentStoreProductCategories($block->getItem()->getProduct());
			
			if (!$categories)
			{
				if (null !== $root = $this->helper->getStoreRootDefaultCategoryId())
				{
					$categories[] = $root;
				}
			}

			if (!$this->helper->useSimples())
			{
				$element->setAttribute('data-id', $this->helper->escapeDataArgument($block->getItem()->getProduct()->getSku()));
			}
			else 
			{
				$element->setAttribute('data-id', $this->helper->escapeDataArgument($block->getItem()->getSku()));
			}
			
			$element->setAttribute('data-name', 		  $this->helper->escapeDataArgument($block->getItem()->getProduct()->getName()));
			$element->setAttribute('data-price', 		  $this->helper->escapeDataArgument($this->helper->getPrice($block->getItem()->getProduct())));
			$element->setAttribute('data-brand', 		  $this->helper->escapeDataArgument($this->helper->getBrand($block->getItem()->getProduct())));
			$element->setAttribute('data-quantity', (int) $block->getItem()->getQty());
			$element->setAttribute('data-event', 		  \Anowave\Ec\Helper\Constants::EVENT_REMOVE_FROM_CART);

			if ($element->getAttribute('data-post') && $this->helper->getUseRemoveConfirm())
			{
				/**
				 * Get current data post
				 * 
				 * @var string $post
				 */
				$post = $element->getAttribute('data-post');
				
				/**
				 * Remove standard data-post
				 */
				$element->removeAttribute('data-post');
				
				/**
				 * Create new data-post
				 */
				$element->setAttribute('data-post-action', $post);
			}

			if ($categories)
			{
				/**
				 * Load last category
				 */
				$category = $this->categoryRepository->get
				(
					end($categories)
				);
				
				$element->setAttribute('data-category', 	$this->helper->getCategoryDetailList($block->getItem()->getProduct(), $category));
				$element->setAttribute('data-list', 		$this->helper->getCategoryDetailList($block->getItem()->getProduct(), $category));
			}
			
			/**
			 * Create transport object
			 *
			 * @var \Magento\Framework\DataObject $transport
			 */
			$transport = new \Magento\Framework\DataObject
			(
				[
					'attributes' => $this->attributes->getAttributes(),
				    'product'    => $block->getItem()->getProduct(), 
				    'item'       => $block->getItem()
				]
			);
			
			/**
			 * Notify others
			 */
			$this->helper->getEventManager()->dispatch('ec_get_remove_attributes', ['transport' => $transport]);
			
			/**
			 * Get response
			 */
			$attributes = $transport->getAttributes();
			
			$element->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
		}
		
		
		return $this->dom->getDOMContent($dom, $doc);
	}
	
	
	/**
	 * Modify add to cart output
	 * 
	 * @param AbstractBlock $block
	 * @param string $content
	 * 
	 * @return string
	 */
	public function augmentAddCartBlock($block, $content)
	{
		list($doc, $dom) = $this->getDom();
		
		if ($this->helper->usePlaceholders())
		{
		    $placeholders = $this->applyPlaceholders($content);
		}
		
		@$dom->loadHTML($content);
		
		$x = new \DOMXPath($dom);

		foreach ($x->query($this->helper->getCartSelector()) as $element)
		{
			$category = $this->registry->registry('current_category');
			
			if (!$category)
			{
				/**
				 * Get all product categories
				 */
				$categories = $this->helper->getCurrentStoreProductCategories($block->getProduct());
				
				/**
				 * Cases when product does not exist in any category
				 */
				if (!$categories)
				{
					$categories[] = $this->helper->getStoreRootDefaultCategoryId();
				}
					
				/**
				 * Load last category
				*/
				$category = $this->categoryRepository->get
				(
					end($categories)
				);
			}
			
			/**
			 * Get existing onclick attribute
			 * 
			 * @var string
			 */
			$click = $element->getAttribute('onclick');
			
			/**
		     * Set data attributes
		     */
			$this->setAttributes($element, $block->getProduct(),
		    [
		        'data-category' => $this->helper->getCategory($category),
		        'data-list'     => $this->helper->getCategoryDetailList($block->getProduct(), $category),
		        'data-event'    => \Anowave\Ec\Helper\Constants::EVENT_ADD_TO_CART,
		        'data-click'    => $click
		    ]);
			
			try 
			{
				/**
				 * Get current stock level 
				 * 
				 * @var int $max
				 */
				$max = (int) $this->stockItemRepository->get($block->getProduct()->getId())->getQty();
				
				$element->setAttribute('data-quantity-max', $max);
			}
			catch (\Exception $e){}

			if (\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE == $block->getProduct()->getTypeId())
			{
				$element->setAttribute('data-grouped',1);
			}
			
			if ('configurable' == $block->getProduct()->getTypeId())
			{
				$element->setAttribute('data-configurable',1);
			}
			
			/**
			 * Create transport object
			 *
			 * @var \Magento\Framework\DataObject $transport
			 */
			$transport = new \Magento\Framework\DataObject
			(
				[
					'attributes' => $this->attributes->getAttributes(),
				    'product'    => $block->getProduct()
				]
			);
			
			/**
			 * Notify others
			 */
			$this->helper->getEventManager()->dispatch('ec_get_add_attributes', ['transport' => $transport]);
			
			/**
			 * Get response
			 */
			$attributes = $transport->getAttributes();
			
			$element->setAttribute('data-attributes', $this->helper->getJsonHelper()->encode($attributes));
		}

		$content =  $this->dom->getDOMContent($dom, $doc);
		
		if ($this->helper->usePlaceholders())
		{
		    if (isset($placeholders))
		    {
		        $content = $this->restorePlaceholders($content, $placeholders);
		    }
		}
		
		return $content;
	}
	
	/**
	 * Get current product
	 */
	public function getCurrentProduct()
	{
		return $this->registry->registry('current_product');
	}
	
	/**
	 * Accelerated Mobile Pages support
	 *
	 * @param string $content
	 * @return string
	 */
	public function augmentAmp($block, $content)
	{
		if (!$this->helper->supportAmp())
		{
			return $content;
		}
	
		/**
		 * Parse content and detect amp-analytics snippet
		 */
		if (false !== strpos($content, 'amp-analytics'))
		{
		    list($doc, $dom) = $this->getDom();
				
			@$dom->loadHTML($content);
				
			$x = new \DOMXPath($dom);
				
			$amp = $x->query('//amp-analytics');
	
			if ($amp->length > 0)
			{
				foreach ($amp as $node)
				{
					$params = $dom->createElement('script');
						
					$params->setAttribute('type','application/json');
						
					/**
					 * Enhanced Ecommerce parameters
					*/
					$params->nodeValue = $this->helper->getJsonHelper()->encode($this->getAmpVariables($node, $block));
						
					$params = $node->appendChild($params);
				}
			}
			
			return $this->dom->getDOMContent($dom, $doc);
		}
		
		return $content;
	}
	
	/**
	 * Generate AMP variables
	 *
	 * @param void
	 * @return []
	 */
	public function getAmpVariables(\DOMElement $node, $block)
	{
		$vars = [];
	
		/**
		 * Read pre-defined variables from static snippets and merge to global []
		*/
		foreach ($node->getElementsByTagName('script') as $script)
		{
			$vars = array_merge($vars, json_decode(trim($script->nodeValue), true));
		}
	
		/**
		 * Get visitor data
		 */
		$vars['vars']['visitor'] = json_decode($this->helper->getVisitorPush($block), true);
		
		/**
		 * Read persistent dataLayer
		 */
		$data = $this->dataLayer->get();
		
		$vars['vars'] = array_merge_recursive($vars['vars'], $data);
		
		return $vars;
	}
	
	/**
	 * Set data attributes
	 * 
	 * @param \DOMElement $element
	 * @param \Magento\Catalog\Model\Product $product
	 */
	public function setAttributes(\DOMElement $element, \Magento\Catalog\Model\Product $product, array $attributes = [])
	{
	    /**
	     * Set common attributes
	     */
	    foreach 
	    (
    	    [
    	        'data-id'                          => $this->helper->getIdentifier($product),
    	        'data-simple-id'                   => $product->getSku(),
    	        'data-remarketing-adwords-id'      => $this->helper->getAdwordsRemarketingId($product),
    	        'data-remarketing-facebook-id'     => $this->helper->getFacebookRemarketingId($product),
    	        'data-name'                        => $product->getName(),
    	        'data-price'                       => $this->helper->getPrice($product),
    	        'data-store'                       => $this->helper->getStoreName(),
    	        'data-brand'                       => $this->helper->getBrand($product),
    	        'data-use-simple'                  => $this->helper->useSimples() ? 1 : 0,
    	        'data-quantity'                    => 1
    	    ] 
        as  $attribute => $value)
	    {
	        $element->setAttribute($attribute, $this->helper->escapeDataArgument($value));
	    }
	    
	    /**
	     * Set stock index dimension
	     */
	    $element->setAttribute("data-{$this->helper->getStockDimensionIndex(true)}", $this->helper->getStock($product));
	    
	    /**
	     * Set custom attributes
	     */
	    foreach ($attributes as $attribute => $value)
	    {
	        $element->setAttribute($attribute, $value);
	    }
	    
	    if (0)
	    {
    	    $map = [];
    	    
    	    foreach ($element->attributes as $attribute)
    	    {
    	        $map[$attribute->nodeName] = $attribute->nodeValue;
    	    }
	    }
	}
	
	/**
	 * Get DOM wrappers
	 * 
	 * @return array
	 */
	protected function getDom() : array
	{
	    return 
	    [
	        new \Anowave\Ec\Model\Dom('1.0','utf-8'), 
	        new \Anowave\Ec\Model\Dom('1.0','utf-8')
	    ];
	}
	
	/**
	 * Get placeholders
	 *
	 * @param string $content
	 *
	 * @return string[]|NULL
	 */
	protected function getPlaceholders($content)
	{
		/**
		 * Match inline JS scripts
		 */
	    preg_match_all('/<script\s?([^>]*?)>.*?<\/script>/ims', $content, $matches);
		
		if ($matches)
		{
			$placeholders = [];
			
			foreach ($matches[0] as $key => $match)
			{
				$placeholders["%{$key}%"] = $match;
			}
			
			return $placeholders;
		}
		
		return null;
	}
	
	/**
	 * Apply placeholders
	 *
	 * @param string $content
	 *
	 * @return string[]|NULL
	 */
	protected function applyPlaceholders(&$content)
	{
		if (null !== $placeholders = $this->getPlaceholders($content));
		{
			foreach ($placeholders as $placeholder => $value)
			{
				$content = str_replace($value,$placeholder, $content);
			}
		}
		
		return $placeholders;
	}
	
	/**
	 * Restore placeholders
	 *
	 * @param string $content
	 * @param string $placeholders
	 *
	 * @return mixed
	 */
	protected function restorePlaceholders(&$content, $placeholders)
	{
		if ($placeholders)
		{
			if ($placeholders)
			{
				foreach ($placeholders as $placeholder => $value)
				{
					$content = str_replace($placeholder,$value, $content);
				}
			}
		}
		
		return $content;
	}
}