define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';
    return function (Component) {
        return Component.extend({
            /**
             * @override
             */
            initialize: function () {
                //var cartData = customerData.get('cart');
                var self = this;
                return this._super();
            },
            isAttachmentItem(itemId){
                var attachmentSection = customerData.get('attachment_section');
                if(attachmentSection){
                    var attachmentIds = attachmentSection().attachment_data;
                    if(Array.isArray(attachmentIds)){
                        if(attachmentIds.includes(itemId)){
                            return true;
                        }
                        return false;
                    }
                }
                return false;
            }
        });
    }
});
