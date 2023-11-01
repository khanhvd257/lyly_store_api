<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReplyRatingValidation;
use App\Models\Ratings;
use App\Models\ReplyRating;
use Illuminate\Support\Facades\Auth;

class ReplyRatingController extends Controller
{
    public function replyRating(ReplyRatingValidation $request)
    {
        $rating = Ratings::find($request['rating_id']);
        if (!$rating) return $this->sendError('Không thấy bài đánh giá');
        $username = Auth::guard('api')->user()['username'];
        $request['username'] = $username;
        $data = [
            'rating_id' => $request['rating_id'],
            'reply' => $request['reply'],
            'username' => $username,
        ];
        $reply = ReplyRating::create($data);
        return $this->sendResponse($reply, 'Phản hồi đánh giá thành công');
    }
}
