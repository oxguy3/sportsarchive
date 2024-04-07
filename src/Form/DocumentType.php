<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'File',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '750m',
                    ]),
                ],
            ])
            ->add('title', TextType::class)
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Unsorted' => 'unsorted',
                    'Branding' => 'branding',
                    'Directories' => 'directories',
                    'Game notes' => 'game-notes',
                    'Legal documents' => 'legal-documents',
                    'Media guides' => 'media-guides',
                    'Miscellany' => 'miscellany',
                    'Press releases' => 'press-releases',
                    'Programs' => 'programs',
                    'Record books' => 'record-books',
                    'Rosters' => 'rosters',
                    'Rule books' => 'rule-books',
                    'Schedules' => 'schedules',
                    'Season reviews' => 'season-reviews',
                    'Yearbooks' => 'yearbooks',
                ],
            ])
            ->add('language', LanguageType::class, [
                'required' => false,
                'preferred_choices' => ['en'],
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'general.save',
            ])
            ->add('saveAndAddAnother', SubmitType::class, [
                'label' => 'document.saveAndAddAnother',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
