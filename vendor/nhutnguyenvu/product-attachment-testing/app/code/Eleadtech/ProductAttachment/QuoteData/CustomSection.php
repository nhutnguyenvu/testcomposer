<?php
namespace Eleadtech\ProductAttachment\QuoteData;
use Magento\Customer\CustomerData\SectionSourceInterface;

class CustomSection implements SectionSourceInterface
{
    protected  $cart;
    protected  $serializer;
    public function __construct(
       \Magento\Checkout\Model\Cart $cart,
       \Magento\Framework\Serialize\SerializerInterface $serializer
    ){
        $this->serializer = $serializer;
        $this->cart = $cart;
    }

    public function getSectionData()
    {
        return [
            'attachment_data' => $this->getAllAttachmentItems()
        ];
    }

    public function getAllAttachmentItems(){
        $items = $this->cart->getQuote()->getAllVisibleItems();
        if($items){
            $itemIds = [];
            foreach ($items as $item){
                $options = $item->getOptionByCode('additional_options');
                if($options){
                    $optionsData = $options->getData();
                    if(isset($optionsData['value'])){
                        $valueData = $this->serializer->unserialize($optionsData['value']);
                        foreach ($valueData as $value){
                            if(isset($value['is_attachment'])){
                                $itemIds[] = $optionsData['item_id'];
                                break;
                            }
                        }
                    }
                }
            }
        }
        if(empty($itemIds)){
            return false;
        }
        return $itemIds;
    }
}
