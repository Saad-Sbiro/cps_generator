<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use App\Models\ProjetInvitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use App\Notifications\CollabInvitationAccepted;

class ProjetInvitationController extends Controller
{
    /**
     * Send an invitation to a project.
     * Owner or Editor can invite.
     */
    public function store(Request $request, Projet $projet): JsonResponse
    {

        if (! $request->user()->hasProjetRole($projet, 'editor')) {
            return response()->json(['message' => 'Accès interdit. Seul un éditeur ou le propriétaire peut inviter.'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role'  => ['required', 'string', Rule::in(['editor', 'viewer'])],
        ]);

        $email = strtolower(trim($validated['email']));


        $existingUser = User::where('email', $email)->first();
        if ($existingUser && ((string) $existingUser->id === (string) $projet->user_id || $projet->collaborators()->where('user_id', $existingUser->id)->exists())) {
            return response()->json([
                'message' => 'Cet utilisateur collabore déjà sur ce projet.',
            ], 422);
        }


        $existing = ProjetInvitation::where('projet_id', $projet->id)
            ->where('email', $email)
            ->first();

        if ($existing) {

            $existing->update(['role' => $validated['role']]);
            return response()->json([
                'message'    => 'Invitation mise à jour.',
                'invitation' => $existing,
            ]);
        }

        $invitation = ProjetInvitation::create([
            'projet_id'  => $projet->id,
            'inviter_id' => $request->user()->id,
            'email'      => $email,
            'role'       => $validated['role'],
        ]);

        return response()->json([
            'message'    => 'Invitation envoyée avec succès.',
            'invitation' => $invitation,
        ], 201);
    }

    /**
     * List invitations sent for a specific project.
     * Viewers logic can see pending invitations.
     */
    public function indexForProject(Projet $projet): JsonResponse
    {
        Gate::authorize('view', $projet);

        return response()->json($projet->invitations ?? ProjetInvitation::where('projet_id', $projet->id)->get());
    }

    /**
     * Cancel an invitation sent for a project.
     */
    public function destroy(Projet $projet, ProjetInvitation $invitation): JsonResponse
    {
        if (! request()->user()->hasProjetRole($projet, 'editor')) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        if ((string) $invitation->projet_id !== (string) $projet->id) {
            abort(404);
        }

        $invitation->delete();

        return response()->json([
            'message' => 'Invitation annulée.',
        ]);
    }

    /**
     * List pending invitations sent to the authenticated user's email.
     */
    public function indexMyInvitations(Request $request): JsonResponse
    {
        $invitations = ProjetInvitation::where('email', strtolower($request->user()->email))
            ->with(['projet:id,intitule,objet_marche,reference', 'inviter:id,name,email'])
            ->get()
            ->map(function ($invitation) {
                return [
                    'id'         => $invitation->id,
                    'role'       => $invitation->role,
                    'projet'     => [
                        'intitule'     => $invitation->projet->intitule,
                        'objet_marche' => $invitation->projet->objet_marche,
                    ],
                    'invited_by' => $invitation->inviter,
                    'created_at' => $invitation->created_at,
                ];
            });

        return response()->json($invitations);
    }

    /**
     * Decline an invitation from the in-app inbox.
     */
    public function reject(Request $request, ProjetInvitation $invitation): JsonResponse
    {

        if (strtolower($request->user()->email) !== strtolower($invitation->email)) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        $invitation->delete();

        return response()->json(['message' => 'Invitation refusée.']);
    }

    /**
     * Accept a pending invitation.
     */
    public function accept(Request $request, ProjetInvitation $invitation): JsonResponse
    {
        $user = $request->user();


        if (strtolower($user->email) !== strtolower($invitation->email)) {
            return response()->json([
                'message' => 'Cette invitation n\'est pas destinée à votre adresse e-mail.',
            ], 403);
        }

        $projet = $invitation->projet;


        if ((string) $projet->user_id === (string) $user->id || $projet->collaborators()->where('user_id', $user->id)->exists()) {
            $invitation->delete();
            return response()->json([
                'message' => 'Vous collaborez déjà sur ce projet.',
                'projet'  => $projet,
            ]);
        }


        $projet->collaborators()->attach($user->id, ['role' => $invitation->role]);


        $invitation->delete();

        $projet->load('user:id,name,email');
        

        if ((string) $projet->user_id !== (string) $user->id && $projet->user) {
            $projet->user->notify(new CollabInvitationAccepted([
                'projet_id' => $projet->id,
                'projet_title' => $projet->intitule,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'message' => $user->name . ' a accepté votre invitation à collaborer sur "' . $projet->intitule . '".'
            ]));
        }

        return response()->json([
            'message' => 'Vous avez rejoint le projet avec succès.',
            'projet'  => $projet,
        ]);
    }
}
