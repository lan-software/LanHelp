<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use LanSoftware\LanCoreClient\Exceptions\LanCoreDisabledException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreRequestException;
use LanSoftware\LanCoreClient\LanCoreClient;

class SyncUserRolesFromLanCore
{
    public function __construct(private readonly LanCoreClient $client) {}

    /**
     * Sync the user's roles from LanCore.
     *
     * Pass $roles directly (e.g. from an SSO exchange response) to skip the
     * resolve API call. When null the user is resolved from LanCore first.
     *
     * @param  array<string>|null  $roles
     */
    public function handle(User $user, ?array $roles = null): void
    {
        if ($roles === null) {
            $fetched = $this->fetchRoles($user);

            if ($fetched === null || $fetched === false) {
                // 404 (user not in LanCore) or network error — leave roles untouched
                return;
            }

            $roles = $fetched;
        }

        $this->applyRoles($user, $roles);
    }

    /**
     * Fetch roles from LanCore. Returns the roles array on success, null if the
     * user was not found in LanCore, or false on network / unexpected error.
     *
     * @return array<string>|null|false
     */
    private function fetchRoles(User $user): array|null|false
    {
        try {
            if ($user->lancore_user_id) {
                $lanCoreUser = $this->client->resolveUserById($user->lancore_user_id);
            } elseif ($user->email) {
                $lanCoreUser = $this->client->resolveUserByEmail($user->email);
            } else {
                return [];
            }

            return $lanCoreUser->roles ?? [];
        } catch (LanCoreDisabledException) {
            return [];
        } catch (LanCoreRequestException $e) {
            if ($e->statusCode === 404) {
                return null;
            }

            Log::error('LanCore role sync failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'status' => $e->statusCode,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('LanCore role sync failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Apply the given LanCore roles to the user's local role field.
     *
     * LanCore is authoritative for all shared roles.
     * The local Staff role is used as the LanHelp equivalent of moderator.
     *
     * @param  array<string>  $lanCoreRoles
     */
    private function applyRoles(User $user, array $lanCoreRoles): void
    {
        $newRole = $this->highestMappedRole($lanCoreRoles);

        if ($newRole === null) {
            // No recognized LanCore role — reset to User unless the current role
            // is local-only (Staff is never managed by LanCore).
            if ($user->role !== UserRole::Staff && $user->role !== UserRole::User) {
                $user->role = UserRole::User;
                $user->save();
            }

            return;
        }

        // Preserve a local-only Staff role unless LanCore grants higher privilege.
        if ($user->role === UserRole::Staff && $this->priority($newRole) <= $this->priority(UserRole::Staff)) {
            return;
        }

        if ($user->role !== $newRole) {
            $user->role = $newRole;
            $user->save();
        }
    }

    /**
     * Return the highest-privilege UserRole from a LanCore roles array,
     * mapped into the local LanHelp role model.
     *
     * @param  array<string>  $lanCoreRoles
     */
    private function highestMappedRole(array $lanCoreRoles): ?UserRole
    {
        return collect($lanCoreRoles)
            ->map(function (string $role): ?UserRole {
                return match ($role) {
                    'superadmin', 'admin' => UserRole::Admin,
                    'moderator' => UserRole::Staff,
                    'user' => UserRole::User,
                    default => null,
                };
            })
            ->filter(fn (?UserRole $role) => $role !== null)
            ->sortByDesc(fn (UserRole $r) => $this->priority($r))
            ->first();
    }

    private function priority(UserRole $role): int
    {
        return match ($role) {
            UserRole::Admin => 3,
            UserRole::Staff => 2,
            UserRole::User => 1,
        };
    }
}
