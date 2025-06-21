<?php

namespace App\Controller\Admin;

use App\Entity\BookedRdv;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

class BookedRdvCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BookedRdv::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Rendez-vous')
            ->setEntityLabelInPlural('Rendez-vous')
            ->setDefaultSort(['beginAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('beginAt')
            ->add('clientSurname')
            ->add('isPaid')
            ->add('rdv');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('clientSurname', 'Nom du client');
        yield AssociationField::new('rdv', 'Type de rendez-vous')
            ->setFormTypeOption('choice_label', 'name');
        
        yield DateTimeField::new('beginAt', 'Date et heure')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->renderAsNativeWidget();
            
        yield DateTimeField::new('createdAt', 'Date de création')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');
            
        yield BooleanField::new('isPaid', 'Payé');
        
        yield TextField::new('bookingToken', 'Code de réservation')
            ->hideOnIndex();
            
        yield TextField::new('paymentId', 'ID de paiement')
            ->hideOnIndex();
            
        // If you want to display the price from the related Rdv entity
        if ($pageName !== Crud::PAGE_NEW) {
            yield MoneyField::new('rdv.price', 'Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(false)
                ->hideOnForm();
        }
    }
}
