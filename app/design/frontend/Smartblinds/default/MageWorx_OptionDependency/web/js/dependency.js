define(["jquery","underscore","jquery-ui-modules/widget"],function(n,c){"use strict";return n.widget("mageworx.optionDependency",{options:{dataType:{option:"data-option_id",value:"data-option_type_id"},addToCartSelector:"#product_addtocart_form",options:[],firstRunProcessed:[]},baseObject:{},firstRun:function(i,t,a,o){this.options.options=[],this.initOptions(),this.baseObject=a;var e=this;n.each(o.options,function(p,s){e.options[p]=s}),n(".mageworx-need-wrap").wrap("<span>");var d=n(".mageworx-disable-date-validation");d.length>0&&(d.find("select").attr("data-validate",'{"datetime-validation": false}'),this.disableDatetimeValidation(d)),!c.isUndefined(o.options.dependencyRulesJson)&&o.options.dependencyRulesJson.length!==0&&(this.options.dependencyRules=JSON.parse(o.options.dependencyRulesJson)),window.apoData={},n.each(this.options.options,function(p,s){window.apoData[s.id]=[]}),c.isUndefined(o.options.selectedValues)||n.each(o.options.selectedValues,function(p,s){window.apoData[p]=s}),o.options.isAdmin&&n.each(i,function(p,s){var r=n('[data-option_id="'+p+'"]')?n('[data-option_id="'+p+'"]'):n('[data-option_type_id="'+p+'"]');r.css("display")=="none"&&(r.removeClass("required"),(r.find('input[type="file"]').length<1||o.options.isAdmin)&&(r.find("input, select, textarea, .field").removeClass("required"),r.find("input, select, textarea, .field").removeClass("required-entry")))})},update:function(i,t,a,o){var e=this,d=n(i).closest("[data-option_id]"),p=d.attr("data-option_id"),s=e.getOptionObject(p,"option"),r=n(i).find("[data-option_type_id]").first();r.length<1&&(r=n(i).closest("[data-option_type_id]"));var l=s;if(r){var u=parseInt(r.attr("data-option_type_id"));l=e.getOptionObject(u,"value")}if(n.inArray(s.type,["drop_down","multiple"])!==-1)if(s.type==="drop_down")n("#"+i.attr("id")+" option:selected").each(function(){e.toggleDropdown(s,e.getOptionObject(n(this).attr("data-option_type_id"),"value"))});else{var f=n("#"+i.attr("id")+" option:selected");f.length>0?e.toggleMultiselect(s,f):e.resetMultiselect(s)}else n.inArray(s.type,["checkbox","radio"])!==-1&&(s.type==="radio"?n(i).is(":checked")&&e.toggleRadio(s,l):n(i).is(":checked")?e.toggleCheckbox(s,l):e.resetCheckbox(s,l));for(e.options.needDependencyRulesProcessing=!0;e.options.needDependencyRulesProcessing;)e.options.needDependencyRulesProcessing=!1,e.processDependencyRules();e.options.hiddenValues=e.options.valuesToHide,e.options.valuesToHide=[],e.options.hiddenOptions=e.options.optionsToHide,e.options.optionsToHide=[]},toggleDropdown:function(i,t){var a=this;typeof t.id=="undefined"&&c.isArray(window.apoData[i.id])&&n.each(window.apoData[i.id],function(o,e){var d=window.apoData[i.id].indexOf(parseInt(e));d!==-1&&window.apoData[i.id].splice(d,1)}),typeof t.id!="undefined"&&(c.isArray(window.apoData[i.id])&&n.each(window.apoData[i.id],function(o,e){var d=window.apoData[i.id].indexOf(parseInt(e));e!==t.id&&d!==-1&&window.apoData[i.id].splice(d,1)}),c.isUndefined(window.apoData[i.id])&&(window.apoData[i.id]=[]),window.apoData[i.id].push(parseInt(t.id)))},toggleMultiselect:function(i,t){var a=this,o=[];n.each(t,function(e,d){o.push(parseInt(n(d).attr("data-option_type_id")))}),n.each(window.apoData[i.id],function(e,d){var p=o.indexOf(parseInt(d));if(p===-1){var s=window.apoData[i.id].indexOf(parseInt(d));window.apoData[i.id].splice(s,1)}}),n.each(t,function(e,d){var p=a.getOptionObject(n(d).attr("data-option_type_id"),"value"),s=window.apoData[i.id].indexOf(parseInt(p.id));s===-1&&(c.isUndefined(window.apoData[i.id])&&(window.apoData[i.id]=[]),window.apoData[i.id].push(parseInt(p.id)))})},resetMultiselect:function(i){var t=this;n.each(window.apoData[i.id],function(a,o){var e=window.apoData[i.id].indexOf(parseInt(o));e!==-1&&window.apoData[i.id].splice(e,1)}),window.apoData[i.id]=[]},toggleRadio:function(i,t){var a=this;typeof t.id!="undefined"&&(c.isUndefined(window.apoData)&&(window.apoData={}),c.isArray(window.apoData[i.id])&&n.each(window.apoData[i.id],function(o,e){var d=window.apoData[i.id].indexOf(parseInt(e));e.id!==t.id&&d!==-1&&window.apoData[i.id].splice(d,1)}),c.isUndefined(window.apoData[i.id])&&(window.apoData[i.id]=[]),window.apoData[i.id].push(parseInt(t.id)))},toggleCheckbox:function(i,t){var a=this;typeof t.id!="undefined"&&(c.isUndefined(window.apoData[i.id])&&(window.apoData[i.id]=[]),window.apoData[i.id].push(parseInt(t.id)))},resetCheckbox:function(i,t){var a=this,o=window.apoData[i.id].indexOf(parseInt(t.id));o!==-1&&window.apoData[i.id].splice(o,1)},processDependencyRules:function(){var i=this;i.options.optionsToHide=[],i.options.valuesToHide=[],n.each(i.options.dependencyRules,function(t,a){a.condition_type==="and"?i.processDependencyAndRules(a):i.processDependencyOrRules(a)}),i.hideOptionIfAllValuesHidden(),i.runShowProcessor()},processDependencyOrRules:function(i){var t=this,a=!1,o=!1;n.each(i.conditions,function(e,d){var p=d.values;p.length<1&&d.id&&t.options.optionToValueMap[d.id]&&(p=t.options.optionToValueMap[d.id],a=!0),d.type==="!eq"?(n.each(p,function(s,r){var l=t.options.valueToOptionMap[r],u=-1;if(c.isUndefined(l)||(u=window.apoData[l].indexOf(parseInt(r))),a){if(u!==-1)return o=!0,!1}else u===-1&&t.processHiddenValuesByRule(i)}),a&&!o&&t.processHiddenValuesByRule(i)):n.each(p,function(s,r){var l=t.options.valueToOptionMap[r],u=-1;c.isUndefined(l)||(u=window.apoData[l].indexOf(parseInt(r))),u!==-1&&t.processHiddenValuesByRule(i)})})},processDependencyAndRules:function(i){var t=this,a=!0;n.each(i.conditions,function(o,e){if(a===!1)return!1;var d=e.values;d.length<1&&e.id&&t.options.optionToValueMap[e.id]&&(d=t.options.optionToValueMap[e.id]),e.type==="!eq"?n.each(d,function(p,s){var r=t.options.valueToOptionMap[s],l=-1;if(c.isUndefined(r)||(l=window.apoData[r].indexOf(parseInt(s))),l!==-1)return a=!1,!1}):n.each(d,function(p,s){var r=t.options.valueToOptionMap[s],l=-1;if(c.isUndefined(r)||(l=window.apoData[r].indexOf(parseInt(s))),l===-1)return a=!1,!1})}),a&&t.processHiddenValuesByRule(i)},processHiddenValuesByRule:function(i){var t=this;n.each(i.actions.hide,function(a,o){var e=t.getOptionObject(o.id,"option");n.inArray(e.type,["drop_down","multiple","checkbox","radio"])===-1?n.inArray(parseInt(o.id),t.options.optionsToHide)===-1&&t.options.optionsToHide.push(parseInt(o.id)):n.each(o.values,function(d,p){var s=window.apoData[o.id].indexOf(parseInt(p));if(s!==-1){t.options.needDependencyRulesProcessing=!0,window.apoData[o.id].splice(s,1);var r=t.getOptionObject(p,"value")}n.inArray(parseInt(p),t.options.valuesToHide)===-1&&t.options.valuesToHide.push(parseInt(p))}),t.runHideProcessor(o)})},runShowProcessor:function(){var i=this;n.each(i.options.hiddenOptions,function(t,a){var o=i.options.optionsToHide.indexOf(parseInt(a));if(o===-1){var e=i.getOptionObject(a,"option");e!==""&&i.show(e,!0)}}),n.each(i.options.hiddenValues,function(t,a){var o=i.options.valuesToHide.indexOf(parseInt(a));if(o===-1){var e=i.getOptionObject(a,"value");e!==""&&(i.show(e.getOption(),!0),i.show(e,!1))}})},hideOptionIfAllValuesHidden:function(){var i=this;n.each(i.options.optionToValueMap,function(t,a){var o=!0;if(!(a.length<1)&&(n.each(a,function(s,r){if(n.inArray(parseInt(r),i.options.valuesToHide)===-1)return o=!1,!1}),o)){if(n.inArray(parseInt(t),i.options.optionsToHide)!==-1)return;i.options.optionsToHide.push(parseInt(t));var e=!0,d=i.options.hiddenOptions.indexOf(parseInt(t));if(d!==-1)return;var p=i.getOptionObject(t,"option");p!==""&&i.hide(p,e)}})},show:function(i,t){var a=this,o=!1,e=n(t?'[data-option_id="'+i.id+'"]':'[data-option_type_id="'+i.id+'"]');if(t&&typeof a.options.optionRequiredConfig!="undefined"&&(o=typeof a.options.optionRequiredConfig[i.id]!="undefined"?a.options.optionRequiredConfig[i.id]:!1),!t&&e.css("display")==="none"&&a.baseObject.addNewlyShowedOptionValue(i.id),!t){var d=i.getOption().type;n.inArray(d,["drop_down","multiple"])!==-1&&e.parent().prop("tagName").toLowerCase()==="span"&&e.unwrap("span")}e.show(),t&&o&&(e.hasClass("date")||e.find(".datetime-picker").length>0?a.enableDatetimeValidation(e):(e.addClass("required"),(e.find('input[type="file"]').length<1||a.options.isAdmin)&&(e.find("input, select, textarea, .field").addClass("required"),e.find("input, select, textarea, .field").addClass("required-entry"))))},runHideProcessor:function(i){var t=this,a=!1;if(!c.isEmpty(i.values))n.each(i.values,function(d,p){var s=t.options.hiddenValues.indexOf(parseInt(p));if(s===-1){var r=t.getOptionObject(p,"value");r!==""&&t.hide(r,a)}});else{a=!0;var o=t.options.hiddenOptions.indexOf(parseInt(i.id));if(o===-1){var e=t.getOptionObject(i.id,"option");e!==""&&t.hide(e,a)}}},hide:function(i,t){var a=this,o=!1,e=n(t?'[data-option_id="'+i.id+'"]':'[data-option_type_id="'+i.id+'"]');if(t&&typeof a.options.optionRequiredConfig!="undefined"&&(o=typeof a.options.optionRequiredConfig[i.id]!="undefined"?a.options.optionRequiredConfig[i.id]:!1),!t){var d=i.getOption().type;n.inArray(d,["drop_down","multiple"])!==-1&&e.parent().prop("tagName").toLowerCase()!=="span"&&e.wrap("<span>")}e.hide(),t&&o&&(e.hasClass("date")||e.find(".datetime-picker").length>0?a.disableDatetimeValidation(e):(e.removeClass("required"),(e.find('input[type="file"]').length<1||a.options.isAdmin)&&(e.find("input, select, textarea, .field").removeClass("required"),e.find("input, select, textarea, .field").removeClass("required-entry")))),i.reset()},getOptionObject:function(i,t){var a="";return n.each(this.options.options,function(o,e){if(t==="option"&&parseInt(e.id)===parseInt(i))return a=e,!1;n.each(e.values,function(d,p){if(t==="value"&&parseInt(p.id)===parseInt(i))return a=p,!1})}),a},initOptions:function(){var i=this,t,a;return t=function(o){var e="";return n.each(i.options.options,function(d,p){n.each(p.values,function(s,r){if(o===r.id){e=r.getOption().type;return}})}),e},a=function(o){var e=!c.isUndefined(o.type);if(e)return this;var d=n('[data-option_type_id="'+o.id+'"]');if(d.css("display")!=="none")return this;var p=o.getOption().type,s=null;n.inArray(p,["checkbox","radio"])!==-1&&(s=d.children("input"),s.removeAttr("checked")),n.inArray(p,["drop_down","multiple"])!==-1&&(s=d.closest("select"),d.removeAttr("selected"));var r=n(i.options.addToCartSelector).data("magePriceOptions");return!c.isUndefined(r)&&!c.isNull(s)&&r._onOptionChanged({target:s}),this},n("[data-option_id]").each(function(o,e){var d=[],p={};n(e).find("[data-option_type_id]").each(function(s,r){var l={id:n(r).attr("data-option_type_id"),_getType:function(u){return t(u)},reset:function(){return a(this)},getOption:function(){return p}};d.push(l)}),p={id:parseInt(n(e).attr("data-option_id")),type:i.options.optionTypes[n(e).attr("data-option_id")],values:d,_getType:function(s){return t(s)},reset:function(){return a(this)}},i.options.options.push(p)}),this},disableDatetimeValidation:function(i){this.setDatetimeValidation(i,!1)},enableDatetimeValidation:function(i){this.setDatetimeValidation(i,!0)},setDatetimeValidation:function(i,t){var a=t?"date":"datetime",o=t?"datetime":"date",e=i.find("input:hidden[name^='validate_"+a+"_']");!c.isUndefined(e)&&e.length>0&&(e.attr("name",e.attr("name").replace(a,o)),e.attr("class",e.attr("class").replace(a,o))),i.find("select").attr("data-validate",'{"datetime-validation": '+t+"}")}}),n.mageworx.optionDependency});
