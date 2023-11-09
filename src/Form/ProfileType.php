<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ProfileType extends AbstractType
{
    public function __construct(private Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Pseudo:'
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom:'
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom:'
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone:'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email:'
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être les mêmes',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => 'Mot de passe:'],
                'second_options' => ['label' => 'Confirmation'],
                'mapped' => false,
                'attr' => ['autocomplete' => $this->security->getUser()->getPassword()]
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus:',
                'class' => Campus::class,
                'choice_label' => 'name',
                'placeholder' => ''
            ])
            ->add('profilePicture', FileType::class, [
                'label' => 'Ma photo:',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '1M',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Veuillez entrer une image au format .png ou .jpeg/jpg'
                    ])
                ]
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $events) {
            $user = $events->getData();
            if ($user && $user->getProfilePicture()) {
                $form = $events->getForm();
                $form->add('deleteImage', CheckboxType::class, [
                    'label' => 'supprimer l\'image',
                    'required' => false,
                    'mapped' => false
                ]);
            }
        });

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
