<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSubAssets extends AbstractMigration
{
    public function change(): void
    {
        /*
        $this->table('assetTypes')
            ->addColumn('assetTypes_sub_asset_id', 'integer', [
                'null' => true,
                'default' => null,
                'after' => 'assetTypes_id'
            ])
            ->addColumn('assetTypes_sub_asset_quantity', 'integer', [
                'null' => true,
                'default' => null,
                'after' => 'assetTypes_sub_asset_id'
            ])

            ->addForeignKey('assetTypes_sub_asset_id', 'assetTypes', 'assetTypes_id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->save();
        */

        $this->table('assetTypesSubAssets')
            ->addColumn('assetTypes_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('assetTypes_sub_asset_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('assetTypes_sub_asset_quantity', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->addForeignKey('assetTypes_id', 'assetTypes', 'assetTypes_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])
            ->addForeignKey('assetTypes_sub_asset_id', 'assetTypes', 'assetTypes_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])
            ->create();
        /*
        $this->table('assets')
            ->addColumn('assets_sub_asset_id', 'integer', [
                'null' => true,
                'default' => null,
                'after' => 'assets_id'
            ])
            ->addColumn('assets_sub_asset_quantity', 'integer', [
                'null' => true,
                'default' => null,
                'after' => 'assets_sub_asset_id'
            ])

            ->addForeignKey('assets_sub_asset_id', 'assets', 'assets_id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->save();
        */
    }
}
