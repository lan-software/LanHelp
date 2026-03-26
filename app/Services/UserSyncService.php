<?php

namespace App\Services;

use App\DTOs\LanCoreUser;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserSyncService
{
    /**
     * Create or update a local shadow user from LanCore identity data.
     */
    public function resolveFromUpstream(LanCoreUser $lanCoreUser): User
    {
        $user = User::firstOrNew(['lancore_user_id' => $lanCoreUser->id]);

        $isNew = ! $user->exists;

        // Always sync these fields from LanCore
        $user->name = $lanCoreUser->username;
        $user->avatar_url = $lanCoreUser->avatar;
        $user->lancore_synced_at = Carbon::now();

        if ($lanCoreUser->email) {
            $user->email = $lanCoreUser->email;
        }

        if ($isNew) {
            // New user: set display_name from username, mark email as verified
            $user->lancore_user_id = $lanCoreUser->id;
            $user->display_name = $lanCoreUser->username;
            $user->email_verified_at = Carbon::now();
            $user->password = null;
        }

        // Preserve existing display_name if user has customised it
        // (display_name is only set on first creation above, never overwritten)

        $user->save();

        return $user;
    }
}
