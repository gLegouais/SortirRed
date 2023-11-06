<?php

namespace App\Form;

use App\Entity\Outing;
use App\Form\Model\CancellationTypeModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CancellationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('motif', TextType::class, [
                'label' => "Motif d'annulation",
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CancellationTypeModel::class, //pour le moment, ça marche, mais pour ajouter un motif d'annulation plus proprement, il faut passer par un TypeModel
        ]);
    }




}
