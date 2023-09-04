<?php

namespace App\Service;

interface HolidayApiServiceInterface
{

    public function fetchHolidaysForYear( string $country, string $year): array;


    public function fetchCurrentDayStatus(string $country): string;

    public function fetchCountries(): array;


    public function fetchHolidaysRange(string $country): array;

}
