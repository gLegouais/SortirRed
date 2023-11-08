<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminAddUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options,): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'required' => true
            ])
            ->add('role', CheckboxType::class, [
                'label' => 'role admin ?', //checkbox, menu déroulant, bouton ?
                'required' => false,
                'mapped' => false
            ])

            ->add('lastname', TextType::class, [
                'label' => 'Nom : ',
                'required' => true
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom : '
            ])
            ->add('email', TextType::class, [
                'label' => 'mail :',
                'required' => true
            ])

            ->add('phone', TextType::class, [
                'label' => 'Téléphone : ',
                'required' => true
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'name',
            ]);

        //->add('profilePicture') //photo de profil par défaut ? Supprimé car inutile dans le formulaire

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
