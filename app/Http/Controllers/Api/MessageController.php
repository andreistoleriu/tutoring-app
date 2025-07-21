<?php
// Replace your ENTIRE app/Http/Controllers/Api/MessageController.php with this simpler version:

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            // Simple query to avoid relationship issues
            $conversations = DB::table('conversations')
                ->where(function($query) use ($user) {
                    $query->where('tutor_id', $user->id)
                          ->orWhere('student_id', $user->id);
                })
                ->orderBy('last_message_at', 'desc')
                ->get();

            $conversationsWithData = [];

            foreach ($conversations as $conversation) {
                // Get other participant
                $otherUserId = ($conversation->tutor_id == $user->id)
                    ? $conversation->student_id
                    : $conversation->tutor_id;

                $otherUser = User::find($otherUserId);

                // Get latest message
                $latestMessage = Message::where('conversation_id', $conversation->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Get unread count
                $unreadCount = Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->count();

                $conversationsWithData[] = [
                    'id' => $conversation->id,
                    'tutor_id' => $conversation->tutor_id,
                    'student_id' => $conversation->student_id,
                    'last_message_at' => $conversation->last_message_at,
                    'other_participant' => $otherUser,
                    'latest_message' => $latestMessage,
                    'unread_count' => $unreadCount,
                ];
            }

            return response()->json([
                'conversations' => $conversationsWithData,
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => count($conversationsWithData),
                ],
                'total_unread' => array_sum(array_column($conversationsWithData, 'unread_count')),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading conversations: ' . $e->getMessage());

            return response()->json([
                'conversations' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 0,
                ],
                'total_unread' => 0,
            ]);
        }
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        // Check if user has access to this conversation
        if ($conversation->tutor_id !== $user->id && $conversation->student_id !== $user->id) {
            return response()->json(['message' => 'Acces interzis.'], 403);
        }

        try {
            $messages = Message::where('conversation_id', $conversation->id)
                ->with('sender:id,first_name,last_name')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            // Mark messages as read
            Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            // Get other participant
            $otherUserId = ($conversation->tutor_id == $user->id)
                ? $conversation->student_id
                : $conversation->tutor_id;
            $otherUser = User::find($otherUserId);

            return response()->json([
                'conversation' => [
                    'id' => $conversation->id,
                    'tutor_id' => $conversation->tutor_id,
                    'student_id' => $conversation->student_id,
                    'tutor' => $conversation->tutor_id == $user->id ? $user : $otherUser,
                    'student' => $conversation->student_id == $user->id ? $user : $otherUser,
                ],
                'messages' => $messages->toArray(),
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 50,
                    'total' => $messages->count(),
                ],
                'other_participant' => $otherUser,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading conversation: ' . $e->getMessage());

            return response()->json([
                'message' => 'Eroare la încărcarea conversației.'
            ], 500);
        }
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
            'conversation' => [
                'id' => $conversation->id,
                'tutor_id' => $conversation->tutor_id,
                'student_id' => $conversation->student_id,
            ],
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

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Mesajele au fost marcate ca citite.',
            'unread_count' => 0,
        ]);
    }

    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            $unreadCount = DB::table('messages')
                ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
                ->where(function($query) use ($user) {
                    $query->where('conversations.tutor_id', $user->id)
                          ->orWhere('conversations.student_id', $user->id);
                })
                ->where('messages.sender_id', '!=', $user->id)
                ->whereNull('messages.read_at')
                ->count();

            return response()->json([
                'unread_count' => $unreadCount,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting unread count: ' . $e->getMessage());

            return response()->json([
                'unread_count' => 0,
            ]);
        }
    }
}
