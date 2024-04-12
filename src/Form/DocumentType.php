<?php

namespace App\Form;

use App\Entity\Document;
use App\Service\DocumentInfoProvider;
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
    public function __construct(private readonly DocumentInfoProvider $documentInfo) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'File',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // only required on new documents
                'required' => $options['is_new'],

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1000m',
                    ]),
                ],
            ])
            ->add('title', TextType::class)
            ->add('category', ChoiceType::class, [
                'choices' => array_flip($this->documentInfo->getCategoryCapitalizedLabels()),
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
        ;
        if ($options['is_new']) {
            $builder->add('saveAndAddAnother', SubmitType::class, [
                'label' => 'document.saveAndAddAnother',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
            'is_new' => false,
        ]);

        $resolver->setAllowedTypes('is_new', 'bool');
    }
}
