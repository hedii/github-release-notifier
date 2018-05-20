<?php

namespace App\Console\Commands;

use App\Repository;
use App\Services\Github\Exceptions\RepositoryNotFound;
use App\Services\Github\Github;
use Illuminate\Console\Command;

class WatchRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:watch-repo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a Github repository to the watched repositories';

    /**
     * The Github instance.
     *
     * @var \App\Services\Github\Github
     */
    private $github;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\Github\Github $github
     */
    public function __construct(Github $github)
    {
        parent::__construct();

        $this->github = $github;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $data = explode('/', $this->ask('What Github repository do you want to watch?'));

        if (count($data) < 2) {
            return $this->error('Invalid Github repository name');
        }

        $username = $data[0];
        $repository = $data[1];

        try {
            $repo = $this->github->getRepository($username, $repository);
        } catch (RepositoryNotFound $exception) {
            return $this->error($exception->getMessage());
        }

        if (Repository::where('id', $repo['id'])->exists()) {
            return $this->comment("The Github repository {$username}/{$repository} is already watched");
        }

        Repository::create(['id' => $repo['id'], 'owner' => $username, 'name' => $repository]);

        return $this->info("The Github repository {$username}/{$repository} has been added to the watched repositories");
    }
}
