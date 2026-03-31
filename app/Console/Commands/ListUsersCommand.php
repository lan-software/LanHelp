<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('users:list {--role= : Filter by role (user, staff, admin)} {--source= : Filter by source (local, lancore)}')]
#[Description('List all users with their role, email, and authentication source')]
class ListUsersCommand extends Command
{
    public function handle(): int
    {
        $query = User::query()->orderBy('id');

        if ($role = $this->option('role')) {
            $query->where('role', $role);
        }

        if ($source = $this->option('source')) {
            match ($source) {
                'lancore' => $query->whereNotNull('lancore_user_id'),
                'local' => $query->whereNull('lancore_user_id'),
                default => $this->error("Unknown source \"{$source}\". Use \"local\" or \"lancore\".") && exit(self::FAILURE),
            };
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('No users found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Source', 'Last Sync'],
            $users->map(fn (User $user) => [
                $user->id,
                $user->displayName(),
                $user->email ?? '—',
                $user->role->label(),
                $user->lancore_user_id ? "LanCore (#{$user->lancore_user_id})" : 'Local',
                $user->lancore_synced_at?->diffForHumans() ?? '—',
            ]),
        );

        $this->line('');
        $this->line("<fg=gray>Total: {$users->count()} user(s)</>");

        return self::SUCCESS;
    }
}
