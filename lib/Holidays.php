<?php

namespace Hashsandsalt\Yasumi;

use ArrayIterator;
use DateTime;
use IteratorAggregate;
use Traversable;
use Yasumi\Filters\OfficialHolidaysFilter;
use Yasumi\Filters\ObservedHolidaysFilter;
use Yasumi\Filters\BankHolidaysFilter;
use Yasumi\Filters\SeasonalHolidaysFilter;
use Yasumi\Filters\OtherHolidaysFilter;
use Yasumi\ProviderInterface;

class Holidays implements IteratorAggregate
{
    private ProviderInterface $provider;
    private array $data = [];
    private ?string $type = null;
    private ?DateTime $start = null;
    private ?DateTime $end = null;
    private bool $equals = true;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    private function getData()
    {
        $provider = $this->provider;

        if ($this->start !== null && $this->end !== null) {
            $provider = $provider->between(
                $this->start,
                $this->end,
                $this->equals
            );
        }

        if ($this->type !== null) {
            $iterator = method_exists($provider, 'getIterator')
                ? $provider->getIterator()
                : $provider;
            $provider = match ($this->type) {
                'official' => new OfficialHolidaysFilter($iterator),
                'observed' => new ObservedHolidaysFilter($iterator),
                'bank' => new BankHolidaysFilter($iterator),
                'seasonal' => new SeasonalHolidaysFilter($iterator),
                'other' => new OtherHolidaysFilter($iterator),
                default => $provider
            };
        }

        foreach ($provider as $holiday) {
            $this->data[] = new Holiday($holiday);
        }
    }

    public function getIterator(): Traversable
    {
        $this->getData();

        return new ArrayIterator($this->data);
    }

    public function between(string $start, string $end, bool $equals = true)
    {
        $this->start = new DateTime($start);
        $this->end = new DateTime($end);
        $this->equals = $equals;

        return $this;
    }

    public function filterByType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function __debugInfo(): array
    {
        return iterator_to_array($this->getIterator());
    }
}
