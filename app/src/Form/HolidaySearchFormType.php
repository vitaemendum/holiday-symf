<?php

namespace App\Form;

use App\Entity\Holiday;
use App\Service\HolidayApiServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HolidaySearchFormType extends AbstractType
{
    public function __construct(private readonly HolidayApiServiceInterface $apiService, private readonly RouterInterface $router)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('country', ChoiceType::class, [
                'choices' => $this->apiService->fetchCountries(),
                'label' => 'Select Country',
                'required' => true,
                'attr' => ['class' => 'country'],
            ])
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    $years = [];

                    if (isset($data['country'])) {
                        $country = $data['country'];
                        $years = $this->apiService->fetchHolidaysRange($country);
                    }

                    $form->add('year', ChoiceType::class, [
                        'choices' => $years,
                        'label' => 'Select Year',
                        'required' => true,
                        'attr' => [
                            'class' => 'year',
                            'data-url' => $this->router->generate('get_holiday_date_range', ['country' => 'ago']),
                        ],
                    ]);
                }
            )
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $form = $event->getForm();

                    $form->add('year', ChoiceType::class, [
                        'choices' => [],
                        'label' => 'Select Year',
                        'required' => true,
                        'attr' => [
                            'class' => 'year',
                            'data-url' => $this->router->generate('get_holiday_date_range', ['country' => 'ago']),
                        ],
                    ]);
                }
            )
            ->add('search', SubmitType::class, [
                'label' => 'Search Holidays',
            ]);
    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Holiday::class,
        ]);
    }
}
