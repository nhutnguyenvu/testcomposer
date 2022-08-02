<?php
namespace Eleadtech\ProductAttachment\Controller\Adminhtml\Pattachment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Controller\Result\JsonFactory;

class SearchProducts extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    protected $resultJsonFactory;
    protected $productCollectionFactory;
    protected $serviceAttachment;
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Eleadtech\ProductAttachment\Service\Attachment $serviceAttachment
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->serviceAttachment = $serviceAttachment;
    }

    public function execute()
    {
        $sku = $this->getRequest()->getParam("term");
        $sku = trim($sku,"'");
        $sku = trim($sku,"`");
        $sku = trim($sku,'"');
        $jsonObject = $this->resultJsonFactory->create();
        if(empty($sku)){
            $result['message'] = __("Invalid Sku");
            $result['error'] = 1;
            return $jsonObject->setData($result);
        }
        $result['message'] = "";
        $result['error'] = 0;
        try{
            $data = [];
            $productIds = $this->serviceAttachment->getProductIdList();
            $collection = $this->productCollectionFactory->create()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter ([
                            ["attribute"=>"sku", 'like' => "%$sku%"], ["attribute"=>"name",'like' => "%$sku%"]
                ])
                ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                //->addAttributeToFilter('type_id', ['neq' => "configurable"])
                ->addFieldToFilter('visibility', ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE])
                ->setPageSize(20)
                ->setCurPage(1);

            if(!empty($productIds)){
                $collection->addAttributeToFilter("entity_id",["nin" => $productIds]);
            }
            if($collection){
                $i = 0;
                foreach ($collection as $item){
                    $data[$i]['label'] = $item->getSku() . " ( " . $item->getName() . " )";
                    $data[$i]['value'] = $item->getSku();
                    $data[$i]['product_id'] = $item->getId();
                    $i++;
                }
            }
            $result['data'] = $data;

        } catch (\Exception $ex) {
            $result['error'] = 1;
            $result['message'] = $ex->getMessage();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objectManager->create('Eleadtech\ProductAttachment\Helper\Data')->writeLog($ex->getMessage());
        }

        return $jsonObject->setData($result);
    }
}
