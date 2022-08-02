/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'domReady'
], function ($,domready) {
    'use strict';
    $.widget('mage.update_attachment', {
        options: {
            warningMessage: "Please fill the main product before selecting its attachments"
        },
        /**
         *
         * @private
         */
        _create: function () {
            var productId = this._getProductId();
            if(productId == ""){
                alert(this.options.warningMessage);
            }
            var self = this;
            this.initCustomPrice();
            $(this.element).find("td.col-action.col-edit").each(function(index){
                $(this).find("a").unbind("click").bind("click",function(event){
                    event.preventDefault();
                    event.stopPropagation();
                    self._updateAttachment(this);
                })
            });
        },
        _updateAttachment: function(object){
            var productId = this._getProductId();
            if(productId == ""){
                alert(this.options.warningMessage);
                return false;
            }
            this._lockMainProductForm();
            var attachmentId = this._getAttachmentId(object);
            var price = this._getPrice(object);
            var qty = this._getQty(object);
            var priceType = this._getPriceType(object);
            var action = "";

            if(this._getAction(object)){
                action = "update";
            }
            else{
                action = "remove";
            }
            $.ajax({
                url: this.options.url,
                data: {product_id: productId, product_attachment_id: attachmentId, price: price, qty: qty, price_type: priceType,'action':action},
                method: 'get',
                dataType: 'json',
                beforeSend: function () {
                    $(object).closest("td").find("div").remove();
                    $(object).hide();
                },
                success: function (res) {
                    $(object).show();
                    $(object).closest("td").append("<div style='font-weight: bold; margin-top:10px'>" + res.message + "</div>");
                }
            });
        },
        _getAction: function(object){
            return $(object).closest("tr").find(".col-in_products input").prop("checked");
        },
        _getProductId: function (){
            return $("#page_product_id").val();
        },
        _getAttachmentId: function(object){
            return $(object).closest("tr").find(".col-in_products input").val();
        },
        _getQty: function (object){
            return $(object).closest("tr").find(".col-qty input").val();
        },
        _checkCustomPrice: function(object){
            return $(object).closest("tr").find(".check_custom_price").prop('checked');
        },
        _getPrice: function(object){
            if(this._checkCustomPrice(object)){
                return this.options.nonPrice;
            }
            return $(object).closest("tr").find(".productPrice .price").val();
        },
        _getPriceType: function(object){
            if(this._checkCustomPrice(object)){
                return this.options.defaulPriceType;
            }
            else{
                return $(object).closest("tr").find(".productPrice .price_type").val();
            }
        },
        initCustomPrice: function(){
            $(".productattachment-pattachment-edit #productsGrid_table td").each(function(){
                $(this).not(".col-in_products").unbind("click").bind("click",function(event){
                    event.stopPropagation();
                });
            });

            $(".productPrice").each(function(){
                var boxchecked = $(this).find(".check_custom_price").prop('checked');
                if(boxchecked){
                    $(this).find(".price_type").attr("disabled", true);
                    $(this).find(".price").attr("disabled", true);
                }
                $(this).find(".check_custom_price").unbind("click").bind("click",function(){
                    if($(this).prop('checked')==true){
                        $(this).closest(".productPrice").find(".price_type").attr("disabled", true);
                        $(this).closest(".productPrice").find(".price").attr("disabled", true);
                    }
                    else{
                        $(this).closest(".productPrice").find(".price_type").attr("disabled", false);
                        $(this).closest(".productPrice").find(".price").attr("disabled", false);
                    }
                })
            });
        },
        _lockMainProductForm: function(){
            $("#page_sku").attr("disabled",true);
        }
    });
    return $.mage.update_attachment;
});
