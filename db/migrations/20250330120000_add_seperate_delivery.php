<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSeperateDelivery extends AbstractMigration
{
    public function change(): void
    {
        $this->table('projects')
            ->addColumn('projects_dates_tech_start', 'timestamp', ['null' => true, 'after' => 'projects_dates_deliver_end'])
            ->addColumn('projects_dates_tech_end', 'timestamp', ['null' => true, 'after' => 'projects_dates_tech_start'])

            ->addColumn('projects_dates_stageStructure_start', 'timestamp', ['null' => true, 'after' => 'projects_dates_tech_end'])
            ->addColumn('projects_dates_stageStructure_end', 'timestamp', ['null' => true, 'after' => 'projects_dates_stageStructure_start'])

            ->addColumn('projects_dates_stage_start', 'timestamp', ['null' => true, 'after' => 'projects_dates_stageStructure_end'])
            ->addColumn('projects_dates_stage_end', 'timestamp', ['null' => true, 'after' => 'projects_dates_stage_start'])

            ->addColumn('projects_dates_build', 'timestamp', ['null' => true, 'after' => 'projects_dates_stage_end'])
            ->addColumn('projects_dates_demolition', 'timestamp', ['null' => true, 'after' => 'projects_dates_build'])

            ->update();
    }
}
