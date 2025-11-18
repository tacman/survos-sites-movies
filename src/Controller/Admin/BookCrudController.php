<?php

namespace App\Controller\Admin;

use Survos\EzBundle\Controller\BaseCrudController;

class BookCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return \App\Entity\Book::class;
    }
}
