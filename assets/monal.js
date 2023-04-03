(()=>{"use strict";(function(e){let n;const t={install_plugins(e){(new s).init(e)},install_content(e){(new i).init(e)}};function o(){const n=e(".monal__body"),o=e("#monal__drawer-trigger"),s="monal__drawer--open";setTimeout((function(){n.addClass("loaded")}),100),o.on("click",(function(){n.toggleClass(s)})),e(".monal__button--proceed:not(.monal__button--closer)").on("click",(function(e){e.preventDefault();const t=this.getAttribute("href");n.addClass("exiting"),setTimeout((function(){window.location=t}),400)})),e(".monal__button--closer").on("click",(function(e){n.removeClass(s),e.preventDefault();const t=this.getAttribute("href");setTimeout((function(){n.addClass("exiting")}),600),setTimeout((function(){window.location=t}),1100)})),e(".button-next").on("click",(function(n){if(n.preventDefault(),!function(e){const n=jQuery(e);return"yes"!==n.data("done-loading")&&(n.data("done-loading","yes"),n.addClass("monal__button--loading"),{done(){n.attr("disabled",!1)}})}(this))return!1;const o=e(this).data("callback");return!o||void 0===t[o]||(t[o](this),!1)})),e(document).on("change",".js-monal-demo-import-select",(function(){const n=e(this).val(),t=MONAL_LOCALIZED.base_url+"/assets/img/no-preview.png";let o=MONAL_LOCALIZED.import_files[n].import_preview_image_url;void 0!==o&&""!==o||(o=t);const s=e(".js-content-preview-billboard").find("img");s.fadeTo(100,.3,(function(){s.attr("src",o)})).fadeTo(300,1),e(".js-monal-select-spinner").show(),e.post(MONAL_LOCALIZED.ajaxurl,{action:"monal_update_selected_import_data_info",wpnonce:MONAL_LOCALIZED.wpnonce,selected_index:n},(function(n){n.success?e(".js-monal-drawer-import-content").html(n.data):alert(MONAL_LOCALIZED.texts.something_went_wrong),e(".js-monal-select-spinner").hide()})).fail((function(){e(".js-monal-select-spinner").hide(),alert(MONAL_LOCALIZED.texts.something_went_wrong)}))}));const i=function(n){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"success";const o=e(".monal__content-billboard").hide();o.removeClass("monal__content-billboard-success").remove("monal__content-billboard-error"),o.addClass(`monal__content-billboard-${t}`),o.find(".monal__content-billboard-text").text(n),o.fadeIn()};e(".js-monal-freemius-activate-button").on("click",(function(n){n.preventDefault();const t=e(this),o=e(this).data("module-id");if(0===e(".js-freemius-license-key").val().length)return void i("Please enter license key.","error");const s={action:`fs_activate_license_${o}`,security:MONAL_LOCALIZED.freemius_security,license_key:e(".js-freemius-license-key").val(),module_id:o};e.ajax({type:"POST",url:MONAL_LOCALIZED.freemius_ajaxurl,data:s,dataType:"json",beforeSend:function(){t.addClass("monal__button--loading")},success:function(e){!0===e.success?i("Theme activated successfully","success"):i(e.error,"error")},error:function(){i("Some error occurred.","error")},complete:function(){t.removeClass("monal__button--loading")}})}))}function s(){const n=e(".monal__body");let t,o,s=0,i="",a="";function l(e){const n=o.find("label");"object"==typeof e&&void 0!==e.message?(n.removeClass("installing success error").addClass(e.message.toLowerCase()),void 0!==e.done&&e.done?r():void 0!==e.url?e.hash===a?(n.removeClass("installing success").addClass("error"),r()):(a=e.hash,jQuery.post(e.url,e,l).fail(l)):r()):c()}function c(){i&&(o.find("input:checkbox").is(":checked")?jQuery.post(MONAL_LOCALIZED.ajaxurl,{action:"monal_plugins",wpnonce:MONAL_LOCALIZED.wpnonce,slug:i},l).fail(l):(o.addClass("skipping"),setTimeout(r,300)))}function r(){o&&(o.data("done_item")||(s++,o.data("done_item",1)),o.find(".spinner").css("visibility","hidden"));const n=e(".monal__drawer--install-plugins li");n.each((function(){const n=e(this);return!!n.data("done_item")||(i=n.data("slug"),o=n,c(),!1)})),s>=n.length&&t()}return{init(o){e(".monal__drawer--install-plugins").addClass("installing"),e(".monal__drawer--install-plugins").find("input").prop("disabled",!0),t=function(){setTimeout((function(){e(".monal__body").addClass("js--finished")}),1e3),n.removeClass("monal__drawer--open"),setTimeout((function(){e(".monal__body").addClass("exiting")}),3e3),setTimeout((function(){window.location.href=o.href}),3500)},r()}}}function i(){const n=e(".monal__body");let t,o,s,i=0,a="",l="",c=1,r=0;function d(n){const t=o.find("label");"object"==typeof n&&void 0!==n.message?(t.addClass(n.message.toLowerCase()),void 0!==n.num_of_imported_posts&&0<r&&(c="all"===n.num_of_imported_posts?r:n.num_of_imported_posts,_()),void 0!==n.url?n.hash===l?(t.addClass("status--failed"),u()):(l=n.hash,void 0===n.selected_index&&(n.selected_index=e(".js-monal-demo-import-select").val()||0),jQuery.post(n.url,n,d).fail(d)):(n.done,u())):(t.addClass("status--error"),u())}function u(){let n=!1;o&&(o.data("done_item")||(i++,o.data("done_item",1)),o.find(".spinner").css("visibility","hidden"));const s=e(".monal__drawer--import-content__list-item");s.each((function(){""===a||n?(a=e(this).data("content"),o=e(this),a&&(o.find("input:checkbox").is(":checked")?jQuery.post(MONAL_LOCALIZED.ajaxurl,{action:"monal_content",wpnonce:MONAL_LOCALIZED.wpnonce,content:a,selected_index:e(".js-monal-demo-import-select").val()||0},d).fail(d):(o.addClass("skipping"),setTimeout(u,300))),n=!1):e(this).data("content")===a&&(n=!0)})),i>=s.length&&t()}function _(){e(".js-monal-progress-bar").css("width",c/r*100+"%");const n=(t=c/r*100,0,99,Math.min(99,Math.max(0,t)));var t;e(".js-monal-progress-bar-percentage").html(Math.round(n)+"%"),1==c/r&&clearInterval(s)}return{init(o){e(".monal__drawer--import-content").addClass("installing"),e(".monal__drawer--import-content").find("input").prop("disabled",!0),t=function(){e.post(MONAL_LOCALIZED.ajaxurl,{action:"monal_import_finished",wpnonce:MONAL_LOCALIZED.wpnonce,selected_index:e(".js-monal-demo-import-select").val()||0}),setTimeout((function(){e(".js-monal-progress-bar-percentage").html("100%")}),100),setTimeout((function(){n.removeClass("monal__drawer--open")}),500),setTimeout((function(){e(".monal__body").addClass("js--finished")}),1500),setTimeout((function(){e(".monal__body").addClass("exiting")}),3400),setTimeout((function(){window.location.href=o.href}),4e3)},function(){if(!e(".monal__drawer--import-content__list-item .checkbox-content").is(":checked"))return!1;jQuery.post(MONAL_LOCALIZED.ajaxurl,{action:"monal_get_total_content_import_items",wpnonce:MONAL_LOCALIZED.wpnonce,selected_index:e(".js-monal-demo-import-select").val()||0},(function(e){r=e.data,0<r&&(_(),s=setInterval((function(){c+=r/500,_()}),1e3))}))}(),u()}}}return{init(){n=this,e(o)},callback(e){console.log(e),console.log(this)}}})(jQuery).init()})();