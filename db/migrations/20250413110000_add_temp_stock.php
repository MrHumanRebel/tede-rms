<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTempStock extends AbstractMigration
{
    public function change(): void
    {
        $this->table('tempStockAssets')

            ->addColumn('tempStock_assets_id', 'integer', [
                'null' => false,
                'identity' => false,
            ])
            ->addColumn('tempStock_project_id', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('tempStock_assets_quantity', 'integer', [
                'null' => true,
                'default' => null,
            ])

            ->create();
    }
}
