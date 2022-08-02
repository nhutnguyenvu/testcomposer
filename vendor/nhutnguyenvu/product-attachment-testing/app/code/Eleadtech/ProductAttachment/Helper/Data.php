<?php

namespace Eleadtech\ProductAttachment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Eleadtech\Bcore\Helper\Data as BcoreData;


class Data extends BcoreData
{
    protected $logFile = "var/log/product_attachment.log";
    const ENABLED = "enabled";
    const MESSAGE = "message";

    public function writeLog($message)
    {
        parent::writeLog($message);
    }
    public function createApiConfigurationPath($name){
        return "productattachment/api/".$name;
    }
    public function createGeneralConfigurationPath($name){
        return "productattachment/general/".$name;
    }
    public function isEnabled(){
        return $this->getConfigValue($this->createGeneralConfigurationPath(self::ENABLED));
    }
    public function isEnabledApi(){
        return $this->getConfigValue($this->createApiConfigurationPath(self::ENABLED)) && $this->isEnabled();
    }
    public function getAttachmentMessage(){
        return $this->getConfigValue($this->createGeneralConfigurationPath(self::MESSAGE));
    }
}
