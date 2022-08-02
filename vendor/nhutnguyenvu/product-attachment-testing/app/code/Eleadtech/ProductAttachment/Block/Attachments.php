<?php

namespace Eleadtech\ProductAttachment\Block;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Helper\Image;

class Attachments  extends  \Magento\Framework\View\Element\Template
{
    protected $context;
    protected $helper;
    protected $attachmentService;
    protected $priceCurrency;
    protected $imageHelper;

    public function __construct(
        Context $context,
        \Eleadtech\ProductAttachment\Helper\Data $helper,
        \Eleadtech\ProductAttachment\Service\Attachment $attachmentService,
        PriceCurrencyInterface $priceCurrency,
        Image $imageHelper

    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->helper = $helper;
        $this->attachmentService = $attachmentService;
        $this->priceCurrency =  $priceCurrency;
        $this->imageHelper = $imageHelper;
    }
    public function getProductId(){
        return $this->getRequest()->getParam("id");
    }
    public function getAttachments(){
        $productId = $this->getProductId();
        $attachmentInfor = $this->attachmentService->getAttachmentByProductId($productId);
        $mainProduct = $this->attachmentService->loadProductByProductId($productId);
        $mainPrice = $mainProduct->getFinalPrice();
        $attachmentData = [];
        if(!empty($attachmentInfor)){
            $i = 0;
            foreach ($attachmentInfor as $item){
                $attachmentData[$i] = $item;
                $attachmentProduct = $this->attachmentService->loadProductByProductId($item['product_attachment_id']);
                $attachmentData[$i]['name'] = $attachmentProduct->getData("name");
                $attachmentData[$i]['image'] = $this->imageHelper->init($attachmentProduct,"product_thumbnail_image")->getUrl();
                //$estimatePrice = $this->attachment->estimateAttrachmentProductPriceByDetail($item['price'],$attachmentProduct->getFinalPrice(),$item['price_type'],$mainPrice);
                /*$attachmentData[$i]['estimated_price'] = $this->getPriceComponent($estimatePrice);*/
                $i++;
            }
        }

        return $attachmentData;
    }
    public function getImageLinkByProductId($attachmentId){
        $attachmentProduct = $this->attachmentService->getAttachmentByProductId($attachmentId);
        return $attachmentProduct->getData('thumbnail');
    }
    public function getHelper(){
        return $this->helper;
    }
    public function getPriceComponent($price){
        if($price == 0){
            return __("Free");
        }
        return $this->priceCurrency->format($price,true);
    }
    public function canShow(){
        return $this->helper->isEnabled();
    }
    public function getAttachmentMessage(){
        return $this->helper->getAttachmentMessage();
    }
    public function getAllAttachmentItems(){
        return json_encode($this->attachmentService->getAllAttachmentItems());
    }
}

