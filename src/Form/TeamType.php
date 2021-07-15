<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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
            ->add('logoFileType', TextType::class, [
                'required' => false,
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
                'choices' => [
                    'Baseball' => 'baseball',
                    'Basketball' => 'basketball',
                    'Bowling' => 'bowling',
                    'eSports' => 'esports',
                    'Football' => 'football',
                    'Golf' => 'golf',
                    'Hockey' => 'hockey',
                    'Lacrosse' => 'lacrosse',
                    'Motorsport' => 'motorsport',
                    'Rugby' => 'rugby',
                    'Soccer' => 'soccer',
                    'Table tennis' => 'table-tennis',
                    'Tennis' => 'tennis',
                    'Volleyball' => 'volleyball',
                ],
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
