<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjetCollaborationController extends Controller
{
    /**
     * List collaborators on a project.
     * Accessible to anyone who has access to the project.
     */
    public function index(Projet $projet): JsonResponse
    {
        // Use standard view policy to see who else is here
        \Illuminate\Support\Facades\Gate::authorize('view', $projet);

        // Fetch owner
        $owner = User::find($projet->user_id);
        $ownerData = null;
        if ($owner) {
            $ownerData = [
                'id'       => $owner->id,
                'name'     => $owner->name,
                'email'    => $owner->email,
                'role'     => 'owner',
                'is_owner' => true,
            ];
        }

        // Fetch explicitly invited collaborators
        $collaborators = $projet->collaborators()
            ->select('users.id', 'users.name', 'users.email')
            ->get()
            ->map(function ($member) {
                return [
                    'id'       => $member->id,
                    'name'     => $member->name,
                    'email'    => $member->email,
                    'role'     => $member->pivot->role,
                    'is_owner' => false,
                ];
            });

        $allMembers = collect([$ownerData])->filter()->merge($collaborators);

        return response()->json($allMembers);
    }

    /**
     * Change a collaborator's role.
     * Only the owner can do this.
     */
    public function update(Request $request, Projet $projet, User $user): JsonResponse
    {
        // Only owner can manage roles directly
        if ((string) $request->user()->id !== (string) $projet->user_id) {
            return response()->json(['message' => 'Seul le propriétaire peut modifier les rôles.'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(['editor', 'viewer'])],
        ]);

        if ((string) $user->id === (string) $projet->user_id) {
            return response()->json(['message' => 'Impossible de modifier le rôle du propriétaire.'], 422);
        }

        // Check if user is actually a collaborator
        if (! $projet->collaborators()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Cet utilisateur n\'est pas un collaborateur sur ce projet.'], 404);
        }

        $projet->collaborators()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'Rôle mis à jour avec succès.',
            'user_id' => $user->id,
            'role'    => $validated['role'],
        ]);
    }

    /**
     * Remove a collaborator (or leave project).
     * Owner can remove anyone.
     * Anyone can remove themselves.
     */
    public function destroy(Request $request, Projet $projet, User $user): JsonResponse
    {
        $authUser = $request->user();

        if ((string) $user->id === (string) $projet->user_id) {
            return response()->json(['message' => 'Le propriétaire ne peut pas quitter le projet.'], 422);
        }

        // Must be owner OR the user leaving the project themselves
        $isSelf  = (string) $authUser->id === (string) $user->id;
        $isOwner = (string) $authUser->id === (string) $projet->user_id;

        if (! $isSelf && ! $isOwner) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        $projet->collaborators()->detach($user->id);

        return response()->json([
            'message' => 'Collaborateur retiré avec succès.',
        ]);
    }
}
