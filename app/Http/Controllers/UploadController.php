<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Kiểm tra loại tệp và kích thước tối đa
        ]);

        if ($request->file('image')) {
            $path = $request->file('image')->store('public/temp'); // Lưu tệp vào thư mục 'public/images'
            $url = Storage::url($path);
            return response()->json(['message' => 'Tải lên thành công', 'url' => url($url)]);

        }
        return $this->sendError('Upload error');
    }
}
