<?php

namespace Klevze\OnlineUsers\Console;

use Illuminate\Console\Command;
use Klevze\OnlineUsers\Models\UserActivity;

class PopulateUserIpHash extends Command
{
    protected $signature = 'online-users:populate-ip-hash {--batch=1000 : Number of records to process per batch} {--dry-run : Show how many records would be updated without making changes}';
    protected $description = 'Populate the user_ip_hash column for existing user_activities using the configured salt and algorithm.';

    public function handle(): int
    {
        $salt = config('online-users.ip_salt');
        $algorithm = config('online-users.hash_algorithm', 'sha256');

        if (empty($salt)) {
            $this->error('ip_salt is not configured in config/online-users.php or .env. Set ONLINE_USERS_IP_SALT first.');
            return 1;
        }

        $batch = (int)$this->option('batch');

        // Only count rows that still need a hash and have a non-empty ip
        $query = UserActivity::whereNull('user_ip_hash')->whereNotNull('user_ip');
        $count = $query->count();

        if ($this->option('dry-run')) {
            $this->info("Dry run: $count records would be processed.");
            return 0;
        }

        $this->info("Found $count records to process.");

        $progress = $this->output->createProgressBar($count);
        $progress->start();

        $query->chunk($batch, function ($rows) use ($salt, $algorithm, $progress) {
            foreach ($rows as $row) {
                if ($row->user_ip) {
                    $row->user_ip_hash = hash($algorithm, $row->user_ip . $salt);
                    $row->save();
                }
                $progress->advance();
            }
        });

        $progress->finish();
        $this->info('');
            $this->info('Population complete.');

        return 0;
    }
}
