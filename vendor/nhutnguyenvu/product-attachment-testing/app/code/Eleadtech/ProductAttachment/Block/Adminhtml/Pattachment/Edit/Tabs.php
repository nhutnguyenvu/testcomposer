<?php
namespace Eleadtech\ProductAttachment\Block\Adminhtml\Pattachment\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pattachment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Information'));
    }
    protected function _prepareLayout()
    {

        $this->addTab(
            'attachment', [
                'label' => __('Attachment Products'),
                'url' => $this->getUrl('*/*/loadserializerattachment', ['_current' => true]),
                'class' => 'ajax',
                'after' => "main_section"
            ]
        );
        return parent::_prepareLayout();
    }
}
