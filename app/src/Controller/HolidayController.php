<?php

namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\HolidaySearchFormType;
use App\Service\HolidayApiService;
use Symfony\Component\HttpFoundation\Response;

class HolidayController extends AbstractController
{
    private HolidayApiService $holidayApiService;

    public function __construct(HolidayApiService $holidayApiService)
    {
        $this->holidayApiService = $holidayApiService;
    }

    #[Route('/', name: 'app_holiday')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(HolidaySearchFormType::class);
        $form->handleRequest($request);

        $holidaysByMonth = [];
        $currentDayStatus = [];
        
        if ($form->isSubmitted() && $form->isValid()) {
            $country = $form->get('country')->getData();
            $year = $form->get('year')->getData();
        
            $data = $this->holidayApiService->fetchHolidaysForYear($country, $year);
            $currentDayStatus = $this->holidayApiService->fetchCurrentDayStatus($country);
            
            foreach ($data as $holiday) {
                
                $monthNumber = $holiday['date']['month'];
                $dateObject = new DateTime("$year-$monthNumber");
                $holidaysByMonth[$dateObject->format('Y-m')][] = $holiday;              
            }
        }

        return $this->render('holiday/index.html.twig', [
            'form' => $form->createView(),
            'holidaysByMonth' => $holidaysByMonth,
            'currentDayStatus' => $currentDayStatus,
        ]);
    }

    #[Route('/get-holiday-date-range/{country}', name: 'get_holiday_date_range', methods: ['GET'])]
    public function getHolidayRange(string $country): JsonResponse
    {
        $range = $this->holidayApiService->fetchHolidaysRange($country);
        return new JsonResponse($range);
    }
}