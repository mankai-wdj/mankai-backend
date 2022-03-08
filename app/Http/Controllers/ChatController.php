<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\Users;
use App\Events\UsersCommunication;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Laravel\Ui\Presets\React;
use SebastianBergmann\Environment\Console;
use Throwable;

use function PHPUnit\Framework\isNull;

class ChatController extends Controller
{
    public function getMessages($id) {
        // dd(Auth::user());
        $messages = Room::find($id)->messages()->with('user')->latest()->paginate(20);
        return $messages;
    }

    public function sendMessageBot(Request $request) {

        $trans = new TranslationController;
        $lang = $trans->searchLanguage($request->message);
        $tran = '';
        if($request->user_id == 5) { // user_id 5 는 일본어 통역봇
            if($lang == 'ko') {
                $tran = 'ja';
            }else if ($lang == 'ja'){
                $tran = 'ko';
            }else {
                return ;
            }
        }else if($request->user_id == 6) { // user_id 6 은 영어 통역봇
            if($lang == 'ko') {
                $tran = 'en';
            }else if($lang == 'en'){
                $tran = 'ko';
            }else {
                return ;
            }
        }
        // return $lang.'   '.$tran;
        $client_id = env("NA_CLIENT_ID"); // 네이버 개발자센터에서 발급받은 CLIENT ID
        $client_secret = env("NA_CLIENT_SECRET");// 네이버 개발자센터에서 발급받은 CLIENT SECRET
        $encText = urlencode($request->message);
        $postvars = "source=".$lang."&target=".$tran."&text=".$encText;
        $url = "https://openapi.naver.com/v1/papago/n2mt";
        $is_post = true;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $postvars);
        $headers = array();
        $headers[] = "X-Naver-Client-Id: ".$client_id;
        $headers[] = "X-Naver-Client-Secret: ".$client_secret;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec ($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // echo "status_code:".$status_code."<br>";
        curl_close ($ch);
        if($status_code == 200) {
        //   echo $response;
            $data = json_decode($response);
            $request->message = $data->message->result->translatedText;
        } else {
        //   echo "Error 내용:".$response;
            // return $request->text;
        }
        return $this->sendMessage($request);
    }

    public function messageSend(Request $request) {
        return $this->sendMessage($request);
    }

    public function sendMessage($request) {
        // return $request->toUser;
        // return ($request->to_users[0]);
        // return is_array($request->file('file'));
        $file_path = null;
        $images = [];
        $user = User::find($request->user_id);
        // dd($request->file());
        $room = Room::find($request->room_id);
        if ($request->hasFile('file')) {
            // array_push($files, $request->file('file'));
            // dd(is_array($request->file('file')));
            if(is_array($request->file('file'))) {
                for($i = 0; $i < count($request->file('file')); $i++){
                    $fileType = explode("/",$request->file('file')[$i]->getClientMimeType());
                    if($fileType[0] == 'image'){
                        $fileName = time() . '_' . $request->file('file')[$i]->getClientOriginalName();
                        $request->file('file')[$i]->storeAs('/public/images/'.$request->room_id.'/'.date('Y-m-d').'/', $fileName);
                        $file_path ='images/'.$request->room_id.'/'.date('Y-m-d').'/'.$fileName ;
                        array_push($images, $file_path);
                    }else {
                        $fileName = time() . '_' . $request->file('file')[$i]->getClientOriginalName();
                        $request->file('file')[$i]->storeAs('/public/files/'.$request->room_id.'/'.date('Y-m-d').'/', $fileName);
                        $file_path ='files/'.$request->room_id.'/'.date('Y-m-d').'/'.$fileName ;

                        $message = $user->messages()->create([
                            'message' => $request->message,
                            'room_id' => $request->room_id,
                            'file' => $file_path,
                        ]);

                        for($i = 0; $i < count($request->to_users); $i++){
                            broadcast(new MessageSent($message->load('user'), $request->to_users[$i]))->toOthers();
                        }
                        // return $message;
                        // broadcast(new UsersCommunication($message->load('user'), $user))->toOthers();
                    }
                }

                $message = $user->messages()->create([
                    'message' => $request->message,
                    'room_id' => $request->room_id,
                    'file' => json_encode($images),
                ]);

                for($i = 0; $i < count($request->to_users); $i++){
                    broadcast(new MessageSent($message->load('user'), $request->to_users[$i]))->toOthers();
                }
                // broadcast(new UsersCommunication($message->load('user'), $user))->toOthers();
            }else {
                $fileType = explode("/",$request->file('file')->getClientMimeType());
                if($fileType[0] == 'image') {
                    $request->file('file');
                    $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
                    $request->file('file')->storeAs('/public/images/'.$request->room_id.'/'.date('Y-m-d').'/', $fileName);
                    $file_path ='images/'.$request->room_id.'/'.date('Y-m-d').'/'.$fileName ;
                }else {
                    $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
                    $request->file('file')->storeAs('/public/files/'.$request->room_id.'/'.date('Y-m-d').'/', $fileName);
                    $file_path ='files/'.$request->room_id.'/'.date('Y-m-d').'/'.$fileName ;
                }
                $message = $user->messages()->create([
                    'message' => $request->message,
                    'room_id' => $request->room_id,
                    'file' => $file_path,
                ]);
                for($i = 0; $i < count($request->to_users); $i++){
                    broadcast(new MessageSent($message->load('user'), $request->to_users[$i]))->toOthers();
                }

                // broadcast(new UsersCommunication($message->load('user'), $user))->toOthers();
            }

        }else{
            if(!$request->message){
                return;
            }
            $message = $user->messages()->create([
                'message' => $request->message,
                'room_id' => $request->room_id,
                'file' => $file_path,
            ]);

            // event(new MessageSent($message->load('user')));
            for($i = 0; $i < count($request->to_users); $i++){
                broadcast(new MessageSent($message->load('user'), $request->to_users[$i]))->toOthers();
            }

            // broadcast(new UsersCommunication($message->load('user'), $user))->toOthers();

        }


        $room->last_message = $request->message;
        $room->save();
        return $message;

    }

