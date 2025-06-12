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
        foreach ($users as $slackUser) {
            if (isset($slackUser['is_bot']) && $slackUser['is_bot']) {
                continue;
            }
            if (!isset($slackUser['profile']['email'])) {
                continue;
            }
            $isDeletedFromSource = $slackUser['deleted'];
            $user = User::where('email', $slackUser['profile']['email'])->first();
            if ($user) {
                if (!$user->trashed() && $isDeletedFromSource) {
                    // Soft delete the user
                    $user->delete();
                } elseif ($user->trashed() && $isDeletedFromSource) {
                    // Do nothing — already deleted
                } elseif (!$isDeletedFromSource) {
                    // Update user (restore if soft-deleted first)
                    if ($user->trashed()) {
                        $user->restore();
                    }

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
                }
            } else {
                if (!$isDeletedFromSource) {
                    User::create([
                        'slack_user_id' => $slackUser['id'],
                        'name' => $slackUser['profile']['real_name'] ?? $slackUser['name'],
                        'email' => $slackUser['profile']['email'],
                    ]);
                }
            }
        }

        $this->info('Slack users synced successfully.');
    }
}
