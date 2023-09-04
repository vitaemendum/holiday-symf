<?php

namespace App\Controller;

use App\Service\HolidayApiServiceInterface;
use App\Service\HolidayServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\HolidaySearchFormType;
use Symfony\Component\HttpFoundation\Response;

class HolidayController extends AbstractController
{
    public function __construct(private readonly HolidayServiceInterface $holidayService,private readonly  HolidayApiServiceInterface $holidayApiService)
    {
    }

    #[Route('/', name: 'app_holiday')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(HolidaySearchFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $holidaysByMonth = $this->holidayService->fetchHolidaysByMonth($request);
            $currentDayStatus = $this->holidayService->fetchCurrentDayStatus($request);
        }

        return $this->render('holiday/index.html.twig', [
            'form' => $form->createView(),
            'holidaysByMonth' => $holidaysByMonth ?? [],
            'currentDayStatus' => $currentDayStatus ?? '',
        ]);
    }

    #[Route('/get-holiday-date-range/{country}', name: 'get_holiday_date_range', methods: ['GET'])]
    public function getHolidayRange(string $country): JsonResponse
    {
        $range = $this->holidayApiService->fetchHolidaysRange($country);
        return new JsonResponse($range);
    }
}