<?php
namespace Eleadtech\ProductAttachment\Controller\Adminhtml\Pattachment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Controller\Result\JsonFactory;

class UpdateAttachment extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    protected $resultJsonFactory;
    protected $pattachmentFactory;
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        \Eleadtech\ProductAttachment\Model\PattachmentFactory $pattachmentFactory
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->pattachmentFactory = $pattachmentFactory;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $jsonObject = $this->resultJsonFactory->create();
        $result['message'] = "";
        $result['error'] = 1;
        try{
            $data['product_id'] = intval($params['product_id']);
            $data['product_attachment_id'] = intval($params['product_attachment_id']);
            $data['price'] = @floatval($params['price']);
            $data['price_type'] = @intval($params['price_type']);
            $data['qty'] = floatval($params['qty']);

            if(trim($params['action']) == "remove"){
                $rowDelete = $this->pattachmentFactory->create()->removeAttachmentByProductIdandAttachmentId($data['product_id'],$data['product_attachment_id']);
                if(!$rowDelete){
                    $result['message'] = __("No Action To Do");
                }
                else{
                    $result['message'] = __("Removed Data");
                }
                $result['data'] = "";
                $result ['error'] = 0;
            }
            elseif(trim($params['action']) == "update"){
                $this->pattachmentFactory->create()->updateRecord($data);
                $result['data'] = "";
                $result['message'] = __("Updated Data");
                $result ['error'] = 0;
            }
            else{
                $result ['error'] = 1;
                $result['message'] = __("Nothing to Update");
            }

        } catch (\Exception $ex) {
            $result['error'] = 1;
            $result['message'] = $ex->getMessage();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objectManager->create('Eleadtech\ProductAttachment\Helper\Data')->writeLog($ex->getMessage());
        }

        return $jsonObject->setData($result);
    }
}
