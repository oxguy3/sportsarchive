<?php

namespace App\Form;

use App\Entity\TeamName;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamNameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('language', LanguageType::class, [
                'required' => false,
                'preferred_choices' => ['en'],
            ])
            ->add('startYear', IntegerType::class, [
                'required' => false,
            ])
            ->add('endYear', IntegerType::class, [
                'required' => false,
            ])
            // ->add('team', EntityType::class, [
            //     'class' => Team::class,
            //     'query_builder' => function (EntityRepository $er) {
            //         return $er->createQueryBuilder('t')
            //             ->orderBy('t.name', 'ASC');
            //     },
            //     'choice_label' => 'name',
            //     'required' => false,
            // ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TeamName::class,
        ]);
    }
}
