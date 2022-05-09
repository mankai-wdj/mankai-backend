<?php

namespace App\Http\Controllers;

use App\Events\DeleteRoomEvent;
use App\Events\InviteEvent;
use App\Events\MessageSent;
use App\Events\ReadEvent;
use App\Events\Users;
use App\Events\UsersCommunication;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Laravel\Ui\Presets\React;
use PhpParser\Node\Expr\FuncCall;
use SebastianBergmann\Environment\Console;
use Throwable;

use function PHPUnit\Framework\isNull;

class ChatController extends Controller
{
    public function getMessages($id, $userId)
    {
        $messages = Room::find($id)->messages()->with('user')->latest()->paginate(20);
        return $messages;
    }


    public function sidebarData($id)
    {
        User::find($id);
        $files = Message::where('room_id', $id)->where('type', 'file')->where('message', 'like', '[{%')->latest()->get();
        $images = Message::where('room_id', $id)->where('type', 'file')->where('message', 'like', '%images%')->latest()->get();
        $memos = Message::where('room_id', $id)->where('type', 'memo')->latest()->get();
        return ["files" => $files, "images" => $images, "memos" => $memos];
    }

    public function sendMessageBot(Request $request)
    {

        $trans = new TranslationController;
        $lang = $trans->searchLanguage($request->message);
        $tran = '';
        if ($request->user_id == 3) { // user_id 5 는 일본어 통역봇
            if ($lang == 'ja') {
                return;
            } else {
                $tran = 'ja';
            }
        } else if ($request->user_id == 5) { // user_id 6 은 영어 통역봇
            if ($lang == 'en') {
                return;
            } else {
                $tran = 'en';
            }
        } else if ($request->user_id == 4) { // user_id 7 은 한국어 통역봇
            if ($lang == 'ko') {
                return;
            } else {
                $tran = 'ko';
            }
        }

        // return $lang.'   '.$tran;
        $client_id = env("NA_CLIENT_ID"); // 네이버 개발자센터에서 발급받은 CLIENT ID
        $client_secret = env("NA_CLIENT_SECRET"); // 네이버 개발자센터에서 발급받은 CLIENT SECRET
        $encText = urlencode($request->message);
        $postvars = "source=" . $lang . "&target=" . $tran . "&text=" . $encText;
        $url = "https://openapi.naver.com/v1/papago/n2mt";
        $is_post = true;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $headers = array();
        $headers[] = "X-Naver-Client-Id: " . $client_id;
        $headers[] = "X-Naver-Client-Secret: " . $client_secret;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // echo "status_code:".$status_code."<br>";
        curl_close($ch);
        if ($status_code == 200) {
            //   echo $response;
            $data = json_decode($response);
            $request->message = $data->message->result->translatedText;
        } else {
            //   echo "Error 내용:".$response;
            // return $request->text;
        }
        return $this->sendMessage($request);
    }

    public function messageSend(Request $request)
    {

        return $this->sendMessage($request);
    }

