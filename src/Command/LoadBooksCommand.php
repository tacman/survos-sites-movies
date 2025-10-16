<?php

namespace App\Command;

use App\Entity\Movie;
use App\Repository\BookRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Survos\CoreBundle\Service\LooseObjectMapper;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand('app:load', 'Load the movies database')]
class LoadBooksCommand
{
	public function __construct(
        private LooseObjectMapper      $objectMapper,
        private EntityManagerInterface $em,
        private BookRepository         $bookRepository,
    )
	{
	}


	public function __invoke(
		SymfonyStyle $io,
		#[Argument('url to the csv')]
		string $url = "https://raw.githubusercontent.com/sanjeevai/Investigate_a_dataset/master/tmdb-movies.csv",
		#[Option('limit the number imported')] ?int $limit = null,
		#[Option('batch flush')] int $batch = 500,
		#[Option('purge the table first')] ?bool $reset = null,
	): int
	{
        $filename = 'movies.csv';
        if (!file_exists($filename)) {
            file_put_contents($filename, file_get_contents($url));
        }
        if ($reset) {
            $deleted = $this->em->createQueryBuilder()->delete(Movie::class, 'm')->getQuery()->execute();
            $io->writeln("$deleted rows deleted");
        }
        $reader = Reader::createFromPath($filename, 'r');
        $reader->setHeaderOffset(0);
        foreach ($reader as $idx => $row) {
            // skip the lookup if reset
            if ($reset || (!$movie = $this->em->getRepository(Movie::class)->find($row['id']))) {
                $movie = new Movie();
                $movie->id = $row['id'];
                $this->em->persist($movie);
            }
            foreach (['production_companies','cast','budget_adj','revenue_adj'] as $key) {
                unset($row[$key]);
            }
//            $entity =
                $this->objectMapper->mapInto($row, $movie, ignored: ['id']);
//            $entity = $this->objectMapper->map($row, Movie::class);
            if (($idx % $batch) === 0) {
                $io->writeln($idx);
                $this->em->flush();
                $this->em->clear();
            }

            if ($limit && ($idx >= $limit)) {
                break;
            }

        }
        $this->em->flush();
		$io->success(self::class . " success. " . $this->em->getRepository(Movie::class)->count());
		return Command::SUCCESS;
	}
}
