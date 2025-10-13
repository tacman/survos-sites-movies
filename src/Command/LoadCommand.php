<?php

namespace App\Command;

use League\Csv\Reader;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand('app:load', 'Load the movies database')]
class LoadCommand
{
	public function __construct()
	{
	}


	public function __invoke(
		SymfonyStyle $io,
		#[Argument('url to the csv')]
		string $url = "https://raw.githubusercontent.com/sanjeevai/Investigate_a_dataset/master/tmdb-movies.csv",
		#[Option('limit the number imported')]
		?int $limit = null,
	): int
	{
        $filename = 'movies.csv';
        if (!file_exists($filename)) {
            file_put_contents($filename, file_get_contents($url));
        }
        $reader = Reader::createFromPath($filename, 'r');
        $reader->setHeaderOffset(0);
        foreach ($reader as $row) {
            $io->writeln(json_encode($row, JSON_UNESCAPED_SLASHES, JSON_UNESCAPED_UNICODE));
        }
		$io->success(self::class . " success.");
		return Command::SUCCESS;
	}
}
