<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Location;
use App\Entity\Outing;
use App\Form\Model\OutingTypeModel;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class OutingType extends AbstractType
{

    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly CityRepository     $cityRepository
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie'
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                'input' => 'datetime_immutable'
            ])
            ->add('deadline', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [
                    new LessThanOrEqual([
                        'propertyPath' => 'parent.all[startDate].data',
                        'message' => 'La date limite d\'inscription doit être inférieure/égale à la date de la sortie'
                    ]),
                ]
            ])
            ->add('maxRegistered', IntegerType::class, [
                'label' => 'Nombre de places'
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description et infos'
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'name',
            ])
            ->add('city', EntityType::class, [
                'label' => 'Ville',
                'required' => true,
                'class' => City::class,
                'choice_label' => 'name',
                'mapped' => false,
                'placeholder' => '-- Choisir une ville --'
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'label' => 'Lieu',
                'choice_label' => 'name',
                'choices' => [],
                'disabled' => true,
                'placeholder' => '-- Choix d\'une ville requis --'
            ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function addLocations(FormInterface $form, City $city = null, Location $location = null)
    {


        if ($city) {
            $locations = $this->locationRepository->createQueryBuilder('l')
                ->where('l.city = :cityId')
                ->setParameter('cityId', $city->getId())
                ->getQuery()->getResult();
        }

        if ($location) {
            $form->add('location', EntityType::class, [
                'required' => true,
                'class' => Location::class,
                'placeholder' => '-- Choix d\'une ville requis --',
                'choices' => $locations,
                'choice_label' => 'name',
                'empty_data' => $location
            ]);
            $form->add('city', EntityType::class, [
                'label' => 'Ville',
                'required' => true,
                'class' => City::class,
                'choice_label' => 'name',
                'mapped' => false,
                'placeholder' => '-- Choisir une ville --',
                'data' => $city
            ]);
        } else {
            $form->add('location', EntityType::class, [
                'required' => true,
                'class' => Location::class,
                'placeholder' => '-- Choix d\'une ville requis --',
                'choices' => $locations,
                'choice_label' => 'name'
            ]);
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $city = $this->cityRepository->find($data['city']);

        $this->addLocations($form, $city);

    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $outing = $event->getData();
        $location = $outing->getLocation();
        if ($location) {
            $city = $location->getCity();

            $this->addLocations($form, $city, $location);
        }

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,
        ]);
    }
}
