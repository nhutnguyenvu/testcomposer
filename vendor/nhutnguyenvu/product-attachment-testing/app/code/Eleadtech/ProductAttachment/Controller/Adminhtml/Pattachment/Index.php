<?php

namespace Eleadtech\ProductAttachment\Controller\Adminhtml\Pattachment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPagee;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Eleadtech_ProductAttachment::pattachment');
        $resultPage->addBreadcrumb(__('Eleadtech'), __('Eleadtech'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Manage Attachments'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Attachments'));
        return $resultPage;
    }
}
?>
