<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSubAssets extends AbstractMigration
{
    public function change(): void
    {
        $this->table('assetSubAssets')

            ->addColumn('assetSubAssets_id', 'integer', [
                'null' => false,
            'identity' => false,
        ])
            ->addColumn('assetSubAssets_sub_asset_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('assetSubAssets_sub_asset_quantity', 'integer', [
                'null' => true,
                'default' => null,
            ])

            ->addForeignKey('assetSubAssets_id', 'assets', 'assets_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])

            ->create();

        $this->table('assetsAssignments')
            ->addColumn('assetsAssignments_assignedAsSubAsset', 'boolean', [
                'null' => false,
                'default' => false,
            'after' => "assetsAssignments_deleted"
            ])
            ->save();
    }
}
