<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface HolidayServiceInterface
{
    public function fetchHolidaysByMonth(Request $request): array;

    public function fetchCurrentDayStatus(Request $request): string;
}
