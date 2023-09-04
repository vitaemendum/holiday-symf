<?php

namespace App\Service;

use DateTime;
use Symfony\Component\HttpFoundation\Request;

class HolidayService
{
    private HolidayApiService $holidayApiService;

    public function __construct(HolidayApiService $holidayApiService)
    {
        $this->holidayApiService = $holidayApiService;
    }

    public function fetchHolidaysByMonth(Request $request)
    {
        $formData = $request->request->all();
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

    public function fetchCurrentDayStatus(Request $request)
    {
        $formData = $request->request->all();
        $country = $formData['holiday_search_form']['country'];

        return $this->holidayApiService->fetchCurrentDayStatus($country);
    }
}
