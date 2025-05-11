<?php

namespace App\Form;

use App\Entity\Headshot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class HeadshotType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('image', FileType::class, [
            'label' => 'Image',

            // unmapped means that this field is not associated to any entity property
            'mapped' => false,

            // make it optional so you don't have to re-upload the PDF file
            // every time you edit the Product details
            'required' => $options['is_new'],

            // unmapped fields can't define their validation using annotations
            // in the associated entity, so you can use the PHP constraint classes
            'constraints' => [
                new Assert\File([
                    'maxSize' => '100m',
                    'mimeTypes' => [
                        'image/*',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image file',
                ]),
            ],
        ]);
        if (!$options['is_dropzone']) {
            $builder
                ->add('personName', TextType::class)
                ->add('jerseyNumber', TextType::class, [
                    'required' => false,
                ])
                ->add('title', TextType::class, [
                    'required' => false,
                ])
                ->add('role', ChoiceType::class, [
                    'choices' => [
                        'Player' => 'player',
                        'Staff' => 'staff',
                    ],
                ])
                ->add('save', SubmitType::class);
        } else {
            $builder->add('role', HiddenType::class, [
                'constraints' => [
                    new Assert\Choice(['player', 'staff']),
                ],
            ]);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Headshot::class,
            'is_new' => false,
            'is_dropzone' => false,
        ]);

        $resolver->setAllowedTypes('is_new', 'bool');
        $resolver->setAllowedTypes('is_dropzone', 'bool');
    }
}
