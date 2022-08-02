<?php

namespace Eleadtech\ProductAttachment\Block\Adminhtml\Pattachment\Edit\Tab;
use Magento\Framework\Exception\LocalizedException;

/**
 * Pattachment edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Eleadtech\ProductAttachment\Model\Status
     */
    protected $_status;
    protected $attachmentService;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Eleadtech\ProductAttachment\Model\Status $status,
        \Eleadtech\ProductAttachment\Service\Attachment $attachmentService,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->attachmentService = $attachmentService;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {

        /* @var $model \Eleadtech\ProductAttachment\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('pattachment');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Product')]);
        $fieldset->addField('product_id', 'hidden', ['name' => 'product_id']);
        $product = $this->attachmentService->loadProductByProductId($model->getProductId());
        /*
        if(!$product){
            throw new LocalizedException(
                __("Invalid Product"));
        }
        */
        if($product){
            $model->setData("sku",$product->getSku() . " ( " . $product->getName() ." )");
            $fieldset->addField(
                'sku',
                'label',
                [
                    'name' => 'sku',
                    'label' => __('Product:'),
                    'title' => __('Product'),
                    'disabled' => $isElementDisabled
                ]
            );
        }
        else{
            $model->setData("sku","");
            $fieldset->addField(
                'sku',
                'text',
                [
                    'name' => 'sku',
                    'label' => __('Product:'),
                    'title' => __('Product'),
                    'disabled' => $isElementDisabled,
                    'note' => __("Please fill product before selecting its attachments <br> You could not change the main product once you click 'Attachment Products' tab")
                ]
            );
        }

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Product');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
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
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
}
