<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Question\Question;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the application';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('>> Welcome to the installation process! <<');

        $this->createEnvFile();

        if (strlen(config('app.key')) === 0) {
            $this->call('key:generate');
            $this->line('~ Secret key properly generated.');
        }

        $credentials = $this->requestDatabaseCredentials();

        $this->updateEnvironmentFile($credentials);

        if ($this->confirm('Do you want to migrate the database?', false)) {
            $this->migrateDatabaseWithFreshCredentials($credentials);

            $this->line('~ Database successfully migrated.');
        }

        if ($this->confirm('Do you want use a github app to communicate with the Github API?', false)) {
            $githubCredentials = $this->requestGithubCredentials();

            $this->updateEnvironmentFile($githubCredentials);
        }

        $this->updateEnvironmentFile($this->requestNotifiableEmail());

        $this->updateEnvironmentFile($this->requestCacheDriver());

        $this->updateEnvironmentFile($this->requestCurrentEnvironment());

        $this->updateEnvironmentFile($this->requestMailConfiguration());

        $this->call('cache:clear');

        $this->info('>> The installation process is complete. Enjoy! <<');
    }

    /**
     * Request the local database details from the user.
     *
     * @return array
     */
    private function requestDatabaseCredentials(): array
    {
        return [
            'DB_DATABASE' => $this->ask('Database name'),
            'DB_PORT' => $this->ask('Database port', 3306),
            'DB_USERNAME' => $this->ask('Database user'),
            'DB_PASSWORD' => $this->askHiddenWithDefault('Database password (leave blank for no password)'),
        ];
    }

    /**
     * Request the github application details from the user.
     *
     * @return array
     */
    private function requestGithubCredentials(): array
    {
        return [
            'GITHUB_CLIENT_ID' => $this->askHiddenWithDefault('Github client id (leave blank for no client id)'),
            'GITHUB_CLIENT_SECRET' => $this->askHiddenWithDefault('Github client secret (leave blank for no client secret)')
        ];
    }

    /**
     * Request the notifiable email address from the user.
     *
     * @return array
     */
    public function requestNotifiableEmail(): array
    {
        return ['NOTIFIED_ADDRESS' => $this->ask('Which email address should be notified of new releases?')];
    }

    /**
     * Request the current environment from the user.
     *
     * @return array
     */
    public function requestCurrentEnvironment(): array
    {
        $env = $this->choice('What is the current environment of the application?', ['local', 'production'], 0);

        return [
            'APP_ENV' => $env,
            'APP_DEBUG' => $env === 'production' ? 'false' : 'true'
        ];
    }

    /**
     * Request the cache driver from the user.
     *
     * @return array
     */
    public function requestCacheDriver(): array
    {
        return ['CACHE_DRIVER' => $this->choice('Which cache driver do you want to use?', ['array', 'file', 'redis'], 1)];
    }

    /**
     * Request the email configuration from the user.
     *
     * @return array
     */
    public function requestMailConfiguration(): array
    {
        return [
            'MAIL_DRIVER' => $this->choice('What is the SMTP driver?', ['smtp', 'log', 'array'], 0),
            'MAIL_HOST' => $this->ask('What is the SMTP hostname?', 'smtp.mailtrap.io'),
            'MAIL_PORT' => $this->ask('What is the SMTP port?', '2525'),
            'MAIL_USERNAME' => $this->ask('What is the SMTP username?', 'null'),
            'MAIL_PASSWORD' => $this->askHiddenWithDefault('What is the SMTP password?'),
            'MAIL_ENCRYPTION' => $this->choice('What is the SMTP encryption?', ['null', 'ssl', 'tls'], 0)
        ];
    }

    /**
     * Update the .env file from an array of $key => $value pairs.
     *
     * @param array $updatedValues
     */
    protected function updateEnvironmentFile($updatedValues): void
    {
        $envFile = $this->laravel->environmentFilePath();

        foreach ($updatedValues as $key => $value) {
            file_put_contents($envFile, preg_replace(
                "/{$key}=(.*)/",
                "{$key}={$value}",
                file_get_contents($envFile)
            ));
        }
    }

    /**
     * Create the initial .env file.
     */
    private function createEnvFile(): void
    {
        if (! file_exists('.env')) {
            copy('.env.example', '.env');
            $this->line('~ .env file successfully created');
        }
    }

    /**
     * Migrate the db with the new credentials.
     *
     * @param array $credentials
     */
    protected function migrateDatabaseWithFreshCredentials($credentials): void
    {
        foreach ($credentials as $key => $value) {
            $configKey = strtolower(str_replace('DB_', '', $key));

            if ($configKey === 'password' && $value == 'null') {
                config(["database.connections.mysql.{$configKey}" => '']);

                continue;
            }

            config(["database.connections.mysql.{$configKey}" => $value]);
        }

        $this->call('migrate', ['--force' => true]);
    }

    /**
     * Prompt the user for optional input but hide the answer from the console.
     *
     * @param string $question
     * @param bool $fallback
     * @return null|string
     */
    private function askHiddenWithDefault($question, $fallback = true): ?string
    {
        $question = new Question($question, 'null');

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }
}
