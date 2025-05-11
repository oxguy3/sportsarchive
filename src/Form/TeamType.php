<?php

namespace App\Form;

use App\Entity\Team;
use App\Service\SportInfoProvider;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TeamType extends AbstractType
{
    public function __construct(private readonly SportInfoProvider $sportInfo) {}

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Team' => 'teams',
                    'Organization' => 'orgs',
                ],
            ])
            ->add('slug', TextType::class)
            ->add('logo', FileType::class, [
                'label' => 'Logo',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '10m',
                        'mimeTypes' => [
                            'image/svg',
                            'image/svg+xml',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid SVG or PNG file',
                    ]),
                ],
            ])
            ->add('website', UrlType::class, [
                'required' => false,
            ])
            ->add('country', CountryType::class, [
                'required' => false,
            ])
            ->add('startYear', IntegerType::class, [
                'required' => false,
            ])
            ->add('endYear', IntegerType::class, [
                'required' => false,
            ])
            ->add('gender', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Men\'s' => 'men',
                    'Women\'s' => 'women',
                ],
            ])
            ->add('sport', ChoiceType::class, [
                'required' => false,
                'choices' => array_flip($this->sportInfo->getCapitalizedNames()),
            ])
            ->add('parentTeam', EntityType::class, [
                'class' => Team::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'choice_label' => 'name',
                'required' => false,
            ])
            ->add('save', SubmitType::class)
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
