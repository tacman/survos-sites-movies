<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FilmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return \App\Entity\Film::class;
    }
}
