<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminAddUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options, ): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
            ])
            ->add('roles', CheckboxType::class, [
                'label' => 'role admin ?', //checkbox, menu déroulant, bouton ?
                'required' => false,
                'mapped' => false
            ])

            ->add('password', TextType::class, [
                'label' => 'Mot de passe : ',
            ]) //password par défaut ? Truc aléatoire en fonction du nom ?

            ->add('lastname', TextType::class, [
                'label' => 'Nom : '
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom : '
            ])
            ->add('email', TextType::class,[
                'label' => 'mail :'
            ])
            ->add('isActive')
            ->add('phone', TextType::class, [
                'label' => 'Téléphone : '
            ])
            ->add('profilePicture') //photo de profil par défaut ?
            ->add('campus', TextType::class, [ //menu déroulant ?
                'label' => 'Campus : '
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
