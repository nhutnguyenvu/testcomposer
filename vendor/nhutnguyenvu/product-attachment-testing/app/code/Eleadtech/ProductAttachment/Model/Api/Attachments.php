<?php
namespace Eleadtech\ProductAttachment\Model\Api;


class Attachments {

    /**
     * {@inheritdoc}
     */
    protected $attachmentService;
    protected $attachment;
    protected $infoFactory;
    protected $message = [];

    public function __construct(
        \Eleadtech\ProductAttachment\Service\Attachment $attachmentService,
        \Eleadtech\ProductAttachment\Data\AttachmentsInterface $attachment,
        \Eleadtech\ProductAttachment\Model\InfoFactory $infoFactory
    )
    {
        $this->attachmentService = $attachmentService;
        $this->attachment = $attachment;
        $this->infoFactory = $infoFactory;
        $this->message  = [0=>(string)__("Please enable your API")];
    }

    public function getAttachments($sku)
    {
        if(!$this->attachmentService->getHelper()->isEnabledApi()){
            return $this->message;
        }
        try{
            $product = $this->attachmentService->getProductRepository()->get($sku);
            $attachments = $this->attachmentService->getAttachmentByProductId($product->getId());
            $info = [];
            $i =  0;
            foreach ($attachments as $attachment){
                $info[$i]['sku'] = $attachment['sku'];
                $info[$i]['qty'] = $attachment['qty'];
            }
            return $info;
        }
        catch (\Exception $ex){
            $this->attachmentService->writeLog($ex->getMessage());
        }
        return [];
    }
    public function getAttachmentList($skus){
        if(!$this->attachmentService->getHelper()->isEnabledApi()){
            return $this->message;
        }
        if(!is_array($skus)) {
            $skuList[] = $skus;
        }
        else{
            $skuList = $skus;
        }
        $infoObj = [];
        foreach ($skuList as $sku){
            try{
                $product = $this->attachmentService->getProductRepository()->get($sku);
                $attachments = $this->attachmentService->getAttachmentByProductId($product->getId());

                $info = [];
                $i =  0;
                foreach ($attachments as $attachment){
                    $info[$i]['sku'] = $attachment['sku'];
                    $info[$i]['qty'] = $attachment['qty'];
                    $i++;
                }
                $infoObj[] = $this->infoFactory->create()
                    ->setData(\Eleadtech\ProductAttachment\Data\AttachmentsInterface\InfoInterface::KEY_INPUT_SKU,$sku)
                    ->setData(\Eleadtech\ProductAttachment\Data\AttachmentsInterface\InfoInterface::KEY_ATTACHMENTS,$info);
            }
            catch (\Exception $ex){
                $this->attachmentService->writeLog($ex->getMessage());
            }
        }
        $this->attachment->setAttachmentsInfo($infoObj);
        return $this->attachment;
    }

    public function getAllItemIdsAttachments($cartId){
        if(!$this->attachmentService->getHelper()->isEnabledApi()){
            return $this->message;
        }
        return $this->attachmentService->getAllAttachmentItems($cartId);
    }
}
