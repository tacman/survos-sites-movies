<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class MovieCrudController extends \Survos\EzBundle\Controller\BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return \App\Entity\Movie::class;
    }
}
