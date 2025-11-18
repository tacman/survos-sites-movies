<?php

use Castor\Attribute\AsTask;

use function Castor\{io,capture,import, run};
use Survos\StepBundle\Attribute\{Arg,Opt};
import('.castor/vendor/tacman/castor-tools/castor.php');

#[AsTask('meili', description: 'creates and updates meili index settings')]
function create_meili(
    #[Opt()] ?bool $force=null
): void
{
    run('bin/console meili:settings:update ' . ($force ? '--force' : ''));
}

#[AsTask('load', description: 'Loads the database')]
function load_database(
    #[Arg('code')] string $code,
): void
{
    $map = [
        'Wam' => 'data/wam/raw/wam-dywer.csv',
        'Movie' => 'data/movies.csv',
        'Car' => 'data/cars.csv',
        'Book' => 'data/goodreads-books.csv',
    ];
    if (!array_key_exists($code, $map)) {
        io()->error("The code '{$code}' does not exist: " . join('|', array_keys($map)));
        return;
    }
    run(sprintf('bin/console import:entities App\\\\Entity\\\\%s --file %s',
        $code, $map[$code]));
}
