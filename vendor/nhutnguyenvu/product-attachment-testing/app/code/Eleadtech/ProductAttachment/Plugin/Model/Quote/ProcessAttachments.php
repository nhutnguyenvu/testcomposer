<?php
namespace Eleadtech\ProductAttachment\Plugin\Model\Quote;

class ProcessAttachments {

    protected $attachmentService;
    protected $checkoutSession;
    public function __construct(
        \Eleadtech\ProductAttachment\Service\Attachment $attachmentService,
        \Magento\Checkout\Model\Session $checkoutSession
    ){
        $this->attachmentService = $attachmentService;
        $this->checkoutSession = $checkoutSession;
    }
    public function aroundRemoveItem($subject, $process, $itemId) {
        if(empty($this->attachmentService->getHelper()->isEnabled())){
            return $process($itemId);
        }
        $item = $subject->getItemById($itemId);

        if($item){
            if(!$this->attachmentService->allowToRemoveItem($item)){
                $this->attachmentService->showError("You could not remove this item");
            }
        }
        $result = $process($itemId);
        if(!$this->removeAttachmentProduct($item,$subject)){
            $parentId = $item->getParentItemId();
            if(!empty($parentId)){
                $parentItem = $subject->getItemById($parentId);
                $this->removeAttachmentProduct($parentItem,$subject);
            }
        }
        return $result;
    }

    protected function removeAttachmentProduct($quoteItem,$currentQuote){

        if($quoteItem){
            $isParentAttachmentItem = $this->attachmentService->isParentAttachmentItem($quoteItem);
            if(!empty($isParentAttachmentItem)){
                try {
                    $this->attachmentService->deleteAttachmentItem($quoteItem,$currentQuote);
                }
                catch (\Exception $ex){
                    $this->attachmentService->writeLog($ex->getMessage());
                }
                return true;
            }
        }
        return false;
    }

    public function beforeAddProduct($subject,\Magento\Catalog\Model\Product $product, $request = null, $processMode=\Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL) {
        if(empty($this->allowToAddAttachments())){
            return [$product, $request, $processMode];
        }
        $extraInfo = "";
        if(isset($request['super_attribute'])){
            if(is_array($request['super_attribute'])){
                $extraInfo = implode(",",$request['super_attribute']);
            }
            else{
                $extraInfo = $request['super_attribute'];
            }
        }
        $additionalOptions = $this->attachmentService->createAddtionalOptionForParentAttachment($product->getId(),$extraInfo);
        if(!empty($additionalOptions)){
            $product->addCustomOption('additional_options', $this->attachmentService->getSerializer()->serialize($additionalOptions));
        }
        return [$product, $request, $processMode];
    }
    public function afterAddProduct($subject,$parentItem) {

        if(empty($this->allowToAddAttachments())){
            return $parentItem;
        }
        $product = $parentItem->getProduct();
        $parentQty = $parentItem->getQtyToAdd();

        if(!empty($parentQty)){
            $attachmentInfo = $this->attachmentService->getAttachmentByProductId($product->getId());
            if(!empty($attachmentInfo)){
                $additionalOptions = [];
                try {
                    foreach ($attachmentInfo as $item) {
                        $parentSku = $product->getSku();
                        $this->_addToCart($item['product_attachment_id'], $parentQty * $item['qty'], $parentSku,$parentItem);
                    }
                }
                catch (\Exception $ex){
                    $this->attachmentService->writeLog($ex->getMessage());
                }
            }
        }
        return $parentItem;
    }

    protected function _addToCart($productId, $qty=1, $parentSku,$quoteItem){
        $product = $this->attachmentService->loadProductByProductId($productId);
        if($product){
            $additionalOptions = $this->attachmentService->createAddtionalOptionForProductAttachment($parentSku ,$quoteItem);
            $product->addCustomOption('additional_options', $this->attachmentService->getSerializer()->serialize($additionalOptions));
            $this->checkoutSession->setNoneProcessAttachtment(true);
            $this->attachmentService->getCart()->getQuote()->addProduct($product,$qty);
        }
    }
    protected function allowToAddAttachments(){
        if(empty($this->attachmentService->getHelper()->isEnabled())){
            return false;
        }
        if(empty($this->attachmentService->getHelper()->isFrontend())){
            return false;
        }
        if(!empty($this->checkoutSession->getNoneProcessAttachtment())){
            $this->checkoutSession->unsNoneProcessAttachtment();
            return false;
        }
        return true;
    }
}
