<?php

namespace App\Http\Controllers\Api\Front;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

use Auth;

use App\Events\PrivateRoomMessageEvent;
use App\Facades\AppBroadcast;
use App\Helpers\Broadcast;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\UserChat;
use App\Notifications\MessageNotification;
use App\Notifications\ResetMessageNotification;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{

    /**
     * Store message from request to db and push this message
     * as PrivateRoomMessageEvent for online users
     * and as notification for offline users.
     *
     * @param Request $request
     * @return bool
     */
    public function pushMessage(Request $request)
    {
        $chatMessage = ChatMessage::create([
            'chat_id' => $request->roomId,
            'message' => $request->message,
            'user_id' => Auth::user()->id,
        ]);
        $chat = Chat::find($request->roomId);

        $usersInChat = $chat->users;
        $usersOnline = array_column(
            json_decode(
                AppBroadcast::users('presence-room.' . $request->roomId), true)['users']
            , 'id');

        foreach ($usersInChat as $user) {
            if ($user->id != Auth::id() && !in_array($user->id, $usersOnline)) {
                UserChat::where('user_id', $user->id)
                    ->where('chat_id', $request->roomId)
                    ->update(['status' => \App\Helpers\Chat::STATUS_NEW_MESSAGE]);
                $chatMessage['title'] = $chat->getChatTitle();
                var_dump($chatMessage);
                $user->notify(new MessageNotification($chatMessage));
            }
        }
        PrivateRoomMessageEvent::dispatch($chatMessage);
    }

    /**
     * Get rooms list for auth user
     *
     * @return Chat[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getRooms()
    {
        $rooms = Chat::with(['users'])
            ->whereHas('users', function (Builder $query) {
                $query->where('user_id', Auth::id());
            })->get();
        return $rooms->map(function ($item) {
            $item['status'] = UserChat::where('user_id', Auth::id())
                ->where('chat_id', $item->id)->first()->status;
            return $item;
        });
    }

    /**
     * Get info about room with messages and users list
     *
     * @param Request $request
     * @param $room_id
     * @return Chat|\Illuminate\Database\Eloquent\Model|null
     */
    public function getRoom(Request $request, $room_id)
    {
        if (Auth::user()->chats->contains($room_id)) {
            UserChat::where('user_id', Auth::id())
                ->where('chat_id', $room_id)
                ->update(['status' => \App\Helpers\Chat::STATUS_NO_NEW_MESSAGE]);
            Auth::user()->notify(new ResetMessageNotification($room_id));
            return Chat::with(['messages', 'messages.user'])->find($room_id);
        }
    }

    public function getUser()
    {
        return Auth::user();
    }

    /**
     * Try to get room with auth user and other user. If room don`t existed, method create it.
     *
     * @param Request $request
     * @param $user_id
     * @return mixed
     */
    public function findOrCreateRoom(Request $request, $user_id)
    {
        $room = Chat::whereHas('users', function (Builder $query) {
            $query->where('user_id', Auth::id());
        })->whereHas('users', function (Builder $query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->first();
        if (!$room) {
            $room = Chat::create([
                'name' => ''
            ]);
            $room->users()->attach(Auth::id());
            $room->users()->attach($user_id);
        }
        return $room;
    }

}
