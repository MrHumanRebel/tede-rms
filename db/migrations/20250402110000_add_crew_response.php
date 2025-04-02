<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddCrewResponse extends AbstractMigration
{
    public function change(): void
    {
        $this->table('crewAssignments')
            ->addColumn('crewAssignments_response', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'crewAssignments_rank',
                'comment' => '2 - Not confirmed, 1 - Confirmed'
            ])
            ->update();
    }
}
