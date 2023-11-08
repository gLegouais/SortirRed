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

            /*
            ->add('password', TextType::class, [
                'label' => 'Mot de passe : ',
            ]) //password par défaut ? Truc aléatoire en fonction du nom ?
                */

            ->add('lastname', TextType::class, [
                'label' => 'Nom : '
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom : '
            ])
            ->add('email', TextType::class,[
                'label' => 'mail :'
            ])

            //->add('isActive') //commenté car non nécessaire et mis en actif par défaut ?

            ->add('phone', TextType::class, [
                'label' => 'Téléphone : '
            ]);

            //->add('profilePicture') //photo de profil par défaut ? Supprimé car inutile dans le formulaire

               /*
            ->add('campus', TextType::class, [ //menu déroulant ?
                'label' => 'Campus : ',
                'mapped' => false //mapped car ma donnée dans mon entité n'est pas du même type que ce que je demande dans mon formulaire. Bonne ou mauvaise idée ?
            ]);*/
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
