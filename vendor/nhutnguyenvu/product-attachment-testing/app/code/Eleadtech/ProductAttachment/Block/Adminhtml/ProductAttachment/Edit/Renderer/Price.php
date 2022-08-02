<?php

namespace Eleadtech\ProductAttachment\Block\Adminhtml\ProductAttachment\Edit\Renderer;

use Magento\Framework\DataObject;

class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $attachmentFactory;
    protected $helper;
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Eleadtech\ProductAttachment\Model\PattachmentFactory $attachmentFactory,
        \Eleadtech\ProductAttachment\Helper\Data $helper,
        array $data = []
    )
    {
        $this->attachmentFactory = $attachmentFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }
    public function render(DataObject $row)
    {
        try{
            $productId = $this->getRequest()->getParam("product_id");
            $attachmentId = $row->getId();
            if(!empty($productId)){
                $attachmentData = $this->attachmentFactory->create()->getDataByProductIdAndAttachmentId($productId,$attachmentId);
                if(!empty($attachmentData)){
                    return $this->getPriceComponent($attachmentData['price'],$attachmentData['price_type'], $attachmentId);
                }
            }
            return $this->getDefaultPriceComponent($attachmentId);
        }
        catch (\Exception $ex){
            $this->helper->writeLog($ex->getMessage());
            return "";
        }
        return "";
    }
    protected function getDefaultPriceComponent($productAttachmentId){
        $html = "<div id='product$productAttachmentId' class='productPrice'>";
        $html .= "<input type='text' value='0' name='price_$productAttachmentId' class='price' disabled /> ";
        $html .= "<br><br>";
        $html .= $this->getPriceTypeComponent($productAttachmentId,-1, true);
        $html .= "<br><br>";
        $html .= __("Use Origin Price: ") . "<input type='checkbox' checked name='check_custom_price_$productAttachmentId' class='check_custom_price'/>";
        return $html . "</div>";
    }
    protected function getPriceComponent($price,$priceType,$productAttachmentId){
        $html = "<div id='product$productAttachmentId' class='productPrice'>";
        if($price !=\Eleadtech\ProductAttachment\Model\Pattachment::NONE_CUSTOM_PRICE){
            $html .= "<input type='text' value='$price' name='price_$productAttachmentId' class='price'/>";
            $html .= "<br><br>";
            $html .= $this->getPriceTypeComponent($productAttachmentId,$priceType);
            $html .= "<br><br>";
            $html .= __("Use Origin Price: ") . "<input type='checkbox' name='check_custom_price_$productAttachmentId' class='check_custom_price' /> ";

        }
        else{
            $html .= "<input type='text' value='0' name='price_$productAttachmentId' class='price' />";
            $html .= "<br><br>";
            $html .= $this->getPriceTypeComponent($productAttachmentId,$priceType);
            $html .= "<br><br>";
            $html .= __("Use Origin Price: ") . "<input type='checkbox' name='check_custom_price_$productAttachmentId' class='check_custom_price' checked />";
        }
        return $html . "</div>";
    }
    protected function getPriceTypeComponent($productAttachmentId,$currentPriceTye = -1, $disable=false){
        $priceTypes = \Eleadtech\ProductAttachment\Model\PriceType::getOptionArray();
        if($disable){
            $html = "<select name='price_type_$productAttachmentId' disabled class='price_type'>";
        }
        else{
            $html = "<select name='price_type_$productAttachmentId' class='price_type'>";
        }

        foreach ($priceTypes as $type => $label){
            if($type == $currentPriceTye){
                $html .= "<option value='$type' selected >$label</option>";
            }
            else{
                $html .= "<option value='$type'  >$label</option>";
            }
        }
        $html .= "</select>";
        return $html;
    }
}

