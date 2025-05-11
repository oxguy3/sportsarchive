<?php

namespace App\Form;

use App\Entity\TeamName;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamNameType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Primary' => 'primary',
                    'Alternate' => 'alternate',
                ],
            ])
            ->add('firstSeason', TextType::class, [
                'required' => false,
            ])
            ->add('lastSeason', TextType::class, [
                'required' => false,
            ])
            ->add('language', LanguageType::class, [
                'required' => false,
                'preferred_choices' => ['en', 'fr', 'es'],
            ])
            ->add('save', SubmitType::class)
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TeamName::class,
        ]);
    }
}
