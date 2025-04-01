<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter; // Hozzáadva

final class AddTruckCost extends AbstractMigration
{
    public function change(): void
    {
        $this->table('projectsFinanceCache')
            ->addColumn('projectsFinanceCache_truckTotal', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_staffTotal'
            ])
            ->update();
    }
}
