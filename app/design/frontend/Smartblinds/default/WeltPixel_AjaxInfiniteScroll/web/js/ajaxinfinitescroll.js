define(["jquery","ias","ajaxinfinitescroll","mage/storage","jquery/jquery-storageapi"],function(n,c){"use strict";window.ajaxInfiniteScroll={initInfiniteScroll:function(){jQuery(function(e){var t={container:".products.wrapper .product-items",item:".product-item",pagination:".toolbar .pages, .toolbar .limiter",next:".pages .next",negativeMargin:window.negativeMargin},a=jQuery(".toolbar-amount"),o=jQuery(".toolbar-amount .toolbar-number").length;o>2&&(jQuery(".toolbar-amount .toolbar-number:nth-child(1)").text("1"),jQuery(".toolbar-amount .toolbar-number:nth-child(2)").hide(),a.html(a.html().replace(/\-/g,"")),a.html(a.html().replace(/\of/g,"to"))),e.ajaxSetup({cache:!0}),e(t.container).closest(".column.main").addClass("infinite-scroll");var i=e.ias(t);i.getNextUrl=function(d){d||(d=i.$container);var r=e(i.nextSelector,d).last().attr("href");return typeof r!="undefined"&&(window.location.protocol=="https:"?r=r.replace("http:",window.location.protocol):r=r.replace("https:",window.location.protocol),r=window.ajaxInfiniteScroll.removeQueryStringParameter("_",r)),r&&(r=window.ajaxInfiniteScroll.removeQueryStringParameter("isAjax",r),r)},i.on("load",function(d){var r=d.url;d.ajaxOptions.cache=!0,d.url=window.ajaxInfiniteScroll.removeQueryStringParameter("_",d.url)}),i.extension(new c.IASPagingExtension),i.on("pageChange",function(d,r,s){window.page=d}),i.on("loaded",function(d,r){window.ajaxInfiniteScroll.reloadImages(r),window.ajaxInfiniteScroll.dataLayerUpdate(d),window.ajaxInfiniteScroll.updateQuickviewPrevNext(d),window.ajaxInfiniteScroll.updateProductPagePrevNext(d)}),i.on("rendered",function(d){window.ajaxInfiniteScroll.fixAddToCart(),window.ajaxInfiniteScroll.reloadQuickView(),window.ajaxInfiniteScroll.reloadCategoryPage(),e("body").trigger("contentUpdated"),window.showCanonical==1&&window.ajaxInfiniteScroll.reloadCanonicalPrevNext(),e(".product-item-info a").each(function(){typeof e(this).attr("data-item-page")=="undefined"&&e(this).attr("data-item-page",window.page)}),e(document).trigger("wpproductlabels:init"),e.mage.formKey(),e("li.product-item").trigger("contentUpdated"),window.isSlCustomPopupUsed&&parseInt(window.isSlCustomPopupUsed)&&e("li.product-item").find(".towishlist").each(function(){e(this).removeAttr("data-post")})}),i.extension(new c.IASNoneLeftExtension({html:'<span class="ias-no-more '+window.displaySwatchClass+'">'+window.textNoMore+"</span>"}));var l='<div class="ias-spinner">';l+='<img src="{src}"',window.wp_ajax_useCustomPlaceholder=="1"&&(l+="style='max-width:"+window.wp_ajax_placeholderCustomWidth+"'"),l+="/>",l+="<span>"+window.textLoadingMore+"</span>",l+="</div>",i.extension(new c.IASSpinnerExtension({src:window.loadingImage,html:l})),window.LoadMore>0?i.extension(new c.IASTriggerExtension({text:window.textNext,html:'<button class="button action ias-load-more" type="button"><span>{text}</span></button>',textPrev:window.textPrevious,htmlPrev:'<button class="button action ias-load-prev" type="button"><span>{text}</span></button>',offset:window.LoadMore})):i.extension(new c.IASTriggerExtension({textPrev:window.textPrevious,htmlPrev:'<button class="button action ias-load-prev" type="button"><span>{text}</span></button>',offset:1e3})),i.extension(new c.IASHistoryExtension({prev:".previous"}))})},initNextPage:function(){jQuery(".toolbar-products .pages").length>1&&jQuery(".toolbar-products .pages").first().remove(),jQuery(function(e){var t={container:".products.wrapper .product-items",item:".product-item",next:"",textNext:"Load next items",pageLink:".pages li.item a.page",prevLink:".toolbar-products .action.previous",nextLink:".toolbar-products .action.next"};e.ajaxSetup({cache:!0}),window.ajaxInfiniteScroll.addPageSelector(t.pageLink),e(t.container).closest(".column.main").addClass("next-page");var a=t.pageLink+", "+t.prevLink+", "+t.nextLink,o=e(".wp-filters-ajax");o.length>0?e(a).off("click"):e(document).on("click",a,function(i){i.preventDefault(),window.ajaxInfiniteScroll.addPageSelector(t.pageLink),e(this).attr("id")?t.next="#"+e(this).attr("id"):t.next="#page-"+window.ajaxInfiniteScroll.getUrlParameter("p",e(this).attr("href"));var l=e.ias(t),d=e(this),r='<div class="ias-overlay">';r+='<div class="ias-spinner" style="display: none">',r+='<img src="{src}"',window.wp_ajax_useCustomPlaceholder=="1"&&(r+="style='max-width:"+window.wp_ajax_placeholderCustomWidth+"'"),r+="/>",r+="<span>"+window.textLoadingMore+"</span>",r+="</div>",r+="</div>",l.extension(new c.IASSpinnerExtension({src:window.loadingImage,html:r})),l.on("load",function(){window.ajaxInfiniteScroll.backToTop();var s=e(".sidebar"),p=parseInt(e(".ias-spinner").outerWidth())/2;e(".ias-spinner").css({left:"calc(50% - "+p+"px)"}).fadeIn(),e(t.item).each(function(){e(this).addClass("remove")}),l.destroy(),window.ajaxInfiniteScroll.reloadPagination(d,t.pageLink)}),l.on("loaded",function(s,p){window.ajaxInfiniteScroll.reloadImages(p),window.ajaxInfiniteScroll.dataLayerUpdate(s),window.ajaxInfiniteScroll.updateQuickviewPrevNext(s),window.ajaxInfiniteScroll.updateProductPagePrevNext(s)}),l.on("rendered",function(){window.ajaxInfiniteScroll.fixAddToCart(),e(t.item).each(function(){e(this).hasClass("remove")&&e(this).remove()}),e(".ias-overlay").remove(),window.ajaxInfiniteScroll.reloadQuickView(),window.ajaxInfiniteScroll.reloadCategoryPage(),jQuery(".toolbar-products .pages").length>1&&jQuery(".toolbar-products .pages").first().remove(),e(document).trigger("wpproductlabels:init"),e.mage.formKey(),e("li.product-item").trigger("contentUpdated"),window.isSlCustomPopupUsed&&parseInt(window.isSlCustomPopupUsed)&&e("li.product-item").find(".towishlist").each(function(){e(this).removeAttr("data-post")})}),l.next(),l.destroy()})})},resetIasPagination:function(e,t){jQuery.ias().destroy();var a=window.ajaxInfiniteScroll.replaceUrlPrameter(e,t);window.history.replaceState("","",a);var o={container:".products.wrapper .product-items",item:".product-item",next:"",textNext:"Load next items",pageLink:".pages li.item a.page",prevLink:".toolbar-products .action.previous",nextLink:".toolbar-products .action.next"};n.ajaxSetup({cache:!0}),window.ajaxInfiniteScroll.addPageSelector(o.pageLink),n(o.container).closest(".column.main").addClass("next-page")},fixAddToCart:function(){require.defined("catalogAddToCart")&&n("form[data-role='tocart-form']").length&&n("form[data-role='tocart-form']").catalogAddToCart()},reloadQuickView:function(){if(window.quickview){var e=n(".weltpixel-quickview");e.length&&(n(".weltpixel-quickview").bind("click",function(){var t=n(this).attr("data-quickview-url");if(t.length)return window.quickview.displayContent(t),!1}),window.wpQwListMode=="list"&&e.each(function(t,a){if(!n(a).hasClass("wp-qw-adjusted")){var o=n(a).closest(".product-item").find(".product-item-info").get(0),i=n(a).closest(".product-item-info").find(".product-item-photo").get(0);n(o).prepend('<div class="product photo product-item-photo product-image-list"></div>');var l=n(a).closest(".product-item-info").find(".product-image-list").get(0);n(i).appendTo(l);var d=n(a).closest(".product-item-info").find(".product-image-list").get(0);n(a).show().appendTo(d),n(a).addClass("wp-qw-adjusted"),n(a).css("display","")}}))}},reloadCategoryPage:function(){window.CategoryPage&&window.CategoryPage.actions()},reloadImages:function(e){n(e).each(function(){var t=n(this).find(".product-image-photo");t.hasClass("lazy")&&t.hide().attr("src",t.data("original")).css({"max-width":"100%"}).fadeIn("slow")})},dataLayerUpdate:function(e){var t=e.match(/var dlObjects = (.*?);/);if(t!=null&&typeof t=="object"&&t.length==2){var a=JSON.parse(t[1]);window.dataLayer=window.dataLayer||[];for(var o in a)window.dataLayer.push(a[o])}var i=e.match(/var dl4Objects = (.*?);/);if(i!=null&&typeof i=="object"&&i.length==2){var l=JSON.parse(i[1]);window.dataLayer=window.dataLayer||[];for(var o in l)window.dataLayer.push(l[o])}},updateQuickviewPrevNext:function(e){var t=e.match(/window.quickviewProductIds.*\[(.*)];/);if(t!=null&&typeof t=="object"&&t.length==2)for(var a=t[1].split(","),o=0;o<a.length;o++)window.quickviewProductIds.push(a[o].replace(/['"]/g,""))},updateProductPagePrevNext:function(e){n.cookieStorage.setConf({path:"/",expires:1});var t=n.cookieStorage.get("wpListedProductIds"),a=e.match(/wpListedProductIds.*\[(.*)];/);if(a!=null&&typeof a=="object"&&a.length==2)for(var o=a[1].split(","),i=0;i<o.length;i++)t.push(o[i].replace(/['"]/g,""));n.cookieStorage.set("wpListedProductIds",t)},addPageSelector:function(e){n(e).each(function(){n(this).attr("id","page-"+n(this).find("span:last-child").text())})},backToTop:function(){var e=n(".sticky-header, .sticky-header-mobile"),t=0;e.length&&(t=parseInt(e.outerHeight())),n("html, body").animate({scrollTop:n(".column.main").offset().top-t},"slow")},scrollToLocation:function(){if(window.location.hash){var e=t.hash.substr(1),t=n('*[data-product-id="'+e+'"]');t.length&&(n("html, body").animate({scrollTop:t.offset().top},"slow"),window.history.pushState("",document.title,window.location.pathname+window.location.search))}},reloadPagination:function(e,t){var a=window.ajaxInfiniteScroll.getUrlParameter("q",window.ajaxInfiniteScroll.removeQueryStringParameter("p"));n.ajax({cache:!0,url:window.ajaxReloadPaginationUrl,data:{is_ajax:1,category_id:window.currentCategory,q:a,p:window.ajaxInfiniteScroll.getUrlParameter("p",e.attr("href")),pager_url:window.ajaxInfiniteScroll.removeQueryStringParameter("p"),limiter_url:window.ajaxInfiniteScroll.removeQueryStringParameter("product_list_limit")},success:function(o){n(".toolbar.toolbar-products").last().html(o.pager).trigger("contentUpdated");var i=n(o.toolbar);n(".toolbar-amount").replaceWith(i.find(".toolbar-amount")).trigger("contentUpdated"),window.ajaxInfiniteScroll.addPageSelector(t);var l={page:"",url:e.attr("href")};history.pushState(l,l.page,l.url),window.showCanonical==1&&window.ajaxInfiniteScroll.reloadCanonicalPrevNext()}})},reloadCanonicalPrevNext:function(){n.ajax({cache:!0,url:window.ajaxCanonicalRefresh,data:{is_ajax:1,current_url:window.location.href},success:function(e){if(e.prev){var t=n('link[rel="prev"]');t.length?t.attr("href",e.prev):n('<link rel="prev" href="'+e.prev+'">').insertAfter('link[rel="canonical"]')}else n('link[rel="prev"]').remove();setTimeout(function(){if(e.next&&n(".ias-no-more").length==0){var a=n('link[rel="next"]');a.length?a.attr("href",e.next):n('<link rel="next" href="'+e.next+'">').insertAfter('link[rel="canonical"]')}else n('link[rel="next"]').remove()},1500)}})},removeQueryStringParameter:function(e,t){t||(t=window.location.href);var a=t.split("#"),o=new RegExp("([?&])"+e+"=.*?(&|#|$)","i");return a[0].match(o)&&(t=a[0].replace(o,"$1"),t=t.replace(/([?&])$/,""),typeof a[1]!="undefined"&&a[1]!==null&&(t+="#"+a[1])),t},getUrlParameter:function(e,t){t||(t=window.location.href);var a=new RegExp("[?&]"+e+"=([^&#]*)").exec(t);return a==null?0:a[1]||0},replaceUrlPrameter:function(e,t){var a=t.replace(/(p=).*?(&|$)/,"$1"+e+"$2");return a}}});
