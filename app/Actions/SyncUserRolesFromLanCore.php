<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;

class SyncUserRolesFromLanCore
{
    /**
     * Sync the user's roles from a LanCore-authoritative role list.
     *
     * Callers must supply the roles — from the SSO exchange response or
     * webhook payload. The action never talks to LanCore itself.
     *
     * @param  array<int, string>  $roles
     */
    public function handle(User $user, array $roles): void
    {
        $this->applyRoles($user, $roles);
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
