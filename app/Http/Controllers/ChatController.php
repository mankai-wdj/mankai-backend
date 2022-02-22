<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\Environment\Console;
use Throwable;

use function PHPUnit\Framework\isNull;

class ChatController extends Controller
{
    public function getMessages($id) {

        $messages = Room::find($id)->messages()->with('user')->get();
        return $messages;
    }

    public function sendMessage(Request $request) {
        $file_path = null;
        $images = [];
        $user = User::find($request->user_id);
        // dd($request->file());
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

                        broadcast(new MessageSent($message->load('user')))->toOthers();
                    }
                }

                $message = $user->messages()->create([
                    'message' => $request->message,
                    'room_id' => $request->room_id,
                    'file' => json_encode($images),
                ]);

                broadcast(new MessageSent($message->load('user')))->toOthers();
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

                broadcast(new MessageSent($message->load('user')))->toOthers();
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

            broadcast(new MessageSent($message->load('user')))->toOthers();

        }
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
            $room->save();
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

    public function createRoom(Request $request) {  //users에 user 객체 넣기
        // return $request->users;
        $users =  $request->users;
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
            array_push($users1, (object)array('user_id'=>$users[$i]['id'], 'user_name'=>$users[$i]['name']));
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
