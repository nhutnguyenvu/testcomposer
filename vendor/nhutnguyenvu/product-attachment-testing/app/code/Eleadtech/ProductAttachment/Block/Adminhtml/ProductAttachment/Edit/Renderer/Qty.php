<?php

namespace Eleadtech\ProductAttachment\Block\Adminhtml\ProductAttachment\Edit\Renderer;

use Magento\Framework\DataObject;

class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
                    $qty = $attachmentData['qty'];
                    return "<input type='text' name='qty_$attachmentId' value='$qty' class='qty' />";
                }
            }
            return "<input type='text' name='qty_$attachmentId' value='1'  class='qty' />";
        }
        catch (\Exception $ex){
            $this->helper->writeLog($ex->getMessage());
            return "<input type='text' name='qty_$attachmentId' value=''  class='qty' />";
        }
        return "<input type='text' name='qty_$attachmentId' value='' class='qty' />";
    }
}

