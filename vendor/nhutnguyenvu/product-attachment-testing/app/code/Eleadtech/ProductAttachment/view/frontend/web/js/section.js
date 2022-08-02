define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
            //this.customsection = customerData.get('attachment_section');
            this.getAttachmentData();
        },
        getAttachmentData: function (){
            var attachmentData = customerData.get('attachment_section');
            console.log(attachmentData());
            if(attachmentData.attachment_data){

            }
            //var attachmentData
        }
    });
});
