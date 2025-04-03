<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddWarehouse extends AbstractMigration
{
    public function change(): void
    {
        $this->table('assets')
            ->addColumn('assets_warehouse', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assets_showPublic'
            ])
            ->update();
    }
}
