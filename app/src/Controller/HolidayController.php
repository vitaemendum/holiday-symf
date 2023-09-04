<?php

namespace App\Controller;

use App\Service\HolidayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\HolidaySearchFormType;
use App\Service\HolidayApiService;
use Symfony\Component\HttpFoundation\Response;

class HolidayController extends AbstractController
{
    private HolidayService $holidayService;
    private HolidayApiService $holidayApiService;

    public function __construct(HolidayService $holidayService, HolidayApiService $holidayApiService)
    {
        $this->holidayService = $holidayService;
        $this->holidayApiService = $holidayApiService;
    }

    #[Route('/', name: 'app_holiday')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(HolidaySearchFormType::class);
        $form->handleRequest($request);
        //dd($form->handleRequest($request));

        $holidaysByMonth = [];
        $currentDayStatus = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $holidaysByMonth = $this->holidayService->fetchHolidaysByMonth($request);
            $currentDayStatus = $this->holidayService->fetchCurrentDayStatus($request);
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