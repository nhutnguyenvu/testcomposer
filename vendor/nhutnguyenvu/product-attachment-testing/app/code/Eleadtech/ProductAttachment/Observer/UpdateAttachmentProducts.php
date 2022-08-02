<?php

namespace Eleadtech\ProductAttachment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class UpdateAttachmentProducts implements ObserverInterface
{
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    protected $attachmentService;
    protected $checkoutSession;
    public function __construct(
        \Eleadtech\ProductAttachment\Service\Attachment $attachment,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->attachmentService = $attachment;
        $this->checkoutSession = $checkoutSession;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $cart = $observer->getEvent()->getCart();
            $items = $cart->getQuote()->getAllVisibleItems();
            $callOneTime = $this->checkoutSession->getCallOneTime();
            if($callOneTime){
                $this->checkoutSession->unsCallOneTime();
                return $this;
            }

            if (!empty($items)) {
                foreach ($items as $item) {
                    if ($this->attachmentService->isParentAttachmentItem($item)) {
                        $correspondingAttachmentData = $this->attachmentService->getAllCorrespondingAttachmentItemsFromTheirParent($item);
                        if (!empty($correspondingAttachmentData)) {
                            $itemRequest = [];
                            foreach ($correspondingAttachmentData as $itemId => $qty){
                                $itemRequest[$itemId] = ['qty' => $qty,'before_suggest_qty' =>$qty];
                            }

                            $this->checkoutSession->setCallOneTime(true);
                            $cart->updateItems($itemRequest);
                        }
                    }
                }
            }
        }
        catch(\Exception $ex) {
            $this->writeLog($ex->getMessage());
        }
    }

    public function writeLog($message){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->create('Eleadtech\ProductAttachment\Helper\Data')->writeLog($message);
    }

}
