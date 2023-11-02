<?php

namespace App\Form;

use App\Entity\Campus;
use App\Form\Model\SearchOutingFormModel;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchOutingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'name',
                'required' => false
            ])
            ->add('name', TextType::class,[
                'label' => 'Le nom de la sortie contient : ',
                'required' => false
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Entre ',
                'required' => false
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => ' et ',
                'required' => false
            ])
            ->add('outingOrganizer', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false
            ])
            ->add('outingEnlisted', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false
            ])
            ->add('outingNotEnlisted', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrite/e',
                'required' => false
            ])
            ->add('outingFinished', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchOutingFormModel::class
        ]);
    }
}
