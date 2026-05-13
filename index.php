<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Pages;
use Yasumi\Yasumi;

require_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('hashsandsalt/yasumi', [
    'siteMethods' => [
        'holidays' => function (
            string $country,
            int $year,
            string $locale = 'en_US'
        ) {
            return Yasumi::create($country, $year, $locale);
        }
    ],
    'collections' => [
        'holidays' => function ($site): Pages {
            $country = option('hashandsalt.yasumi.country', 'UnitedKingdom');
            $year = intval(option('hashandsalt.yasumi.year', date('Y')));
            $locale = option('hashandsalt.yasumi.locale', 'en_GB');

            foreach ($site->holidays($country, $year, $locale) as $holiday) {
                $date = new DateTime($holiday);

                $pages[] = [
                    'slug' => $holiday->shortName,
                    'num' => $date->format('Ymd'),
                    'template' => 'yasumi',
                    'content' => [
                        'title' => $holiday->getName(),
                        'date' => $date->format('Y-m-d'),
                        'type' => $holiday->getType()
                    ]
                ];
            }

            return Pages::factory($pages);
        }
    ]
]);
