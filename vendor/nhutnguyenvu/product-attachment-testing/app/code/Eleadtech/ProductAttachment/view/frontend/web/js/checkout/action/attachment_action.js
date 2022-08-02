/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($,customerData) {
    'use strict';
    $.widget('mage.attachment_process', {
        options: {
        },
        /**
         *
         * @private
         */
        _create: function () {
            this._processCartPage();
        },

        _processCartPage: function(){
            this._removeCartDeleteButton();
            this._processCartQtyTextbox();
            this._removeCartEditButton();
        },

        _getUrlParameters:function (url){
            try{
                var splits = url.split("/id/");
                var itemLink = splits[1];
                var splits = itemLink.split("/product_id/");
                var itemId = splits[0];
                return itemId;
            }
            catch(ex){
                console.log(ex);
            }
            return -1;
        },
        _removeCartEditButton: function(){
            try{
                var attachmentIds = this._getAttachmentIds();
                var self = this;
                $(this.options.cartSelector).find(".action.action-edit").each(function(){
                    if($(this).attr("href")){
                        var url = $(this).attr("href");
                        var itemId = self._getUrlParameters(url);
                        if(Array.isArray(attachmentIds)){
                            if(attachmentIds.includes(itemId)){
                                $(this).remove();
                            }
                        }
                    }
                });
            }
            catch(ex){
                console.log(ex);
            }
        },
        _removeCartDeleteButton: function(){
            try{
                var attachmentIds = this._getAttachmentIds();
                $(this.options.cartSelector).find(".action.action-delete").each(function(){
                    if($(this).attr("data-post")){
                        var postJson = $.parseJSON($(this).attr("data-post"));
                        var itemId = postJson.data.id;
                        if(Array.isArray(attachmentIds)){
                            if(attachmentIds.includes(itemId)){
                                $(this).remove();
                            }
                        }
                    }
                });
            }
            catch(ex){
                console.log(ex);
            }
        },
        _processCartQtyTextbox: function(){
            try{
                var attachmentIds = this._getAttachmentIds();
                $(this.options.cartSelector).find(".input-text.qty").each(function(){
                    if($(this).attr("id")){
                        var id = $(this).attr("id");
                        id = id.replace("cart-","");
                        var itemId = id.replace("-qty","");
                        if(Array.isArray(attachmentIds)){
                            if(attachmentIds.includes(itemId)){
                                $(this).prop("disabled",true);
                            }
                        }
                    }
                });
            }
            catch(ex){
                console.log(ex);
            }
        },
        _getAttachmentIds: function (){
            //console.log(this.options.attachmentIds);
            return this.options.attachmentIds;
            /*
            var attachment_section = customerData.get("attachment_section");
            return attachment_section().attachment_data;*/
        }
    });
    return $.mage.attachment_process;
});
