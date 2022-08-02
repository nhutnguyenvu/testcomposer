/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'domReady',
    'jquery/ui'
], function ($,domready) {
    'use strict';
    $.widget('mage.product_searching', {
        options: {
        },
        /**
         *
         * @private
         */
        _create: function () {
            var cache = {};
            var requestAjax = false;
            var obj = $(this.element);
            var self = this;
            var currentSku = "";
            obj.autocomplete({
                minLength: 2,
                source: function (request, response) {
                    var term = request.term;
                    var cache_key = obj.data('attr') + "_" + term;

                    if (cache_key in cache) {
                        response(cache[cache_key]);
                        return;
                    }
                    if (requestAjax) {
                        requestAjax.abort();
                    }
                    requestAjax = $.ajax({
                        url: self.options.url,
                        dataType: "json",
                        method: 'get',
                        data: {
                            term: term
                        },
                        beforeSend: function(){
                            obj.addClass("ui-autocomplete-loading");
                        },
                        success: function (result) {
                            if(result.error == 0){
                                response(result.data);
                                cache[cache_key] = result.data;
                                obj.removeClass('ui-autocomplete-loading');
                                if(result.data.length > 0){
                                    $(".ui-helper-hidden-accessible").hide();
                                }
                                else{
                                    $(".ui-helper-hidden-accessible").show();
                                }
                            }
                            obj.removeClass(".ui-autocomplete-loading");
                        },
                        error: function(){
                            obj.removeClass(".ui-autocomplete-loading");
                        }
                    });
                },
                select: function (event, ui) {
                    $("#sku-note").css("color","#303030");
                    $("[name='product_id']").val(ui.item.product_id);
                    currentSku = ui.item.value;
                },
                change: function (event, ui){
                    $("#sku-note").css("color","#303030");
                    if($("[name='sku']").val() != currentSku){
                        $("[name='product_id']").val("");
                        $("[name='sku']").val("");
                    }
                },
                open: function (event, ui){
                    $("#sku-note").css("color","#FFF");
                }
            });
        },

    });
    return $.mage.product_searching;
});
