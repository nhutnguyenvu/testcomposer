<?php
namespace Eleadtech\ProductAttachment\Controller\Adminhtml\Pattachment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Save extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function standardizeData($data){
        var_dump($data);
        die;
        if(empty($data)){
            return false;
        }
        $standardizeData = [];
        $productId = @$data['product_id'];
        if(empty($productId)){
            return false;
        }
        $attachmentIds = explode("&",@$data["attachment"]);
        if(!empty($attachmentIds)){
            foreach ($attachmentIds as $attachmentId){
                if(isset($data["check_custom_price_$attachmentId"])){
                    $standardizeData [] = $this->createRecordData($productId,$attachmentId, 1 , $data["qty_$attachmentId"]);
                }
                else{
                    $standardizeData [] = $this->createRecordData($productId,$attachmentId,0 , $data["qty_$attachmentId"],@$data["price_$attachmentId"],@$data["price_type_$attachmentId"]);
                }
            }
            return $standardizeData;
        }
        return false;
    }
    protected function createRecordData($productId, $attachmentId, $checkCustomPrice, $qty, $price = \Eleadtech\ProductAttachment\Model\Pattachment::NONE_CUSTOM_PRICE, $priceType = "fixed"){
        $data = [];
        $data['product_id'] = $productId;
        $data['product_attachment_id'] = $attachmentId;
        $data["qty"] = $qty;
        if($checkCustomPrice){
            $data["price"] = \Eleadtech\ProductAttachment\Model\Pattachment::NONE_CUSTOM_PRICE;
            $data["price_type"] = "fixed";
        }
        else{
            $data["price"] = $price;
            $data["price_type"] = $priceType;
        }
        return $data;
    }
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $productId = @$data['product_id'];
        $data = $this->standardizeData($data);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data && $productId) {
            $serviceAttachment = $this->_objectManager->create('Eleadtech\ProductAttachment\Service\Attachment');
            try {
                $serviceAttachment->insertAttachmentData($data);
                $this->messageManager->addSuccess(__('The Data has been saved.'));
                //$this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['product_id' => $productId, '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Pattachment.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['product_id' => $this->getRequest()->getParam('product_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
