<?php

use Phinx\Seed\AbstractSeed;

class AssetCategorySeeder extends AbstractSeed
{
    public function run(): void
    {
        $categoryGroupData = [
            [
                "assetCategoriesGroups_id" => 1,
                "assetCategoriesGroups_name" => "Hangtechnika",
                "assetCategoriesGroups_fontAwesome" => "fas fa-volume-up",
                "assetCategoriesGroups_order" => 1,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 2,
                "assetCategoriesGroups_name" => "Fénytechnika",
                "assetCategoriesGroups_fontAwesome" => "far fa-lightbulb",
                "assetCategoriesGroups_order" => 2,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 3,
                "assetCategoriesGroups_name" => "Színpadi tartószerkezet",
                "assetCategoriesGroups_fontAwesome" => "fas fa-tools",
                "assetCategoriesGroups_order" => 3,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 4,
                "assetCategoriesGroups_name" => "Színpadtechnika",
                "assetCategoriesGroups_fontAwesome" => "fas fa-cubes",
                "assetCategoriesGroups_order" => 4,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 5,
                "assetCategoriesGroups_name" => "Vetítéstechnika",
                "assetCategoriesGroups_fontAwesome" => "fas fa-tv",
                "assetCategoriesGroups_order" => 5,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 6,
                "assetCategoriesGroups_name" => "Biztonságtechnika",
                "assetCategoriesGroups_fontAwesome" => "fas fa-shield-alt",
                "assetCategoriesGroups_order" => 6,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ]
        ];

        $categoryData = [
            // Hangtechnika
            [
                "assetCategories_id" => 1,
                "assetCategories_name" => "Hangfal",
                "assetCategories_fontAwesome" => "fas fa-volume-up",
                "assetCategories_rank" => 11,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 2,
                "assetCategories_name" => "Mikrofon",
                "assetCategories_fontAwesome" => "fas fa-microphone",
                "assetCategories_rank" => 12,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            // Fénytechnika
            [
                "assetCategories_id" => 3,
                "assetCategories_name" => "Mozgófejes lámpa",
                "assetCategories_fontAwesome" => "far fa-lightbulb",
                "assetCategories_rank" => 21,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 4,
                "assetCategories_name" => "Ledes lámpa",
                "assetCategories_fontAwesome" => "fas fa-lightbulb",
                "assetCategories_rank" => 22,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            // Színpadi tartószerkezet
            [
                "assetCategories_id" => 5,
                "assetCategories_name" => "Truss elem",
                "assetCategories_fontAwesome" => "fas fa-tools",
                "assetCategories_rank" => 31,
                "assetCategoriesGroups_id" => 3,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 6,
                "assetCategories_name" => "Rögzítő elem",
                "assetCategories_fontAwesome" => "fas fa-paperclip",
                "assetCategories_rank" => 32,
                "assetCategoriesGroups_id" => 3,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            // Színpadtechnika
            [
                "assetCategories_id" => 7,
                "assetCategories_name" => "Dobogó elem",
                "assetCategories_fontAwesome" => "fas fa-cubes",
                "assetCategories_rank" => 41,
                "assetCategoriesGroups_id" => 4,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 8,
                "assetCategories_name" => "Lépcső",
                "assetCategories_fontAwesome" => "fas fa-stairs",
                "assetCategories_rank" => 42,
                "assetCategoriesGroups_id" => 4,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            // Vetítéstechnika
            [
                "assetCategories_id" => 9,
                "assetCategories_name" => "Projektor",
                "assetCategories_fontAwesome" => "fas fa-video",
                "assetCategories_rank" => 51,
                "assetCategoriesGroups_id" => 5,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 10,
                "assetCategories_name" => "LED fal",
                "assetCategories_fontAwesome" => "fas fa-tv",
                "assetCategories_rank" => 52,
                "assetCategoriesGroups_id" => 5,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            // Biztonságtechnika
            [
                "assetCategories_id" => 11,
                "assetCategories_name" => "Kamera",
                "assetCategories_fontAwesome" => "fas fa-video",
                "assetCategories_rank" => 61,
                "assetCategoriesGroups_id" => 6,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 12,
                "assetCategories_name" => "Beléptető rendszer",
                "assetCategories_fontAwesome" => "fas fa-door-closed",
                "assetCategories_rank" => 62,
                "assetCategoriesGroups_id" => 6,
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
        $table->insert($categoryGroupData)->saveData();

        $table = $this->table('assetCategories');
        $table->insert($categoryData)->saveData();
    }
}
