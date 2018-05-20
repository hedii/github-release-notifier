<?php

namespace App\Console\Commands;

use App\Notifications\NewReleaseForAWatchedGithubRepository;
use App\Repository;
use App\Services\Github\Github;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class WatchReleases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:watch-releases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the latest release for each watched Github repository';

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
     */
    public function handle(): void
    {
        Repository::each(function (Repository $repo) {
            $latestRelease = $this->github->getLatestRelease($repo->owner, $repo->name);

            if (! $latestRelease) {
                $this->comment("No releases yet for the Github repository {$repo->full_name}");

                return true;
            }

            $storedRelease = $repo->releases()->find($latestRelease['id']);

            if (! $storedRelease) {
                /** @var \App\Release $release */
                $release = $repo->releases()->create([
                    'id' => $latestRelease['id'],
                    'tag_name' => $latestRelease['tag_name'],
                    'body' => $latestRelease['body'],
                    'url' => $latestRelease['html_url'],
                    'published_at' => Carbon::parse($latestRelease['published_at'])
                ]);

                $this->info("Latest release {$release->tag_name} added to the Github repository {$repo->full_name}");

                Notification::route('mail', $this->github->notifiedAddress)
                    ->notify(new NewReleaseForAWatchedGithubRepository($release));
            } else {
                $this->comment("Latest release {$storedRelease->tag_name} is already stored for the the Github repository {$repo->full_name}");
            }
        });
    }
}
