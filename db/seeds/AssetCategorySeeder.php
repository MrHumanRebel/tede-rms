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
                "assetCategoriesGroups_name" => "Világítás",
                "assetCategoriesGroups_fontAwesome" => "far fa-lightbulb",
                "assetCategoriesGroups_order" => 1,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 2,
                "assetCategoriesGroups_name" => "Hang",
                "assetCategoriesGroups_fontAwesome" => "fas fa-volume-up",
                "assetCategoriesGroups_order" => 2,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 3,
                "assetCategoriesGroups_name" => "Videó",
                "assetCategoriesGroups_fontAwesome" => "fas fa-tv",
                "assetCategoriesGroups_order" => 3,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 4,
                "assetCategoriesGroups_name" => "Díszlet szerelés",
                "assetCategoriesGroups_fontAwesome" => "fas fa-balance-scale-left",
                "assetCategoriesGroups_order" => 4,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 5,
                "assetCategoriesGroups_name" => "Számítógépek és Hálózatok",
                "assetCategoriesGroups_fontAwesome" => "fas fa-server",
                "assetCategoriesGroups_order" => 6,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 6,
                "assetCategoriesGroups_name" => "Kommunikáció",
                "assetCategoriesGroups_fontAwesome" => "fas fa-headset",
                "assetCategoriesGroups_order" => 5,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 10,
                "assetCategoriesGroups_name" => "Jelmez",
                "assetCategoriesGroups_fontAwesome" => null,
                "assetCategoriesGroups_order" => 10,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 11,
                "assetCategoriesGroups_name" => "Kellékek",
                "assetCategoriesGroups_fontAwesome" => null,
                "assetCategoriesGroups_order" => 11,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 12,
                "assetCategoriesGroups_name" => "Díszletek",
                "assetCategoriesGroups_fontAwesome" => null,
                "assetCategoriesGroups_order" => 12,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ],
            [
                "assetCategoriesGroups_id" => 999,
                "assetCategoriesGroups_name" => "Vegyes",
                "assetCategoriesGroups_fontAwesome" => "fas fa-question",
                "assetCategoriesGroups_order" => 999,
                "instances_id" => null,
                "assetCategoriesGroups_deleted" => 0
            ]
        ];

        $categoryData = [
            [
                "assetCategories_id" => 1,
                "assetCategories_name" => "Konvencionális világítás",
                "assetCategories_fontAwesome" => "far fa-lightbulb",
                "assetCategories_rank" => 11,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 2,
                "assetCategories_name" => "Mozgó fények",
                "assetCategories_fontAwesome" => "fas fa-robot",
                "assetCategories_rank" => 12,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 3,
                "assetCategories_name" => "LED-ek",
                "assetCategories_fontAwesome" => "fas fa-traffic-light",
                "assetCategories_rank" => 13,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 4,
                "assetCategories_name" => "Színváltók",
                "assetCategories_fontAwesome" => "fas fa-swatchbook",
                "assetCategories_rank" => 14,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 5,
                "assetCategories_name" => "Kiegészítők és effektek",
                "assetCategories_fontAwesome" => "fas fa-fire",
                "assetCategories_rank" => 18,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 7,
                "assetCategories_name" => "Keverőpultok",
                "assetCategories_fontAwesome" => "fas fa-headphones",
                "assetCategories_rank" => 22,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 8,
                "assetCategories_name" => "Erősítők",
                "assetCategories_fontAwesome" => "fas fa-bullhorn",
                "assetCategories_rank" => 24,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 9,
                "assetCategories_name" => "Mikrofonok & DI",
                "assetCategories_fontAwesome" => "fas fa-microphone",
                "assetCategories_rank" => 21,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 10,
                "assetCategories_name" => "Kiegészítők",
                "assetCategories_fontAwesome" => "fas fa-headset",
                "assetCategories_rank" => 26,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 11,
                "assetCategories_name" => "Hangszórók",
                "assetCategories_fontAwesome" => "fas fa-volume-up",
                "assetCategories_rank" => 23,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 12,
                "assetCategories_name" => "Kábelek",
                "assetCategories_fontAwesome" => "fas fa-network-wired",
                "assetCategories_rank" => 70,
                "assetCategoriesGroups_id" => 999,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 14,
                "assetCategories_name" => "Díszlet szerelés",
                "assetCategories_fontAwesome" => "fas fa-balance-scale-left",
                "assetCategories_rank" => 51,
                "assetCategoriesGroups_id" => 4,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 15,
                "assetCategories_name" => "Fényerő szabályozók",
                "assetCategories_fontAwesome" => "fas fa-bolt",
                "assetCategories_rank" => 17,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 16,
                "assetCategories_name" => "Számítógépek",
                "assetCategories_fontAwesome" => "fas fa-desktop",
                "assetCategories_rank" => 40,
                "assetCategoriesGroups_id" => 5,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 17,
                "assetCategories_name" => "Függönyök, drapériák és szövetek",
                "assetCategories_fontAwesome" => "far fa-eye-slash",
                "assetCategories_rank" => 53,
                "assetCategoriesGroups_id" => 4,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 19,
                "assetCategories_name" => "Kiegészítők",
                "assetCategories_fontAwesome" => "fas fa-video",
                "assetCategories_rank" => 33,
                "assetCategoriesGroups_id" => 3,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 20,
                "assetCategories_name" => "Külső eszközök",
                "assetCategories_fontAwesome" => "fas fa-assistive-listening-systems",
                "assetCategories_rank" => 25,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 21,
                "assetCategories_name" => "Képkeverők és média szerverek",
                "assetCategories_fontAwesome" => "fas fa-server",
                "assetCategories_rank" => 32,
                "assetCategoriesGroups_id" => 3,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 22,
                "assetCategories_name" => "Irányítás",
                "assetCategories_fontAwesome" => "fas fa-microchip",
                "assetCategories_rank" => 15,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 23,
                "assetCategories_name" => "Tokok, dobozok és kocsik",
                "assetCategories_fontAwesome" => "fas fa-truck-loading",
                "assetCategories_rank" => 60,
                "assetCategoriesGroups_id" => 999,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 24,
                "assetCategories_name" => "Szerszámok, biztonság és hozzáférés",
                "assetCategories_fontAwesome" => "fas fa-wrench",
                "assetCategories_rank" => 52,
                "assetCategoriesGroups_id" => 4,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 25,
                "assetCategories_name" => "Kijelzők, panelek és vetítők",
                "assetCategories_fontAwesome" => "fas fa-tv",
                "assetCategories_rank" => 30,
                "assetCategoriesGroups_id" => 3,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 26,
                "assetCategories_name" => "Kiegészítők",
                "assetCategories_fontAwesome" => "far fa-keyboard",
                "assetCategories_rank" => 41,
                "assetCategoriesGroups_id" => 5,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 27,
                "assetCategories_name" => "Rádiók",
                "assetCategories_fontAwesome" => "fas fa-satellite-dish",
                "assetCategories_rank" => 27,
                "assetCategoriesGroups_id" => 6,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 28,
                "assetCategories_name" => "Hálózatok",
                "assetCategories_fontAwesome" => "fas fa-ethernet",
                "assetCategories_rank" => 42,
                "assetCategoriesGroups_id" => 5,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 29,
                "assetCategories_name" => "Főelosztás",
                "assetCategories_fontAwesome" => "fas fa-plug",
                "assetCategories_rank" => 81,
                "assetCategoriesGroups_id" => 999,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 30,
                "assetCategories_name" => "Rendszerek",
                "assetCategories_fontAwesome" => "fas fa-headset",
                "assetCategories_rank" => 999,
                "assetCategoriesGroups_id" => 6,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 31,
                "assetCategories_name" => "Kamerák",
                "assetCategories_fontAwesome" => "fas fa-camera",
                "assetCategories_rank" => 31,
                "assetCategoriesGroups_id" => 3,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 33,
                "assetCategories_name" => "Tabletek és mobiltelefonok",
                "assetCategories_fontAwesome" => "fas fa-mobile-alt",
                "assetCategories_rank" => 40,
                "assetCategoriesGroups_id" => 5,
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
