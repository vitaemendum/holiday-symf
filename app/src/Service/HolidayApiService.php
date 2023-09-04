<?php

namespace App\Service;

use DateTime;
use Exception;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\Cache\ItemInterface;

class HolidayApiService
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }


    public function fetchHolidaysForYear($country, $year)
    {
        $cacheKey = 'holidays_' . $country . '_' . $year;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($country, $year) {
            $endOfDay = new DateTime();
            $item->expiresAt($endOfDay->setTime(23,59,59));
            try {
                $response = $this->httpClient->request('GET', 'https://kayaposoft.com/enrico/json/v3.0/getHolidaysForYear', [
                    'query' => [
                        'year' => $year,
                        'country' => $country,
                    ],
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new Exception('API request failed with status code ' . $response->getStatusCode());
                }

                return $response->toArray();
            } catch (TransportExceptionInterface $e) {
                throw new Exception('Transport Exception: ' . $e->getMessage());
            } catch (Exception $e) {
                throw new Exception('Error: ' . $e->getMessage());
            }
        });
    }


    public function fetchCurrentDayStatus($country)
    {
        $cacheKey = 'day_status_' . $country;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($country) {
            $endOfDay = new DateTime();
            $item->expiresAt($endOfDay->setTime(23,59,59));
            
            $endpoints = [
                'isPublicHoliday',
                'isSchoolHoliday',
                'isWorkDay',
            ];  
            $currentDayDate = new DateTime();
            $currentDayDate = $currentDayDate->format('Y-m-d');

            $trueValues = [];
            try {
                foreach ($endpoints as $endpoint) {
                    $response = $this->httpClient->request('GET', 'https://kayaposoft.com/enrico/json/v3.0/' . $endpoint, [
                        'query' => [
                            'date' => $currentDayDate,
                            'country' => $country,
                        ],
                    ]);
    
                    if ($response->getStatusCode() !== 200) {
                        throw new Exception('API request failed with status code ' . $response->getStatusCode());
                    }
    
                    $content = $response->getContent();
                    $result = json_decode($content, true);
    
                    $value = $result[$endpoint];
            
                    $trueValues[$endpoint] = $value;
                }
                
                if ($trueValues['isWorkDay']){
                    return "Today is a Work Day.";
                } else if ($trueValues['isPublicHoliday']){
                    return "Today is a Public Holiday.";
                } else if ($trueValues['isSchoolHoliday']){
                    return "Today is a School Holiday.";
                } else {
                    return "Today is a Weekend or Unknown Holiday.";
                }
            } catch (TransportExceptionInterface $e) {
                return [
                    'error' => 'Transport Exception: ' . $e->getMessage(),
                ];
            } catch (Exception $e) {
                return [
                    'error' => 'Error: ' . $e->getMessage(),
                ];
            }
        });
       
    }

    public function fetchCountries()
    {
        $cacheKey = 'all_countries';

        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $endOfDay = new DateTime();
            $item->expiresAt($endOfDay->setTime(23,59,59));
            $countryList = [];
            try {
                $response = $this->httpClient->request('GET', 'https://kayaposoft.com/enrico/json/v3.0/getSupportedCountries');
            
                if ($response->getStatusCode() !== 200) {
                    throw new Exception('API request failed with status code ' . $response->getStatusCode());
                }
            
                $data = $response->toArray();

                if ($data) {
                    foreach ($data as $country) {
                        $countryList[$country['fullName']] = $country['countryCode'];
                    }
                }
            
                return $countryList;
            } catch (TransportExceptionInterface $e) {
                return [
                    'error' => 'Transport Exception: ' . $e->getMessage(),
                ];
            } catch (Exception $e) {
                return [
                    'error' => 'Error: ' . $e->getMessage(),
                ];
            }
        });
    }


    public function fetchHolidaysRange($country)
    {
        $cacheKey = 'holidays_year_range_' . $country;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($country) {
            $endOfDay = new DateTime();
            $item->expiresAt($endOfDay->setTime(23,59,59));
            try {
                $response = $this->httpClient->request('GET', 'https://kayaposoft.com/enrico/json/v3.0/getSupportedCountries');

                if ($response->getStatusCode() !== 200) {
                    throw new Exception('API request failed with status code ' . $response->getStatusCode());
                }

                $data = $response->toArray();
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
            } catch (TransportExceptionInterface $e) {
                return [
                    'error' => 'Transport Exception: ' . $e->getMessage(),
                ];
            } catch (Exception $e) {
                return [
                    'error' => 'Error: ' . $e->getMessage(),
                ];
            }
        });
    }
}