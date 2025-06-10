<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SlackService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SyncSlackUsers extends Command
{
    protected $signature = 'slack:sync-users';
    protected $description = 'Sync all Slack users to Laravel database';

    public function __construct(protected SlackService $slackService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $users = $this->slackService->fetchUsers();
        Log::info($users);
        foreach ($users as $slackUser) {
            if ($slackUser['deleted'] || isset($slackUser['is_bot']) && $slackUser['is_bot']) {
                continue;
            }
            if (!isset($slackUser['profile']['email'])) {
                continue;
            }
            $user = User::where('email', $slackUser['profile']['email'])->first();
            if ($user) {
                $dirty = false;
                if ($user->name !== $slackUser['profile']['real_name']) {
                    $user->name = $slackUser['profile']['real_name'];
                    $dirty = true;
                }
                if ($user->email !== $slackUser['profile']['email']) {
                    $user->email = $slackUser['profile']['email'];
                    $dirty = true;
                }
                if ($user->slack_user_id !== $slackUser['id']) {
                    $user->slack_user_id = $slackUser['id'];
                    $dirty = true;
                }
                if ($dirty) {
                    $user->save();
                }
            } else {
                User::create([
                    'slack_user_id' => $slackUser['id'],
                    'name' => $slackUser['profile']['real_name'] ?? $slackUser['name'],
                    'email' => $slackUser['profile']['email'],
                ]);
            }
        }

        $this->info('Slack users synced successfully.');
    }
}
