<?php

namespace App\Service;

use DateTime;
use Exception;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class HolidayApiService implements HolidayApiServiceInterface
{
    private const BASE_ENDPOINT_URL = 'https://kayaposoft.com/enrico/json/v3.0/';
    private const GET_HOLIDAY_URL = self::BASE_ENDPOINT_URL . 'getHolidaysForYear';
    private const GET_SUPPORTED_COUNTRIES_URL = self::BASE_ENDPOINT_URL . 'getSupportedCountries';

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly ClientServiceInterface $client,
    ){
    }

    public function fetchHolidaysForYear( string $country, string $year): array
    {
        $cacheKey = 'holidays_' . $country . '_' . $year;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($country, $year) {
            $this->setCacheExpiration($item);

            return $this->client->request(self::GET_HOLIDAY_URL, [
                'query' => [
                    'year' => $year,
                    'country' => $country,
                ],
            ]);
        });
    }


    public function fetchCurrentDayStatus(string $country): string
    {
        $cacheKey = 'day_status_' . $country;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($country) {
            $this->setCacheExpiration($item);

            $endpoints = [
                'isWorkDay',
                'isPublicHoliday',
                'isSchoolHoliday'
            ];
            $currentDayDate = new DateTime();
            $currentDayDate = $currentDayDate->format('Y-m-d');

            foreach ($endpoints as $endpoint) {
                $response = $this->client->request(self::BASE_ENDPOINT_URL.$endpoint,
                    [
                        'query' => [
                            'date' => $currentDayDate,
                            'country' => $country,
                        ],
                    ]
                );

                $value = $response;

                if ($value){
                    break;
                }
            }

            return match (true) {
                $value['isWorkDay'] => "Today is a Work Day.",
                $value['isPublicHoliday'] => "Today is a Public Holiday.",
                $value['isSchoolHoliday'] => "Today is a School Holiday.",
                default => "Today is a Weekend or Unknown Holiday.",
            };
        });
    }

    public function fetchCountries(): array
    {
        $cacheKey = 'all_countries';

        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $this->setCacheExpiration($item);

            $countryList = [];
            $data = $this->client->request(self::GET_SUPPORTED_COUNTRIES_URL);

            if ($data) {
                foreach ($data as $country) {
                    $countryList[$country['fullName']] = $country['countryCode'];
                }
            }

            return $countryList;
        });
    }


    public function fetchHolidaysRange(string $country): array
    {
        $cacheKey = 'holidays_year_range_' . $country;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($country) {
            $this->setCacheExpiration($item);
            $data = $this->client->request(self::GET_SUPPORTED_COUNTRIES_URL);

                $countryInfo = null;
                $fromDate = null;
                $toDate = null;

                foreach ($data as $entry) {
                    if ($entry['countryCode'] === $country) {
                        $countryInfo = $entry;
                        $fromDate = $entry['fromDate']['year'];
                        $toDate = $entry['fromDate']['year']+15;
                        break;
                    }
                }
                if (!$countryInfo) {
                    throw new Exception('Country not found');
                }

                $years = range($fromDate, $toDate);
                $yearChoices = [];
                foreach ($years as $year) {
                    $yearChoices[$year] = $year;
                }
                return $yearChoices;
        });
    }

    private function setCacheExpiration(ItemInterface $item): void
    {
        $endOfDay = new DateTime();
        $item->expiresAt($endOfDay->setTime(23, 59, 59));
    }
}
