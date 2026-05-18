<?php

use Hashsandsalt\Yasumi\Holidays;
use Kirby\Cms\App as Kirby;
use Kirby\Toolkit\Str;
use Yasumi\Yasumi;

require_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('hashsandsalt/yasumi', [
    'siteMethods' => [
        'yasumi' => function (
            ?string $country = null,
            ?int $year = null,
            ?string $locale = null
        ) {
            if ($locale === null) {
                $locale = Locale::getDefault();
                $locale = Str::before($locale, '.');
                $locale = Str::replace($locale, '-', '_');
            }

            $country = $country ?? Locale::getDisplayRegion($locale, 'en');
            $year = $year ?? date('Y');

            return Yasumi::create($country, $year, $locale);
        },
        'holidays' => function (
            ?string $country = null,
            ?int $year = null,
            ?string $locale = null
        ) {
            $yasumi = $this->yasumi($country, $year, $locale);

            return new Holidays($yasumi);
        }
    ],
    'collections' => [
        'holidays' => function ($site): Holidays {
            $country = option('hashandsalt.yasumi.country');
            $year = intval(option('hashandsalt.yasumi.year'));
            $locale = option('hashandsalt.yasumi.locale');
            $yasumi = $this->yasumi($country, $year, $locale);

            return new Holidays($yasumi);
        }
    ]
]);
