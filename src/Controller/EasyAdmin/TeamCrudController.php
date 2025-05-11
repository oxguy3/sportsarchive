<?php

namespace App\Controller\EasyAdmin;

use App\Entity\Team;
use App\Service\SportInfoProvider;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class TeamCrudController extends AbstractCrudController
{
    public function __construct(private readonly SportInfoProvider $sportInfo) {}

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Team::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Team')
            ->setEntityLabelInPlural('Teams')
            ->setSearchFields(['name', 'slug'])
            ->setDefaultSort(['name' => 'ASC'])
            ->setPaginatorPageSize(30)
            ->setPaginatorRangeSize(4)
            ->showEntityActionsInlined()
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name'),
            ChoiceField::new('type')
                ->setChoices([
                    'Team' => 'teams',
                    'Organization' => 'orgs',
                ]),
            SlugField::new('slug')->setTargetFieldName('name'),
            UrlField::new('website'),
            CountryField::new('country'),
            IntegerField::new('startYear'),
            IntegerField::new('endYear'),
            ChoiceField::new('gender')
                ->setChoices([
                    'Men' => 'men',
                    'Women' => 'women',
                ]),
            ChoiceField::new('sport')
                ->setChoices(array_flip($this->sportInfo->getCapitalizedNames())),
            AssociationField::new('parentTeam'),
            CollectionField::new('subTeams'),
            CollectionField::new('documents'),
            CollectionField::new('teamLeagues'),
            CollectionField::new('memberTeamLeagues'),
        ];
    }
}
