<?php

namespace App\Controller\Admin;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\SectionMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Survos\MeiliBundle\Service\MeiliService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AdminDashboard(routePath: '/', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MovieRepository $movieRepository,
        private UrlGeneratorInterface $urlGenerator,
        private MeiliService $meiliService,
    )
    {
    }

    public function index(): Response
    {
        return $this->render('@SurvosMeili/ez/dashboard.html.twig', [
            'settings' => $this->meiliService->settings
        ]);

    }

    #[AdminRoute('/examples', name: 'examples')]
    #[Template('app/index.html.twig')]
    public function examples(): Response
    {
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
         return $this->render('app/index.html.twig', ['searchTerms'  => [
             "post-apocalyptic action film with robots" => "Should find Terminator Salvation semantically - no exact keyword matches but strong conceptual overlap",
             "movies about artificial intelligence taking over" => "Keywords won't match 'movies' or 'taking over', but semantics understand the AI/cyborg theme",
             "dystopian future with resistance fighters" => "Semantic understanding connects 'resistance of humans' in overview with query concepts",
             "Christian Bale sci-fi thriller" => "Combines cast member + genre understanding - tests multi-field semantic matching",
             "films about saving humanity from machines" => "Conceptual match via keywords ('saving the world', 'cyborg') and overview theme",
             "blockbuster with big budget but mixed reviews" => "Uses budget/revenue/rating data semantically - $200M budget, 5.8 rating qualifies as 'mixed'",
             "McG directed action movies" => "Tests director + genre combination - exact director match plus semantic genre understanding",
             "time travel paradox thriller" => "Marcus Wright's ambiguous origin (future or past?) relates to time confusion, tests deep semantic understanding",
             "war between humans and technology" => "Core theme from overview about humans vs militaristic robots",
             "prophecy driven science fiction" => "Keywords include 'prophecy', genre includes 'Science Fiction' - tests keyword + semantic blend"
         ]]
         );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Movies');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Examples', 'fa fa-home', 'admin_examples');

//        yield MenuItem::linkToCrud('movies', 'fas fa-list', Movie::class);
         foreach ($this->meiliService->settings as $indexName => $meiliSetting) {
             $class = $meiliSetting['class'];
             $label = new \ReflectionClass($class)->getShortName();
             yield MenuItem::linkToCrud($label, 'fas fa-database', $class)
                 ->setBadge($this->em->getRepository($class)->count());

             yield MenuItem::linkToUrl('full-text ' . $meiliSetting['rawName'], 'fas fa-search',
                 $this->urlGenerator->generate('meili_insta',
                     ['indexName' => $indexName],
                 )
             );

                 foreach ($meiliSetting['embedders'] as $embedder) {
                     yield MenuItem::linkToUrl('semantic ' . $embedder, 'fab fa-searchengin',
                         $this->urlGenerator->generate('meili_insta_embed',
                     ['indexName' => $indexName, 'embedder' => $embedder],
                 ));
             }
         }
    }
}