    public function sendMessage($request)
    {  // send message

        $file_path = null;
        $images = [];
        $user = User::find($request->user_id);
        $read_users = [];
        for ($i = 0; $i < count($request->to_users); $i++) {
            if ($request->to_users[$i] != $user->id) {
                array_push($read_users, (int)$request->to_users[$i]);
            }
        }

        $room = Room::find($request->room_id);

        if ($request->type == 'file' && $request->hasFile('file')) {
            if (is_array($request->file('file'))) {
                for ($i = 0; $i < count($request->file('file')); $i++) {
                    $fileType = explode("/", $request->file('file')[$i]->getClientMimeType());

                    if ($fileType[0] == 'image') {
                        $fileName = time() . '_' . $request->file('file')[$i]->getClientOriginalName();
                        $file_path = $request->file('file')[$i]->storeAs('images/' . $request->room_id . '/' . date('Y-m-d'), $fileName, 's3');
                        $file_path = Storage::url($file_path);
                        array_push($images, $file_path);
                    } else {
                        $fileName = time() . '_' . $request->file('file')[$i]->getClientOriginalName();
                        $path = $request->file('file')[$i]->storeAs('files/' . $request->room_id . '/' . date('Y-m-d'), $fileName, 's3');
                        $file_path = [];

                        array_push($file_path, (object)array('path' => Storage::url($path), 'size' => $request->file('file')[$i]->getSize(), 'name' => $request->file('file')[$i]->getClientOriginalName(), 'type' => explode(".", $request->file('file')[$i]->getClientOriginalName())[count(explode(".", $request->file('file')[$i]->getClientOriginalName())) - 1]));
                        $file_path = json_encode($file_path);

                        $message = $user->messages()->create([
                            'message' => $file_path,
                            'room_id' => $request->room_id,
                            'read_users' => json_encode($read_users),
                            'type' => 'file'
                        ]);

                        broadcast(new MessageSent($message->load('user'), $request->room_id));
                        for ($j = 0; $j < count($request->to_users); $j++) {
                            broadcast(new UsersCommunication($message->load('user'), $request->to_users[$j]));
                        }
                    }
                }

                if ($images !== []) {
                    $files = '';
                    if (count($images) > 1) {
                        $files = json_encode($images);
                    } else {
                        $files = $images[0];
                    }
                    $message = $user->messages()->create([
                        'message' => $files,
                        'room_id' => $request->room_id,
                        'read_users' => json_encode($read_users),
                        'type' => 'file'
                    ]);
                    broadcast(new MessageSent($message->load('user'), $request->room_id));
                    for ($i = 0; $i < count($request->to_users); $i++) {
                        broadcast(new UsersCommunication($message->load('user'), $request->to_users[$i]));
                    }
                }
            } else {
                $fileType = explode("/", $request->file('file')->getClientMimeType());
                if ($fileType[0] == 'image') {
                    $request->file('file');
                    $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
                    $file_path = $request->file('file')->storeAs('images/' . $request->room_id . '/' . date('Y-m-d'), $fileName, 's3');
                    $file_path = Storage::url($file_path);
                } else {
                    $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
                    $path = $request->file('file')->storeAs('files/' . $request->room_id . '/' . date('Y-m-d'), $fileName, 's3');
                    $file_path = [];
                    array_push($file_path, (object)array('path' => Storage::url($path), 'size' => $request->file('file')->getSize(), 'name' => $request->file('file')->getClientOriginalName(), 'type' => explode(".", $request->file('file')->getClientOriginalName())[count(explode(".", $request->file('file')->getClientOriginalName())) - 1]));
                    $file_path = json_encode($file_path);
                }
                $message = $user->messages()->create([
                    'message' => $file_path,
                    'room_id' => $request->room_id,
                    'read_users' => json_encode($read_users),
                    'type' => 'file'
                ]);
                broadcast(new MessageSent($message->load('user'), $request->room_id));
                for ($i = 0; $i < count($request->to_users); $i++) {
                    broadcast(new UsersCommunication($message->load('user'), $request->to_users[$i]));
                }
            }
        } else {
            if ($request->type == 'memo') {
                for ($i = 0; $i < count($request->memos); $i++) {
                    $message = $user->messages()->create([
                        'room_id' => $request->room_id,
                        'message' => json_encode($request->memos[$i]),
                        'read_users' => json_encode($read_users),
                        'type' => 'memo'
                    ]);
                    broadcast(new MessageSent($message->load('user'), $request->room_id));
                    for ($j = 0; $j < count($request->to_users); $j++) {
                        broadcast(new UsersCommunication($message->load('user'), $request->to_users[$j]));
                    }
                }

                return 'memo send complate';
            } else if ($request->type == 'group') {
                $message = $user->messages()->create([
                    'room_id' => $request->room_id,
                    'message' => json_encode($request->group),
                    'read_users' => json_encode($read_users),
                    'type' => 'group'
                ]);
                broadcast(new MessageSent($message->load('user'), $request->room_id));
                for ($j = 0; $j < count($request->to_users); $j++) {
                    broadcast(new UsersCommunication($message->load('user'), $request->to_users[$j]));
                }
            } else if (!$request->message) {
                return;
            } else if ($request->type == 'video') {
                $message = $user->messages()->create([
                    'message' => $request->message,
                    'room_id' => $request->room_id,
                    'read_users' => json_encode($read_users),
                    'type' => 'video'
                ]);
            } else {
                $message = $user->messages()->create([
                    'message' => $request->message,
                    'room_id' => $request->room_id,
                    'read_users' => json_encode($read_users),
                    'type' => 'message'
                ]);
            }
            broadcast(new MessageSent($message->load('user'), $request->room_id));
            for ($i = 0; $i < count($request->to_users); $i++) {
                broadcast(new UsersCommunication($message->load('user'), $request->to_users[$i]));
            }
        }


        $room->last_message = $request->message;
        $room->save();
        return $message;
    }

    public function deleteRoom(Request $request)
    { // room delete
        $room = Room::find($request->room['id']);
        if ($request->room['type'] === 'dm') {
            DB::table('room_user')->where('room_id', $request->room['id'])->where('user_id', $request->user_id)->update(['exists' => 1]);
        } else {
            DB::table('room_user')->where('room_id', $request->room['id'])->where('user_id', $request->user_id)->delete();

            $updateUsers = json_decode($room->users);
            for ($i = 0; $i < count($updateUsers); $i++) {
                if ($updateUsers[$i]->user_id === $request->user_id) {
                    array_splice($updateUsers, $i, 1);
                    break;
                }
            };
            $room->users = json_encode($updateUsers);
            $room->save();
            for ($i = 0; $i < count($updateUsers); $i++) {
                broadcast(new DeleteRoomEvent($room, $updateUsers[$i]->user_id));
            }
        }
        return $room;
    }

