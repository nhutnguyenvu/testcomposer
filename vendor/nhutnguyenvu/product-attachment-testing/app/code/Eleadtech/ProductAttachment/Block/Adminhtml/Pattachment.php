<?php

namespace Eleadtech\ProductAttachment\Block\Adminhtml;

class Pattachment extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'pattachment/pattachment.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context,array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'add_primary_new',
            [
                'label' => __('Add New'),
                'class' => 'primary',
                'onclick' => "setLocation('" . $this->_getCreateUrl() . "')",
            ]
        );
        
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Eleadtech\ProductAttachment\Block\Adminhtml\Pattachment\Grid', 'eleadtech.pattachment.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @return array
     */
    protected function _getAddButtonOptions()
    {

        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];

        return $splitButtonOptions;
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'productattachment/*/new'
        );
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
    public function getUpdateAttachmentUrl(){
        return $this->getUrl(
            'productattachment/*/updateAttachment'
        );
    }
}
