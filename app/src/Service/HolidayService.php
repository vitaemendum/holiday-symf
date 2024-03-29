<?php

namespace App\Service;

use DateTime;
use Symfony\Component\HttpFoundation\Request;

class HolidayService implements HolidayServiceInterface
{
    public function __construct(private readonly HolidayApiServiceInterface $holidayApiService)
    {
    }

    public function fetchHolidaysByMonth(Request $request): array
    {
        $formData = $request->request->all();
        if (!isset($formData['holiday_search_form'])){
            return [];
        }
        $country = $formData['holiday_search_form']['country'];
        $year = $formData['holiday_search_form']['year'];

        $data = $this->holidayApiService->fetchHolidaysForYear($country, $year);

        $holidaysByMonth = [];
        foreach ($data as $holiday) {
            $monthNumber = $holiday['date']['month'];
            $dateObject = new DateTime("$year-$monthNumber");
            $holidaysByMonth[$dateObject->format('Y-m')][] = $holiday;
        }

        return $holidaysByMonth;
    }

    public function fetchCurrentDayStatus(Request $request): string
    {
        $formData = $request->request->all();
        $country = $formData['holiday_search_form']['country'];

        return $this->holidayApiService->fetchCurrentDayStatus($country);
    }
}
