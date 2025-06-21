<?php

namespace App\Controller\Admin;

use App\Entity\Message;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Message::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('roomId'),
            TextField::new('sender'),
            TextEditorField::new('content'),
            DateTimeField::new('timestamp')
            ->setFormTypeOptions([
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
            ])
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setFormTypeOption('input', 'datetime_immutable')
            ->setLabel('Date'),
                
        ];
    }
    
}
