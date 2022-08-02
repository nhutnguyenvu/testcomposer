<?php

/**
 * Copyright Ã‚Â© 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Eleadtech\ProductAttachment\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;



/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory

    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $salesSetup = $objectManager->create('Magento\Sales\Setup\SalesSetup');
            $salesSetup->addAttribute('order', 'attachment_signal', ['type' => 'small_int','default'=>0]);
            $quoteSetup = $objectManager->create('Magento\Quote\Setup\QuoteSetup');
            $quoteSetup->addAttribute('quote', 'attachment_signal', ['type' => 'small_int','default'=>0]);
        }
    }
}

