<?php
namespace Eleadtech\ProductAttachment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @category Scandiweb
 * @package Scandiweb\Menumanager\Setup
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2015 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->installProductAttactmentTable($setup);
        $this->createForeignKey($setup);

        $setup->endSetup();
    }
    protected function installProductAttactmentTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('product_attachment'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'product_attachment_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Link Product Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'qty',
                Table::TYPE_INTEGER,
                11,
                ['nullable' => false,'default' => 1]
            )
            ->addColumn(
                'price',
                Table::TYPE_DECIMAL,
                11,
                ['nullable' => false,'default' => 0]
            )
            ->addColumn(
                'price_type',
                Table::TYPE_SMALLINT,
                "",
                ['nullable' => false,'default' => 1],
                'Price Type'
            )
            ->addColumn(
                'description',
                Table::TYPE_TEXT,
                "",
                ['nullable' => true],
                'Description'
            );



        $setup->getConnection()->createTable($table);

    }

    protected function createForeignKey(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $table = $connection->getTableName("catalog_product_entity");
        $connection->addForeignKey(
            $setup->getFkName('product_attachment','product_id',$table,'entity_id'),
            'product_attachment',
            'product_id',
            $table,
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $connection->addForeignKey(
            $setup->getFkName('product_attachment','product_attachment_id',$table,'entity_id'),
            'product_attachment',
            'product_attachment_id',
            $table,
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addIndex(
            $setup->getTable('product_attachment'),
            'product_attachment_product_id',
            array('product_attachment_id', 'product_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE);

    }
}