    public function deleteRoom(Request $request) {
        $room = Room::find($request->room['id']);
        if($request->room['type'] === 'dm') {
            DB::table('room_user')->where('room_id',$request->room['id'])->where('user_id',$request->user_id)->update(['exists' => 1]);

        }else {
            $pivot = DB::table('room_user')->where('room_id',$request->room['id'])->where('user_id',$request->user_id)->delete();

            $updateUsers = json_decode($room->users);
            for($i = 0; $i < count($updateUsers); $i++) {
                if($updateUsers[$i]->user_id === $request->user_id){
                    array_splice($updateUsers, $i, 1);
                    break;
                }
            };
            $room->users = json_encode($updateUsers);
        }
        return $room;
    }

    public function getRooms($id) {
        $user = User::find($id);
        // $user_id = $id;
        // $rooms = $user->myRooms()->get();
        $chatroom = Room::query()->leftJoin('room_user', 'rooms.id', '=', 'room_user.room_id')
        ->where('room_user.user_id', $user->id)
        ->whereIn('rooms.id', function ($query) use ($id) {return $query->select('room_id')->from('room_user')->where('exists', false)->where('user_id',$id)->get(); })->select('rooms.*')->get();
        return $chatroom;
    }

    public function inviteUser(Request $request) {
        // return $request;
        $room = Room::find($request->room['id']);
        // return $room['type'];
        $users = $request->inviteUsers;
        // return gettype($users[0]['id']);
        // return gettype($request->user);
        $users1 = [];
        if($room['type'] == 'dm') {
            // array_push($users, (object)($request->user));   //  그룹 유저들부터 users에 넣어줘야되는데 따로 돌려서
            $users1 = [...$users1, ...json_decode($room->users)];

            $room = new Room();
            for($i = 0; $i < count($users); $i++){
                array_push($users1, (object)array('user_id'=>$users[$i]['id'], 'user_name'=>$users[$i]['name']));  //여기 에러
            }
            $room->users = json_encode($users1);
            $room->type = "group";
            $room->save();
            // return $users1[0]->user_id;
            for($i=0; $i < count($users1); $i++) {
                // $rooms = [];
                $user = User::find($users1[$i]->user_id);
                $user->myRooms()->attach($room->id);
            }
        }else {
            // return $room;
            $users1 = json_decode($room->users);
            for($i = 0; $i < count($users); $i++){
                array_push($users1, (object)array('user_id'=>$users[$i]['id'], 'user_name'=>$users[$i]['name'], 'position' => $users[$i]['position']));
            }
            $room->users = json_encode($users1);
            $room->save();
            $users1 = [...$users];
            // return $users1[0]['id'];
            // return $users1;
            // for($i = 0; $i < count($users); $i++){
            //     array_push($users1, (object)array('user_id'=>$users[$i]['id'], 'user_name'=>$users[$i]['name']));
            // }
            for($i=0; $i < count($users1); $i++) {
                // $rooms = [];

                $user = User::find($users1[$i]['id']);
                $user->myRooms()->attach($room->id);
            }
        }
        return $room;
    }

    public function createRoom(Request $request) {  //users에 user 객체 넣기
        // return $request->users;
        $users =  $request->users;
        // return $users;
        // return gettype($users[0]['id']);
        $type = '';
        if (count($users) === 2) {
            $type = 'dm';
            $to_user = User::find($users[0]['id']);
            $to_user_id = $users[0]['id'];
            $user = User::find($users[1]['id']);
            $chatroom = Room::query()->leftJoin('room_user', 'rooms.id', '=', 'room_user.room_id')
                ->where('rooms.type',$type)
                ->where('room_user.user_id', $user->id)
                ->whereIn('rooms.id', function ($query) use ($to_user_id) {return $query->select('room_id')->from('room_user')->where('user_id', $to_user_id)->get(); })->select('rooms.*')->get();
            // return $chatroom[0]->id;
            // DB::table('room_user')->where('room_id',$request->room['id'])->where('user_id',$request->user_id)->delete();
            try {
                if($chatroom[0]){
                    DB::table('room_user')->where('room_id',$chatroom[0]->id)->where('user_id',$user->id)->update(['exists' => 0]);
                }
                return $chatroom[0];
            }catch(Throwable $err) {

            };
        }else {
            $type = 'group';
        }

        $users1 = [];
        $room = new Room();
        if($request->title) {
            $room->title = $request->title;
        }elseif($request->password) {
            $room->password = $request->password;
        }

        for($i = 0; $i < count($users); $i++){
            array_push($users1, (object)array('user_id'=>$users[$i]['id'], 'user_name'=>$users[$i]['name'], 'position' => $users[$i]['position']));
        }
        $room->users = json_encode($users1);
        $room->type = $type;
        $room->save();


        for($i=0; $i < count($users); $i++) {
            // $rooms = [];
            $user = User::find($users[$i]['id']);
            // if($user->rooms){
            //     $rooms = json_decode($user->rooms);
            // }
            // array_push($rooms,$room->id);
            // $user->rooms = json_encode($rooms);
            // $user->save();
            // return $user->rooms();
            $user->myRooms()->attach($room->id);
        }


        return $room;
    }
}
