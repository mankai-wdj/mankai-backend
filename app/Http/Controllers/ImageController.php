<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function Store(Request $request)
    {
        $i = 0;
        $path = array();
        while ($request->hasFile("images{$i}") == true) {
            $path[$i] = $request->file("images{$i}")->store('image', 's3');
            $i++;
        }
        $j = 0;
        while ($j < $i) {
            $image = Image::create([
                'filename' => basename($path[$j]),
                'url' => Storage::url($path[$j]),
                'free_boards_id' => $request->post_id,
            ]);
            $j++;
        }
        // 이제 Read/Update/Delete를 할 수 있게 하면된다. 
        return $path;
    }

    public function show($post_id)
    {
        $images = Image::where('free_boards_id', $post_id)->get();

        return $images;
    }
}
