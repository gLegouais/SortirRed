<?php

namespace App\Form;

use App\Form\Model\UploadUsersTypeModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadUsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('csv', FileType::class, ['label' => 'Fichier (csv)',
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/plain',
                            'text/csv',
                            'application/octet-stream',
                        ],
                        'mimeTypesMessage' => 'Choisir un fichier au format csv.',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UploadUsersTypeModel::class,
        ]);
    }
}
