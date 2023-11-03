<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Outing;
use App\Form\Model\OutingTypeModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutingType extends AbstractType
{
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
                'input' => 'datetime_immutable'
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
                'class' => City::class,
                'choice_label' => 'name',
                'mapped' => false,
                'placeholder' => '-- Choisir une ville --'
            ])
            ->add('location', LocationType::class, [
                'label' => 'Nouveau lieu',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OutingTypeModel::class,
        ]);
    }
}
