<?php

use Phinx\Seed\AbstractSeed;

class AssetCategorySeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {

        $categoryGroupData = [
            [
                "assetCategoriesGroups_id" => 1,
                "assetCategoriesGroups_name" => "Világítástechnika",
                "assetCategoriesGroups_fontAwesome" => "far fa-lightbulb",
                "assetCategoriesGroups_order" => 1,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 2,
                "assetCategoriesGroups_name" => "Hangtechnika",
                "assetCategoriesGroups_fontAwesome" => "fas fa-volume-up",
                "assetCategoriesGroups_order" => 2,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 3,
                "assetCategoriesGroups_name" => "Videótechnika",
                "assetCategoriesGroups_fontAwesome" => "fas fa-tv",
                "assetCategoriesGroups_order" => 3,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 4,
                "assetCategoriesGroups_name" => "Színpadtechnika",
                "assetCategoriesGroups_fontAwesome" => "fas fa-headset",
                "assetCategoriesGroups_order" => 4,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 5,
                "assetCategoriesGroups_name" => "Számítógépek és Hálózatok",
                "assetCategoriesGroups_fontAwesome" => "fas fa-server",
                "assetCategoriesGroups_order" => 5,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ]
        ];

        $categoryData = [

            [
                "assetCategories_id" => 1,
                "assetCategories_name" => "Lámpa",
                "assetCategories_fontAwesome" => "far fa-lightbulb",
                "assetCategories_rank" => 11,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 2,
                "assetCategories_name" => "Hangfal",
                "assetCategories_fontAwesome" => "fas fa-robot",
                "assetCategories_rank" => 12,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ]


        ];

        $count = $this->fetchRow('SELECT COUNT(*) AS count FROM assetCategoriesGroups');
        if ($count['count'] > 0) {
            return;
        }
        $count = $this->fetchRow('SELECT COUNT(*) AS count FROM assetCategories');
        if ($count['count'] > 0) {
            return;
        }

        $table = $this->table('assetCategoriesGroups');
        $table->insert($categoryGroupData)
            ->saveData();
        $table = $this->table('assetCategories');
        $table->insert($categoryData)
            ->saveData();
    }
}
