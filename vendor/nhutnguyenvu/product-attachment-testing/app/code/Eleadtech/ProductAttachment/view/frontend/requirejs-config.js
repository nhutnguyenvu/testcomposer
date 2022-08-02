var config = {
    'config': {
        'mixins': {
            'Magento_Checkout/js/view/minicart': {
                'Eleadtech_ProductAttachment/js/checkout/view/minicart': true
            }
        }
    },
    map: {
        '*': {
            'Magento_Checkout/template/minicart/item/default.html':  'Eleadtech_ProductAttachment/template/minicart/item/custom_default.html'
        }
    }
};
