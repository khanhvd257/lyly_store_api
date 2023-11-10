<?php

namespace App\Http\Controllers;

use App\Http\Requests\RatingValidation;
use App\Http\Requests\ReplyRatingValidation;
use App\Models\Ratings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class RatingsController extends Controller
{
    public function getRating()
    {

        $rating = Ratings::with('product')->get();
        return $this->sendResponse($rating, 'Lấy danh sách đánh giá thành công');
    }

    public function getRatingByProduct(Request $request)
    {
        $product_id = $request->query('product_id');
        $rating = Ratings::where('product_id', $product_id)->get();
        return $this->sendResponse($rating, 'Lấy danh sách đánh giá thành công');
    }


    public function createRating(RatingValidation $request)
    {
        $username = Auth::guard('api')->user()['username'];
        $request['username'] = $username;
        if ($request['image_url']) {
            $image = basename($request['image_url']);
            $sourcePath = public_path('storage/temp/' . $image);
            $destinationPath = public_path('storage/images/');
            File::move($sourcePath, $destinationPath . $image);
            $request['image_name'] = $image;
            File::delete(public_path($sourcePath));
        } else {
            $request['image_name'] = null;
        }
        $rating = Ratings::create($request->all());
        if ($rating) return $this->sendResponse($rating, 'Đánh giá sản phẩm thành công');
        return $this->sendError('Xảy ra lỗi khi đánh giá');
    }

    public function replyRating(ReplyRatingValidation $request)
    {
        $username = Auth::guard('api')->user()['username'];
        $request['username'] = $username;
        if ($request['image_url']) {
            $image = basename($request['image_url']);
            $sourcePath = public_path('storage/temp/' . $image);
            $destinationPath = public_path('storage/images/');
            File::move($sourcePath, $destinationPath . $image);
            $request['image_name'] = $image;
            File::delete(public_path($sourcePath));
        } else {
            $request['image_name'] = null;
        }
        $rating = Ratings::create($request->all());
        if ($rating) return $this->sendResponse($rating, 'Đã phản hồi đánh giá');
        return $this->sendError('Xảy ra lỗi khi đánh giá');
    }

}
