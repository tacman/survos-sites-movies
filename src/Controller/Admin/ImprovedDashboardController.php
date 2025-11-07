<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Survos\MeiliBundle\Service\MeiliService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AdminDashboard(routePath: '/', routeName: 'admin')]
class ImprovedDashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly MeiliService $meiliService,
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
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
        // Main navigation
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Examples', 'fa fa-lightbulb', 'admin_examples');

        yield MenuItem::section('Content Management', 'fas fa-folder-open');
        ;

        // Group each entity with its search options
        foreach ($this->meiliService->settings as $indexName => $meiliSetting) {
            $class = $meiliSetting['class'];
            $label = new \ReflectionClass($class)->getShortName();
            $count = $this->em->getRepository($class)->count();

            // Parent menu item for the entity
            yield MenuItem::subMenu($label, 'fas fa-film')
                ->setBadge($count, 'info')
                ->setSubItems([
                    // CRUD management
                    MenuItem::linkToCrud('Browse All', 'fas fa-table', $class)
//                        ->setBadge($count, 'info')
                    ,
                    MenuItem::linkToUrl('Overview', 'fas fa-eye',
                        $this->urlGenerator->generate('admin_meili_show_index',
                            ['indexName' => $indexName]
                        )
                    )->setLinkTarget('_blank'),

                    // Divider before searches
                    MenuItem::section('Search Options'),

                    // Full-text search
                    MenuItem::linkToUrl('Full-Text Search', 'fas fa-search',
                        $this->urlGenerator->generate('meili_insta',
                            ['indexName' => $indexName]
                        )
                    )->setLinkTarget('_blank'),

                    // Semantic searches grouped
                    ...$this->getSemanticSearchItems($indexName, $meiliSetting)
                ]);
        }

        // Optional: Add a tools/utilities section at the bottom
        yield MenuItem::section('Tools', 'fas fa-wrench');

        yield MenuItem::linkToUrl('Search Analytics', 'fas fa-chart-line', '#')
            ->setPermission('ROLE_ADMIN');
    }

    public function configureAssets(): Assets
    {
//        return Assets::new()
//            ->addCssFile('css/easyadmin-sidebar-enhanced.css')
            // Or use a CDN/external URL:
            // ->addCssFile('https://yourcdn.com/sidebar.css')

            // Can also add JS if needed:
            // ->addJsFile('js/sidebar-enhancements.js')
            ;


        return parent::configureAssets()
            ->addCssFile('css/easyadmin-sidebar-enhanced.css')
            ;
    }

    private function getSemanticSearchItems(string $indexName, array $meiliSetting): array
    {
        $items = [];

        if (!empty($meiliSetting['embedders'])) {
            foreach ($meiliSetting['embedders'] as $embedder) {
                $items[] = MenuItem::linkToUrl(
                    'Semantic: ' . ucfirst(str_replace('_', ' ', $embedder)),
                    'fas fa-brain',
                    $this->urlGenerator->generate('meili_insta_embed',
                        ['indexName' => $indexName, 'embedder' => $embedder]
                    )
                )->setLinkTarget('_blank');
                ;

            }
        }

        return $items;
    }
}
