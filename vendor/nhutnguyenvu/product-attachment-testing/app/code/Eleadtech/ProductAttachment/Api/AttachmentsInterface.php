<?php
namespace Eleadtech\ProductAttachment\Api;


interface AttachmentsInterface {


    /**
     * @param string $sku
     * @return array
     */
    public function getAttachments($sku);

    /**
     * @param mixed|string $skus
     * @return \Eleadtech\ProductAttachment\Data\AttachmentsInterface
     */
    public function getAttachmentList($skus);

    /**
     * @param string $cartId
     * @return mixed
     */
    public function getAllItemIdsAttachments($cartId);
}
