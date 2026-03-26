<?php

namespace App\Policies;

use App\Models\Projet;
use App\Models\User;

class ProjetPolicy
{
    /**
     * Determine if the user can view any projects.
     * Users can always list projects (filtered to their owned/shared projects in the controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the given project.
     * Allowed if the project belongs to the user or is shared with them.
     */
    public function view(User $user, Projet $projet): bool
    {
        return $this->canAccessProject($user, $projet);
    }

    /**
     * Determine if the user can create projects.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the project.
     * Must be owner or editor on the project.
     */
    public function update(User $user, Projet $projet): bool
    {
        if (! $this->canAccessProject($user, $projet)) {
            return false;
        }

        return $user->hasProjetRole($projet, 'editor');
    }

    /**
     * Determine if the user can delete the project.
     * Only the project owner can delete the project.
     */
    public function delete(User $user, Projet $projet): bool
    {
        return (string) $user->id === (string) $projet->user_id;
    }

    /**
     * Check if the user is the owner, a collaborator, or can access legacy shared projects.
     */
    private function canAccessProject(User $user, Projet $projet): bool
    {
        // Legacy: seeded shared project (null user_id)
        if ($projet->user_id === null) {
            return true;
        }

        return $user->hasProjetRole($projet, 'viewer');
    }
}
