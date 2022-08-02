<?php
namespace Eleadtech\ProductAttachment\Block\Adminhtml\Pattachment;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Eleadtech\ProductAttachment\Model\pattachmentFactory
     */
    protected $_pattachmentFactory;

    /**
     * @var \Eleadtech\ProductAttachment\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Eleadtech\ProductAttachment\Model\pattachmentFactory $pattachmentFactory
     * @param \Eleadtech\ProductAttachment\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Eleadtech\ProductAttachment\Model\PattachmentFactory $PattachmentFactory,
        \Eleadtech\ProductAttachment\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_pattachmentFactory = $PattachmentFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('sku');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_pattachmentFactory->create()->getCollection();
        $collection->groupAttachmentProduct();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }


    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'sku',
            [
                'header' => __('Product SKU'),
                'type' => 'text',
                'index' => 'sku',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Product Name'),
                'type' => 'text',
                'index' => 'name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


        $this->addColumn(
            'group_attachment',
            [
                'header' => __('Attachments'),
                'index' => 'group_attachment',
                'renderer' => 'Eleadtech\ProductAttachment\Block\Adminhtml\Pattachment\Edit\Renderer\GroupAttachment',
                'filter' => false,
                'sortable' => false
            ]
        );

        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'entity_id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }


    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('product_id');
        //$this->getMassactionBlock()->setTemplate('Eleadtech_ProductAttachment::pattachment/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('pattachment');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('productattachment/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('productattachment/*/index', ['_current' => true]);
    }

    /**
     * @param \Eleadtech\ProductAttachment\Model\pattachment|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {

        return $this->getUrl(
            'productattachment/*/edit',
            ['product_id' => $row->getProductId()]
        );

    }



}
