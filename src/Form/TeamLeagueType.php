<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\TeamLeague;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class TeamLeagueType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('league', EntityType::class, [
                'class' => Team::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'choice_label' => 'name',
                'required' => false,
            ])
            ->add('firstSeason', TextType::class, [
                'required' => false,
            ])
            ->add('lastSeason', TextType::class, [
                'required' => false,
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TeamLeague::class,
        ]);
    }
}
