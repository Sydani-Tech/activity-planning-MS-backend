<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Events\CommentPosted;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(Activity $activity)
    {
        return CommentResource::collection($activity->comments()->with('user')->get());
    }

    public function store(Request $request, Activity $activity)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $comment = $activity->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment
        ]);

        $comment->load('user');

        broadcast(new CommentPosted($comment))->toOthers();

        return new CommentResource($comment);
    }
}