    public function getChatRoomById($id)
    {
        $chatroom = Room::find($id);
        return $chatroom;
    }

    public function getRooms($id)
    {  // user rooms get
        $user = User::find($id);
        $chatroom = Room::query()->leftJoin('room_user', 'rooms.id', '=', 'room_user.room_id')
            ->where('room_user.user_id', $user->id)
            ->whereIn('rooms.id', function ($query) use ($id) {
                return $query->select('room_id')->from('room_user')->where('exists', false)->where('user_id', $id)->get();
            })->select('rooms.*')->orderBy('updated_at', 'desc')->get();
        return $chatroom;
    }

    public function inviteUser(Request $request)
    {  // invite users

        $room = Room::find($request->room['id']);
        $users = $request->inviteUsers;
        $users1 = [];
        if ($room['type'] == 'dm') {
            $users1 = [...$users1, ...json_decode($room->users)];
            $room = new Room();

            for ($i = 0; $i < count($users); $i++) {
                array_push($users1, (object)array('user_id' => $users[$i]['id'], 'user_name' => $users[$i]['name'], 'position' => $users[$i]['position'], 'country' => $users[$i]['country']));
            }
            $room->users = json_encode($users1);
            $room->type = "group";
            $room->save();
            for ($i = 0; $i < count($users1); $i++) {
                $user = User::find($users1[$i]->user_id);
                $user->myRooms()->attach($room->id);
                if ($users1[$i]->user_id != $request->user['id']) {
                    broadcast(new InviteEvent($room, $user->id));
                }
            }
        } else {
            $users1 = json_decode($room->users);
            for ($i = 0; $i < count($users); $i++) {
                array_push($users1, (object)array('user_id' => $users[$i]['id'], 'user_name' => $users[$i]['name'], 'position' => $users[$i]['position'], 'country' => $users[$i]['country']));
            }
            $room->users = json_encode($users1);
            $room->save();
            for ($i = 0; $i < count($users1); $i++) {
                if ($users1[$i]->user_id != $request->user['id']) {
                    broadcast(new InviteEvent($room, $users1[$i]->user_id));
                }
            }
            $users1 = [...$users];

            for ($i = 0; $i < count($users1); $i++) {
                $user = User::find($users1[$i]['id']);
                $user->myRooms()->attach($room->id);
            }
        }
        return $room;
    }

    public function createRoom(Request $request)
    {  //room create
        return json_decode($request->users);
        $users =  $request->users;
        $type = '';
        if (count($users) === 2) {
            $type = 'dm';
            $to_user = User::find($users[0]['id']);
            $to_user_id = $users[0]['id'];
            $user = User::find($users[1]['id']);
            $chatroom = Room::query()->leftJoin('room_user', 'rooms.id', '=', 'room_user.room_id')
                ->where('rooms.type', $type)
                ->where('room_user.user_id', $user->id)
                ->whereIn('rooms.id', function ($query) use ($to_user_id) {
                    return $query->select('room_id')->from('room_user')->where('user_id', $to_user_id)->get();
                })->select('rooms.*')->get();

            try {
                if ($chatroom[0]) {
                    DB::table('room_user')->where('room_id', $chatroom[0]->id)->where('user_id', $user->id)->update(['exists' => 0]);
                    DB::table('room_user')->where('room_id', $chatroom[0]->id)->where('user_id', $to_user_id)->update(['exists' => 0]);
                }
                broadcast(new InviteEvent($chatroom[0], $to_user_id));
                return $chatroom[0];
            } catch (Throwable $err) {
            };
        } else {
            $type = 'group';
        }

        $users1 = [];
        $room = new Room();
        if ($request->title) {
            $room->title = $request->title;
        } elseif ($request->password) {
            $room->password = $request->password;
        }

        for ($i = 0; $i < count($users); $i++) {
            array_push($users1, (object)array('user_id' => $users[$i]['id'], 'user_name' => $users[$i]['name'], 'position' => $users[$i]['position'], 'country' => $users[$i]['country']));
        }
        $room->users = json_encode($users1);
        $room->type = $type;
        $room->save();


        for ($i = 0; $i < count($users); $i++) {
            // $rooms = [];
            $user = User::find($users[$i]['id']);
            $user->myRooms()->attach($room->id);
            if ($users[$i]['id'] != $users[count($users) - 1]['id']) {
                broadcast(new InviteEvent($room, $users[$i]['id']));
            }
        }

        return $room;
    }
}
