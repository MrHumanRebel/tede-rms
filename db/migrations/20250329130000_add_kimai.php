<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddKimai extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('users_kimai_username', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_social_mobilephone',
            ])
            ->addColumn('users_kimai_passwd', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_kimai_username',
            ])
            ->update();
    }
}
