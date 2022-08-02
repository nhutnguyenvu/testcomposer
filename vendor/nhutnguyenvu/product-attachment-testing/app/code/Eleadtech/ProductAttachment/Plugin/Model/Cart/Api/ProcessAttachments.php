<?php
namespace Eleadtech\ProductAttachment\Plugin\Model\Cart\Api;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor;

class ProcessAttachments {

    protected $attachmentService;
    protected $cartItemOptionsProcessor;
    public function __construct(
        \Eleadtech\ProductAttachment\Service\Attachment $attachmentService,
        CartItemOptionsProcessor $cartItemOptionsProcessor
    ){
        $this->attachmentService = $attachmentService;
        $this->cartItemOptionsProcessor = $cartItemOptionsProcessor;

    }
    protected function _createExtraInfo($cartItem){
        $extraInfo = "";
        $productOption = $cartItem->getProductOption();
        if($productOption){
            $extensionAttributes = $productOption->getExtensionAttributes();
            if($extensionAttributes){
                $options = $extensionAttributes->getConfigurableItemOptions();
                if(!empty($options)){
                    foreach ($options as $option){
                        $extraInfo.= $option['option_value'] . ",";
                    }
                }
            }
        }
        return trim($extraInfo,",");
    }
    public function aroundSave($subject, $process, $cartItem) {
        if(!$this->attachmentService->getHelper()->isEnabledApi()){
            return $process($cartItem);
        }
        if(empty($cartItem->getId())){
            return $this->_addItem($subject, $process, $cartItem);
        }
        return $this->_updateItem($subject, $process, $cartItem);
    }
    protected function _updateItem($subject, $process, $cartItem){
        $result = $process($cartItem);

        //$items = $this->attachmentService->getAllAttachmentItems($cartItem->getQuoteId());
        $quote = $this->attachmentService->loadActiveQuote($cartItem->getQuoteId());
        $parentItem = $quote->getItemById($cartItem->getId());
        if ($this->attachmentService->isParentAttachmentItem($parentItem)) {
            $correspondingAttachmentData = $this->attachmentService->getAllCorrespondingAttachmentItemsFromTheirParent($parentItem);
            if (!empty($correspondingAttachmentData)) {
                $itemRequest = [];
                foreach ($correspondingAttachmentData as $itemId => $qty) {
                    $item = $quote->getItemById($itemId);
                    $item->setQty($qty);
                }
                $quote->collectTotals()->save();
            }
        }
        $additionalOptions = $this->attachmentService->getAdditionalOptionValue($result);
        $attachmentReturn = $this->attachmentService->findAllAttachmentItemInfoFromTheirParent($result);
        $extensionAttributes = $result->getExtensionAttributes();
        if(!empty($additionalOptions)){
            if($extensionAttributes){
                $extensionAttributes->setAdditionalOptions($additionalOptions);
            }
        }
        if(!empty($attachmentReturn)){
            if($extensionAttributes){
                $extensionAttributes->setAttachments($attachmentReturn);
            }
        }
        return $result;
    }
    protected function _addItem($subject, $process, $cartItem){

        try{
            $extraInfo = $this->_createExtraInfo($cartItem);
            $sku = $cartItem->getSku();
            $product = $this->attachmentService->getProductRepository()->get($sku);
            $additionalOptions = $this->attachmentService->createAddtionalOptionForParentAttachment($product->getId(),$extraInfo);
            if(!empty($additionalOptions)){
                $product->addCustomOption('additional_options', $this->attachmentService->getSerializer()->serialize($additionalOptions));
            }
            $result = $process($cartItem);
            $attachmentInfo = $this->attachmentService->getAttachmentByProductId($product->getId());
            $additionalOptions = $this->attachmentService->getAdditionalOptionValue($result);
            if(!empty($additionalOptions)){
                $extensionAttributes = $result->getExtensionAttributes();
                if($extensionAttributes){
                    $extensionAttributes->setAdditionalOptions($additionalOptions);
                }
            }
            if($result && !empty($attachmentInfo)){
                $parentQty = $cartItem->getQty();
                $i = 0;
                try{
                    $attachmentReturn = [];
                    $i = 0;
                    $attachmentInfoSkuQty = [];
                    foreach ($attachmentInfo as $item) {
                        $parentSku = $product->getSku();
                        $this->_addToCart($item['product_attachment_id'], $parentQty * $item['qty'], $parentSku,$result,$cartItem);
                        $i++;
                        $attachmentInfoSkuQty[$item['sku']] = $item['qty'];
                    }
                    $attachmentReturn = $this->attachmentService->findAllAttachmentItemInfoFromTheirParent($result);
                    $extensionAttributes = $result->getExtensionAttributes();
                    if(!empty($extensionAttributes)) {
                        $attachmentReturnOriginalQty = [];
                        $k = 0;
                        if (!empty($attachmentReturn)){
                            foreach ($attachmentReturn as $itemReturn) {
                                $attachmentReturnOriginalQty[$k] = $itemReturn;
                                $attachmentReturnOriginalQty[$k]['qty'] = $attachmentInfoSkuQty[$itemReturn['sku']];
                                $k++;
                            }
                            $extensionAttributes->setAttachments($attachmentReturnOriginalQty);
                        }
                    }
                }
                catch (\Exception $ex){
                    $this->attachmentService->writeLog($ex->getMessage());
                    $result = $process($cartItem);
                }
            }
        }
        catch (\Exception $ex){
            $this->attachmentService->writeLog($ex->getMessage());
            $result = $process($cartItem);
        }
        return $result;
    }
    protected function _addToCart($productId, $qty=1, $parentSku,$quoteItem, $cartItem){
        $product = $this->attachmentService->getProductRepository()->getById($productId);
        $params['qty'] = $qty;
        $cartId = $cartItem->getQuoteId();
        $quote = $this->attachmentService->getCart()->getQuote()->loadActive($cartId);
        if($product){
            $additionalOptions = $this->attachmentService->createAddtionalOptionForProductAttachment($parentSku ,$quoteItem);
            $product->addCustomOption('additional_options', $this->attachmentService->getSerializer()->serialize($additionalOptions));
            $quote->addProduct($product, $qty);
            $quote->collectTotals();
            $quote->save();
        }
    }

    public function aroundGetList($subject,$process,$cartId){
        if(!$this->attachmentService->getHelper()->isEnabledApi()){
            return $process($cartId);
        }
        $output = [];
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->attachmentService->loadActiveQuoteByQuoteId($cartId);

        /** @var  \Magento\Quote\Model\Quote\Item  $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $item = $this->cartItemOptionsProcessor->addProductOptions($item->getProductType(), $item);
            $extensionAttributes = $item->getExtensionAttributes();
            if($extensionAttributes){
                if(!empty($extensionAttributes)) {
                    $extensionAttributes->setAdditionalOptions($this->attachmentService->getAdditionalOptionValue($item));
                    $attachmentReturn = $this->attachmentService->findAllAttachmentItemInfoFromTheirParent($item);
                    if(!empty($attachmentReturn)){
                        $extensionAttributes->setAttachments($attachmentReturn);
                    }
                }
            }
            $output[] = $this->cartItemOptionsProcessor->applyCustomOptions($item);
        }

        return $output;
    }
}
