<?php
// app/Http/Controllers/Api/MessageController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $conversations = $user->conversations()
            ->with([
                'tutor:id,first_name,last_name,profile_image',
                'student:id,first_name,last_name',
                'latestMessage:id,conversation_id,sender_id,message,created_at'
            ])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        // Add unread count for each conversation
        $conversations->getCollection()->transform(function ($conversation) use ($user) {
            $conversation->unread_count = $conversation->getUnreadCountFor($user);
            $conversation->other_participant = $conversation->getOtherParticipant($user);
            return $conversation;
        });

        return response()->json([
            'conversations' => $conversations->items(),
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
            'total_unread' => $user->getTotalUnreadMessagesCount(),
        ]);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        // Check if user has access to this conversation
        if ($conversation->tutor_id !== $user->id && $conversation->student_id !== $user->id) {
            return response()->json(['message' => 'Acces interzis.'], 403);
        }

        $messages = $conversation->messages()
            ->with('sender:id,first_name,last_name')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Mark messages as read
        $conversation->markAsReadFor($user);

        return response()->json([
            'conversation' => $conversation->load(['tutor:id,first_name,last_name', 'student:id,first_name,last_name']),
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
            'other_participant' => $conversation->getOtherParticipant($user),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string', 'max:1000'],
            'type' => ['sometimes', 'in:text,booking_reference'],
            'metadata' => ['sometimes', 'array'],
        ]);

        $user = Auth::user();
        $recipient = User::findOrFail($validated['recipient_id']);

        // Validate that one is tutor and one is student
        if (!($user->isTutor() && $recipient->isStudent()) &&
            !($user->isStudent() && $recipient->isTutor())) {
            return response()->json([
                'message' => 'Poți trimite mesaje doar între tutori și studenți.'
            ], 422);
        }

        // Find or create conversation
        if ($user->isTutor()) {
            $conversation = Conversation::findOrCreateBetween($user, $recipient);
        } else {
            $conversation = Conversation::findOrCreateBetween($recipient, $user);
        }

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $validated['message'],
            'type' => $validated['type'] ?? 'text',
            'metadata' => $validated['metadata'] ?? null,
        ]);

        $message->load('sender:id,first_name,last_name');

        // TODO: Broadcast message to real-time listeners

        return response()->json([
            'message' => $message,
            'conversation' => $conversation,
        ], 201);
    }

    public function startConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tutor_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        if (!$user->isStudent()) {
            return response()->json([
                'message' => 'Doar studenții pot inicia conversații cu tutorii.'
            ], 403);
        }

        $tutor = User::findOrFail($validated['tutor_id']);

        if (!$tutor->isTutor()) {
            return response()->json([
                'message' => 'Utilizatorul selectat nu este tutor.'
            ], 422);
        }

        // Find or create conversation
        $conversation = Conversation::findOrCreateBetween($tutor, $user);

        // Create initial message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $validated['message'],
            'type' => 'text',
        ]);

        $message->load('sender:id,first_name,last_name');

        return response()->json([
            'conversation' => $conversation->load(['tutor:id,first_name,last_name', 'student:id,first_name,last_name']),
            'message' => $message,
        ], 201);
    }

    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        // Check access
        if ($conversation->tutor_id !== $user->id && $conversation->student_id !== $user->id) {
            return response()->json(['message' => 'Acces interzis.'], 403);
        }

        $conversation->markAsReadFor($user);

        return response()->json([
            'message' => 'Mesajele au fost marcate ca citite.',
            'unread_count' => $user->getTotalUnreadMessagesCount(),
        ]);
    }

    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'unread_count' => $user->getTotalUnreadMessagesCount(),
        ]);
    }
}
