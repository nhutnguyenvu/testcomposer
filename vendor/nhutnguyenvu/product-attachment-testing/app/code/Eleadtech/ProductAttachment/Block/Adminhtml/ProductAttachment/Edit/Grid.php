<?php
namespace Eleadtech\ProductAttachment\Block\Adminhtml\ProductAttachment\Edit;

use Eleadtech\ProductAttachment\Model\PattachmentFactory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Contact factory
     *
     * @var ContactFactory
     */
    protected $pattachmentFactory;

    /**
     * @var  \Magento\Framework\Registry
     */
    protected $registry;

    protected $_objectManager = null;
    protected $_helper;
    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $registry
     * @param ContactFactory $attachmentFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        PattachmentFactory $pattachment,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Eleadtech\ProductAttachment\Helper\Data $helper,
        array $data = []
    ) {
        $this->pattachmentFactory = $pattachment;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->_helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('in_products');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);

        if ($this->getRequest()->getParam('product_id')) {
            $this->setDefaultFilter(array('in_products' => 1));
        }
    }

    public function getTabLabel()
    {
        return __('Assigned Products');
    }
    /**
     * add Column Filter To Collection
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getAttachmentProducts();

            if (empty($productIds)) {
                return $this;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToFilter("type_id","simple");
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('status');
        $collection->addAttributeToSelect('visibility');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        /* @var $model \Webspeaks\ProductsGrid\Model\Slide */
        //$model = $this->_objectManager->get('\Eleadtech\Resources\Model\Resource');

        $this->addColumn(
            'in_products',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'align' => 'center',
                'index' => 'entity_id',
                'field_name' => 'items[]',
                'values' => $this->_getAttachmentProducts()
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        /*
        $this->addColumn(
            'type_id',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        */
        $this->addColumn('qty', array(
            'header' => __('Qty'),
            'type' => 'text',
            'sortable' => false,
            /*'options' => $this->helper->getAllBrandOptions(),*/
            /*'filter_condition_callback' => array($this, '_filterBrandCondition'),*/
            'renderer' => 'Eleadtech\ProductAttachment\Block\Adminhtml\ProductAttachment\Edit\Renderer\Qty',
            'filter' => false,

        ));

        /*
        $this->addColumn('price', array(
            'header' => __('Custom Price'),
            'type' => 'text',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Eleadtech\ProductAttachment\Block\Adminhtml\ProductAttachment\Edit\Renderer\Price'

        ));
        */
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'class' => 'xxx',
                'type' => 'options',
                'width' => '50px',
                'options' => \Magento\Catalog\Model\Product\Attribute\Source\Status::getOptionArray()
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'class' => 'xxx',
                'type' => 'options',
                'width' => '50px',
                'options' => \Magento\Catalog\Model\Product\Visibility::getOptionArray()

            ]
        );
        $this->addColumn(
        'edit',
                [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                [
                    'caption' => __('Click Here To Update Change'),
                    'url' => "javascript:void(0)",
                    'field' => 'attachment_id'
                ]
            ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
        return parent::_prepareColumns();
    }
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/loadattachmentgrid', ['_current' => true]);
    }

    /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }

    protected function _getAttachmentProducts()
    {
        $productId = $this->getRequest()->getParam("product_id");
        $attachmentIds = $this->pattachmentFactory->create()->getAttachmentIdsByProductId($productId);
        return $attachmentIds;

    }

    public function getAttachmentProducts()
    {
        return $this->_getAttachmentProducts();
    }
    public function getRowClickCallback()
    {
        return false;
    }
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }
    protected function _prepareMassaction()
    {
        return $this;
    }

}
