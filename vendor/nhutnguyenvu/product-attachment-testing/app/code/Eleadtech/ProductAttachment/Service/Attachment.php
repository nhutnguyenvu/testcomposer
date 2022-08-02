<?php
namespace Eleadtech\ProductAttachment\Service;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;

class Attachment
{
    protected $connection;
    protected $productRepository;
    protected $pattachmentFactory;
    protected $serializer;
    protected $cart;
    protected $helper;
    protected $quoteRepository;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Eleadtech\ProductAttachment\Model\PattachmentFactory $pattachmentFactory,
        SerializerInterface $serializer,
        \Magento\Checkout\Model\Cart $cart,
        \Eleadtech\ProductAttachment\Helper\Data $helper,
        CartRepositoryInterface $quoteRepository

    ){
        $this->connection = $resource->getConnection();
        $this->productRepository = $productRepository;
        $this->pattachmentFactory = $pattachmentFactory;
        $this->serializer = $serializer;
        $this->cart = $cart;
        $this->helper = $helper;
        $this->quoteRepository = $quoteRepository;
    }
    public function getAttachmentByProductId($productId){
        $select = $this->connection->select();
        $select->from("product_attachment")->join(["product"=>"catalog_product_entity"],"product.entity_id=product_attachment.product_attachment_id AND product_attachment.product_id= $productId",["sku"]);
        $result = $this->connection->fetchAll($select);
        if(empty($result)){
            return false;
        }
        return $result;
    }
    public function loadProductByProductId($productId){
        try{
            return $this->productRepository->getById($productId);
        }
        catch(\Exception $ex){
            $this->writeLog($ex->getMessage());
        }
        return false;
    }
    public function insertAttachmentData($attachmentData){
        if(empty($attachmentData)){
            return false;
        }
        $table = $this->connection->getTableName("product_attachment");
        $this->connection->beginTransaction();
        foreach ($attachmentData  as $record){
            try{
                $select = $this->connection->select();
                $select->from($table)->where("product_id = ?",$record["product_id"])
                    ->where("product_attachment_id = ?",$record["product_attachment_id"]);
                $result = $this->connection->fetchRow($select);
                if(empty($result)){
                    $this->connection->insert($table, $record);
                }
                else{
                    $where = ['product_id = ?' => $record['product_id'],'product_attachment_id = ?' => $record['product_attachment_id']];
                    $this->connection->update($table,$record,$where);
                }
            }
            catch(\Exception $ex){
                $this->writeLog($ex->getMessage());
                $this->connection->rollBack();
                return false;
            }
        }
        $this->connection->commit();
        return true;
    }
    public function getProductIdList(){
        $table = $this->connection->getTableName("product_attachment");
        $select = $this->connection->select();
        $select->distinct()->from($table)->reset("columns")->columns(['product_id']);
        return $this->connection->fetchCol($select);
    }

    protected function _estimateProductAttachmentPrice($databasePrice,$priceType=\Eleadtech\ProductAttachment\Model\PriceType::FIXED,$mainPrice=false){
        if($databasePrice == \Eleadtech\ProductAttachment\Model\Pattachment::NONE_CUSTOM_PRICE){
            return \Eleadtech\ProductAttachment\Model\Pattachment::NONE_CUSTOM_PRICE;
        }
        elseif($databasePrice == 0){
            return 0;
        }
        elseif($priceType == \Eleadtech\ProductAttachment\Model\PriceType::FIXED){
            return $databasePrice;
        }
        elseif($priceType == \Eleadtech\ProductAttachment\Model\PriceType::PERCENTAGE){
            return $mainPrice * ($databasePrice/100);
        }
        return \Eleadtech\ProductAttachment\Model\Pattachment::NONE_CUSTOM_PRICE;
    }

    public function estimateAttrachmentProductPriceByDetail($databaseAttachmentPrice,$attachmentPrice,$priceType,$mainPrice){
        $preEstimatePrice = $this->_estimateProductAttachmentPrice($databaseAttachmentPrice,$priceType,$mainPrice);
        if($preEstimatePrice == \Eleadtech\ProductAttachment\Model\Pattachment::NONE_CUSTOM_PRICE){
            return $attachmentPrice;
        }
        return $preEstimatePrice;
    }
    public function estimateAttrachmentProductPrice($mainProductId, $attachmentProductId){
        try{
            $mainProduct = $this->loadProductByProductId($mainProductId);
            $attachmentProduct = $this->loadProductByProductId($attachmentProductId);
            $attachmentData = $this->pattachmentFactory->create()->getDataByProductIdAndAttachmentId($mainProductId,$attachmentProductId);
            $preEstimatePrice = $this->_estimateProductAttachmentPrice($attachmentData['price'],$attachmentData['price_type'],$mainProduct->getFinalPrice());
            if($preEstimatePrice == \Eleadtech\ProductAttachment\Model\Pattachment::NONE_CUSTOM_PRICE){
                return $attachmentData->getFinalPrice();
            }
            return $preEstimatePrice;
        }
        catch (\Exception $ex){
            $this->writeLog($ex->getMessage());
        }
    }

    public function writeLog($message){
        $this->helper->writeLog($message);
    }
    public function getHelper(){
        return $this->helper;
    }
    public function isParentAttachmentItem($item){
        $valueData = $this->getItemOptionValue($item);
        if(empty($valueData)){
            return false;
        }
        foreach ($valueData as $value){
            if(isset($value['is_parent_attachment'])){
                return true;
            }
        }
        return false;
    }
    public function getAdditionalOptionValue($item){
        return $this->getItemOptionValue($item);
    }
    protected function getItemOptionValue($item){
        $options = $item->getOptionByCode('additional_options');
        $valueData = [];
        if($options) {
            $optionsData = $options->getData();
            if (isset($optionsData['value'])) {
                $valueData = $this->serializer->unserialize($optionsData['value']);
            }
        }
        return $valueData;
    }

    public function allowToRemoveItem($item){
        $valueData = $this->getItemOptionValue($item);
        if(empty($valueData)){
            return true;
        }
        foreach ($valueData as $value){
            if(isset($value['is_attachment'])){
                $parentItem = $this->findParentAttachmentItem($item);
                if($parentItem){
                    return false;
                }
            }
        }
        return true;
    }
    public function isAttachmentItem($item){
        $valueData = $this->getItemOptionValue($item);
        if(empty($valueData)){
            return false;
        }
        foreach ($valueData as $value){
            if(isset($value['is_attachment'])){
                return true;
            }
        }
        return false;
    }
    public function findParentAttachmentItem($attachmentItem){
        $items = $this->getAllVisibleItemsByQuoteId($attachmentItem->getQuoteId());
        foreach ($items as $item){
            if($this->isParentAttachmentItem($item)){
                $d1 = $this->getItemIdentification($attachmentItem);
                $d2 = $this->getItemIdentification($item);
                if($d1==$d2 && !empty($d2)){
                    return $item;
                }
            }
        }
        return false;
    }
    protected function getAllVisibleItemsByQuoteId($quoteId){
        $items = [];
        if($this->helper->isArea(\Magento\Framework\App\Area::AREA_FRONTEND)){
            $items = $this->cart->getQuote()->getAllVisibleItems();
        }
        else{
            $items = $this->cart->getQuote()->loadActive($quoteId)->getAllVisibleItems();
        }
        return $items;
    }
    public function findAllAttachmentItemInfoFromTheirParent($parentItem){
        if(!$this->isParentAttachmentItem($parentItem)){
            return [];
        }
        if(!$parentItem){
            return [];
        }
        $items = $this->getAllVisibleItemsByQuoteId($parentItem->getQuoteId());
        $identification = $this->getItemIdentification($parentItem);
        $attachmentItems = [];
        if($items){
            $itemIds = [];
            $i = 0;
            foreach ($items as $item){
                $options = $item->getOptionByCode('additional_options');
                if($options){
                    $optionsData = $options->getData();
                    if(isset($optionsData['value'])){
                        $valueData = $this->serializer->unserialize($optionsData['value']);
                        foreach ($valueData as $value){
                            if(isset($value['is_attachment'])){
                                if($value['identification'] == $identification){
                                    $attachmentItems[$i]['qty'] = $item->getQty();
                                    $attachmentItems[$i]['price'] = $item->getPrice();
                                    $attachmentItems[$i]['item_id'] = $item->getId();
                                    $attachmentItems[$i]['sku'] = $item->getSku();
                                    $attachmentItems[$i]['name'] = $item->getName();
                                    $attachmentItems[$i]['additional_options'] = $this->getItemOptionValue($item);
                                    $i++;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $attachmentItems;
    }
    public function findAllAttachmentItemIdsFromTheirParent($parentItem, $productKey = false){
        if(!$this->isParentAttachmentItem($parentItem)){
            return [];
        }
        if(!$parentItem){
            return [];
        }
        $items = $this->getAllVisibleItemsByQuoteId($parentItem->getQuoteId());
        $identification = $this->getItemIdentification($parentItem);
        $attachmentItemIds = [];
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
                                if($value['identification'] == $identification){
                                    if(!$productKey){
                                        $attachmentItemIds[] = $optionsData['item_id'];
                                    }
                                    else{
                                        $attachmentItemIds[$optionsData['product_id']] = $optionsData['item_id'];
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $attachmentItemIds;
    }
    public function getSerializer(){
        return $this->serializer;
    }
    public function getCart(){
        return $this->cart;
    }

    public function showError($errorMessage){
        throw new LocalizedException(
            __($errorMessage));
    }
    public function createAddtionalOptionForProductAttachment($parentSku,$quoteItem){
        $identification = $this->getItemIdentification($quoteItem);
        if(empty($identification)){
            $this->showError("Cannot Add Attachment Item");
        }
        $additionalOptions [] =  [
            'label' => "Parent Product",
            'value' => $parentSku,
            'is_attachment' => 1,
            'identification' => $identification
        ];
        return $additionalOptions;
    }
    //protected function defineIdentification($productId)
    public function createAddtionalOptionForParentAttachment($productId,$extraInfor){
        $attachmentInfo = $this->getAttachmentByProductId($productId);
        if(!empty($attachmentInfo)){
            $value = "";
            $attachmentProductIds = "";
            foreach ($attachmentInfo as $key => $attachment){
                $value .= $attachment['sku'] . ", ";
                $attachmentProductIds .= $attachment['product_attachment_id'] . ",";
            }
            if(!empty($extraInfor)){
                $identification = $productId . "-" . trim($attachmentProductIds,",") . "-" . $extraInfor;
            }
            else{
                $identification = $productId . "-" . trim($attachmentProductIds,",");
            }
            $additionalOptions[] = [
                'label' => "Attachment Products",
                'value' => trim($value,", "),
                'is_parent_attachment' => 1,
                'product_attachment_id' => trim($attachmentProductIds,","),
                'identification' => $identification
            ];
            return $additionalOptions;
        }
        return false;
    }

    public function getItemIdentification($item){
        $this->getItemOptionValue($item);
        $options = $item->getOptionByCode('additional_options');
        if(!$options){
            return false;
        }
        $optionsData = $options->getData();
        if(isset($optionsData['value'])){
            $valueData = $this->serializer->unserialize($optionsData['value']);
            foreach ($valueData as $value){
                if(isset($value['identification'])){
                    return $value['identification'];
                }
            }
        }
        return false;
    }

    public function getAllCorrespondingAttachmentItemsFromTheirParent($parentItem){
        try{
            if(!$parentItem){
                return false;
            }
            $attachmentItems = [];
            $productId = $parentItem->getProduct()->getId();
            $attachmentInfo = $this->getAttachmentByProductId($productId);
            $parentQty = $parentItem->getQty();
            $itemIds = $this->findAllAttachmentItemIdsFromTheirParent($parentItem,true);
            $attachmentData = [];
            if(empty($itemIds)){
                return false;
            }
            if(!empty($attachmentInfo)){
                foreach ($attachmentInfo as $info){
                    $productId = $info['product_attachment_id'];
                    $qty  = $info['qty'];
                    if(isset($itemIds[$productId])){
                        $attachmentData[$itemIds[$productId]] = $parentQty * $qty;
                    }
                }
            }
            return $attachmentData;
        }
        catch(\Exception $ex){
            $this->writeLog($ex->getMessage());
        }
        return false;
    }
    public function loadActiveQuote($quoteId=false){
        if($this->helper->isFrontend()){
            return $quote = $this->cart->getQuote();
        }
        return $this->cart->getQuote()->loadActive($quoteId);
    }

    public function loadActiveQuoteByQuoteId($quoteId){
        return $this->cart->getQuote()->loadActive($quoteId);
    }

    public function getAllAttachmentItems($quoteId = false){

        $quote = $this->loadActiveQuote($quoteId);
        $items = $quote->getAllVisibleItems();
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
            return [];
        }
        return $itemIds;
    }
    public function getProductRepository(){
        return $this->productRepository;
    }

    public function deleteAttachmentItem($parentItem,$currentQuote = false){
        try{
            if(empty($currentQuote)){
                $currentQuote = $this->loadActiveQuote($parentItem->getQuoteId());
            }
            $itemIds = $this->findAllAttachmentItemIdsFromTheirParent($parentItem);
            if(!empty($itemIds)){
                foreach($itemIds as $itemId) {
                    $item = $currentQuote->getItemById($itemId);
                    $item->delete();
                }
            }
        }
        catch (\Exception $ex){
            $this->writeLog($ex->getMessage());
        }
    }
}
?>
