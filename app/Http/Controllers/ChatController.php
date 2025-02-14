<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use App\Models\ChatMessage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use EchoLabs\Prism\Facades\PrismServer;
use Illuminate\Http\RedirectResponse;

final class ChatController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $team = $user->currentTeam;

        // Only team owner can access chats
        if (!$user->ownsTeam($team)) {
            abort(403, 'Only team owners can access chats.');
        }

        $chats = $team->chats()
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('Chat/Index', [
            'subscriptionEnabled' => true,
            // 'systemPrompt' => view('prompts.system')->render(),
            'models' => PrismServer::prisms()->pluck('name'),
            'chats' => $chats,
            'currentTeam' => $team,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$user->ownsTeam($team)) {
            abort(403, 'Only team owners can create chats.');
        }

        $chat = $team->chats()->create([
            'title' => 'New Chat',
        ]);

        return redirect()->route('chat.show', $chat);
    }

    public function show(Chat $chat): Response
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->ownsTeam($chat->team)) {
            abort(403, 'Only team owners can view chats.');
        }

        $chat->load(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        return Inertia::render('Chat/Show', [
            'chat' => $chat,
            'messages' => $chat->messages,
            'models' => PrismServer::prisms()->pluck('name'),
        ]);
    }

    public function storeMessage(Request $request, Chat $chat): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->ownsTeam($chat->team)) {
            abort(403, 'Only team owners can send messages.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'role' => ['required', 'string', 'in:user,assistant'],
        ]);

        $chat->messages()->create([
            'user_id' => $user->id,
            'content' => $validated['content'],
            'role' => $validated['role'],
        ]);

        $chat->touch();

        return back();
    }

    public function destroy(Chat $chat): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->ownsTeam($chat->team)) {
            abort(403, 'Only team owners can delete chats.');
        }

        $chat->delete();

        return redirect()->route('chat.index');
    }
}
