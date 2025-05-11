<?php

namespace App\Controller\EasyAdmin;

use App\Entity\Document;
use App\Service\DocumentInfoProvider;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LanguageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DocumentCrudController extends AbstractCrudController
{
    public function __construct(private readonly DocumentInfoProvider $documentInfo) {}

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Document')
            ->setEntityLabelInPlural('Documents')
            ->setSearchFields(['filename', 'title', 'team.name', 'category'])
            ->setDefaultSort(['team.name' => 'ASC', 'category' => 'ASC', 'title' => 'ASC', 'language' => 'ASC'])
            ->setPaginatorPageSize(30)
            ->setPaginatorRangeSize(4)
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            AssociationField::new('team'),
            TextField::new('title'),
            ChoiceField::new('category')
                ->setChoices(array_flip($this->documentInfo->getCategoryCapitalizedLabels())),
            LanguageField::new('language'),
            TextField::new('fileId'),
            TextField::new('filename'),
            TextareaField::new('notes'),
        ];
    }
}
