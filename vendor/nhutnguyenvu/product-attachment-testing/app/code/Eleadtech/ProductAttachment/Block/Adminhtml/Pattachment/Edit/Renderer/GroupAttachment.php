<?php

namespace Eleadtech\ProductAttachment\Block\Adminhtml\Pattachment\Edit\Renderer;

use Magento\Framework\DataObject;

class GroupAttachment extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $attachment;
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Eleadtech\ProductAttachment\Service\Attachment $attachment,
        array $data = []
    )
    {
        $this->attachment = $attachment;
        parent::__construct($context, $data);
    }
    public function render(DataObject $row)
    {
        $attachments = $this->attachment->getAttachmentByProductId($row->getProductId());
        $string = "";
        if(!empty($attachments)){
            foreach ($attachments as $index =>$item){
                if($index != 0){
                    $string.= "<br><br>";
                }
                //$string .= __("<span>Sku: %1 </span> <span>Qty: %2 </span> <span>Price: %3 </span> <span>Price Type: %4 </span>", $item['sku'],$item['qty'],$customPrice,\Eleadtech\ProductAttachment\Model\PriceType::getOptionText($item['price_type']));
                $string .= __("<span>Sku: %1 </span> <span>Qty: %2 </span>", $item['sku'],$item['qty']);
            }
        }
        return $string;
    }

}
