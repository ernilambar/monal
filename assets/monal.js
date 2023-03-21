(()=>{"use strict";(function(n){let t;const e={install_plugins(n){(new i).init(n)},install_content(n){(new s).init(n)}};function o(){const t=n(".monal__body"),o=(n(".monal__body--loading"),n(".monal__body--exiting"),n("#monal__drawer-trigger")),i="monal__drawer--open";setTimeout((function(){t.addClass("loaded")}),100),o.on("click",(function(){t.toggleClass(i)})),n(".monal__button--proceed:not(.monal__button--closer)").on("click",(function(n){n.preventDefault();const e=this.getAttribute("href");t.addClass("exiting"),setTimeout((function(){window.location=e}),400)})),n(".monal__button--closer").on("click",(function(n){t.removeClass(i),n.preventDefault();const e=this.getAttribute("href");setTimeout((function(){t.addClass("exiting")}),600),setTimeout((function(){window.location=e}),1100)})),n(".button-next").on("click",(function(t){if(t.preventDefault(),!function(n){const t=jQuery(n);return"yes"!==t.data("done-loading")&&(t.data("done-loading","yes"),t.addClass("monal__button--loading"),{done(){t.attr("disabled",!1)}})}(this))return!1;const o=n(this).data("callback");return!o||void 0===e[o]||(e[o](this),!1)})),n(document).on("change",".js-monal-demo-import-select",(function(){const t=n(this).val(),e=MONAL_LOCALIZED.base_url+"/assets/img/no-preview.png";let o=MONAL_LOCALIZED.import_files[t].import_preview_image_url;void 0!==o&&""!==o||(o=e),n(".js-content-preview-billboard").find("img").attr("src",o),n(".js-monal-select-spinner").show(),n.post(MONAL_LOCALIZED.ajaxurl,{action:"monal_update_selected_import_data_info",wpnonce:MONAL_LOCALIZED.wpnonce,selected_index:t},(function(t){t.success?n(".js-monal-drawer-import-content").html(t.data):alert(MONAL_LOCALIZED.texts.something_went_wrong),n(".js-monal-select-spinner").hide()})).fail((function(){n(".js-monal-select-spinner").hide(),alert(MONAL_LOCALIZED.texts.something_went_wrong)}))}))}function i(){const t=n(".monal__body");let e,o,i=0,s="",a="";function l(n){const t=o.find("label");"object"==typeof n&&void 0!==n.message?(t.removeClass("installing success error").addClass(n.message.toLowerCase()),void 0!==n.done&&n.done?c():void 0!==n.url?n.hash===a?(t.removeClass("installing success").addClass("error"),c()):(a=n.hash,jQuery.post(n.url,n,l).fail(l)):c()):d()}function d(){s&&(o.find("input:checkbox").is(":checked")?jQuery.post(MONAL_LOCALIZED.ajaxurl,{action:"monal_plugins",wpnonce:MONAL_LOCALIZED.wpnonce,slug:s},l).fail(l):(o.addClass("skipping"),setTimeout(c,300)))}function c(){o&&(o.data("done_item")||(i++,o.data("done_item",1)),o.find(".spinner").css("visibility","hidden"));const t=n(".monal__drawer--install-plugins li");t.each((function(){const t=n(this);return!!t.data("done_item")||(s=t.data("slug"),o=t,d(),!1)})),i>=t.length&&e()}return{init(o){n(".monal__drawer--install-plugins").addClass("installing"),n(".monal__drawer--install-plugins").find("input").prop("disabled",!0),e=function(){setTimeout((function(){n(".monal__body").addClass("js--finished")}),1e3),t.removeClass("monal__drawer--open"),setTimeout((function(){n(".monal__body").addClass("exiting")}),3e3),setTimeout((function(){window.location.href=o.href}),3500)},c()}}}function s(){const t=n(".monal__body");let e,o,i,s=0,a="",l="",d=1,c=0;function r(t){const e=o.find("label");"object"==typeof t&&void 0!==t.message?(e.addClass(t.message.toLowerCase()),void 0!==t.num_of_imported_posts&&0<c&&(d="all"===t.num_of_imported_posts?c:t.num_of_imported_posts,m()),void 0!==t.url?t.hash===l?(e.addClass("status--failed"),_()):(l=t.hash,void 0===t.selected_index&&(t.selected_index=n(".js-monal-demo-import-select").val()||0),jQuery.post(t.url,t,r).fail(r)):(t.done,_())):(e.addClass("status--error"),_())}function _(){let t=!1;o&&(o.data("done_item")||(s++,o.data("done_item",1)),o.find(".spinner").css("visibility","hidden"));const i=n(".monal__drawer--import-content__list-item");n(".monal__drawer--import-content__list-item input:checked"),i.each((function(){""===a||t?(a=n(this).data("content"),o=n(this),a&&(o.find("input:checkbox").is(":checked")?jQuery.post(MONAL_LOCALIZED.ajaxurl,{action:"monal_content",wpnonce:MONAL_LOCALIZED.wpnonce,content:a,selected_index:n(".js-monal-demo-import-select").val()||0},r).fail(r):(o.addClass("skipping"),setTimeout(_,300))),t=!1):n(this).data("content")===a&&(t=!0)})),s>=i.length&&e()}function m(){n(".js-monal-progress-bar").css("width",d/c*100+"%");const t=(e=d/c*100,0,99,Math.min(99,Math.max(0,e)));var e;n(".js-monal-progress-bar-percentage").html(Math.round(t)+"%"),1==d/c&&clearInterval(i)}return{init(o){n(".monal__drawer--import-content").addClass("installing"),n(".monal__drawer--import-content").find("input").prop("disabled",!0),e=function(){n.post(MONAL_LOCALIZED.ajaxurl,{action:"monal_import_finished",wpnonce:MONAL_LOCALIZED.wpnonce,selected_index:n(".js-monal-demo-import-select").val()||0}),setTimeout((function(){n(".js-monal-progress-bar-percentage").html("100%")}),100),setTimeout((function(){t.removeClass("monal__drawer--open")}),500),setTimeout((function(){n(".monal__body").addClass("js--finished")}),1500),setTimeout((function(){n(".monal__body").addClass("exiting")}),3400),setTimeout((function(){window.location.href=o.href}),4e3)},function(){if(!n(".monal__drawer--import-content__list-item .checkbox-content").is(":checked"))return!1;jQuery.post(MONAL_LOCALIZED.ajaxurl,{action:"monal_get_total_content_import_items",wpnonce:MONAL_LOCALIZED.wpnonce,selected_index:n(".js-monal-demo-import-select").val()||0},(function(n){c=n.data,0<c&&(m(),i=setInterval((function(){d+=c/500,m()}),1e3))}))}(),_()}}}return{init(){t=this,n(o)},callback(n){console.log(n),console.log(this)}}})(jQuery).init()})();