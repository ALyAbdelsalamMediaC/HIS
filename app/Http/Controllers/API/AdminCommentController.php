<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\AdminComment;
use App\Models\Media;
use App\Models\Rate;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminCommentController extends Controller
{
    public function showAdminComment(Request $request)
    {
        try {
                        $media_id = $request->media_id;
           $comments = AdminComment::where('media_id', $media_id)
            ->whereNull('parent_id')
            ->with(['replies' => function ($query) {
                $query->select('id', 'user_id', 'media_id', 'parent_id', 'content', 'created_at', 'updated_at')->with('user');
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'AdminComments' => $comments
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'An unexpected error occurred.',
        ], 500);
    }
    }

    public function addComment(Request $request)
    {
        try {
            // Validate the request

            // Get the authenticated user
            $userId = $request->user_id;
            $media_id = $request->media_id;

            $Media = Media::where('id',$media_id)->where('status','pending')->first();

            // Verify media exists
            if (!$Media) {
                return redirect()->back()
                    ->with('error', 'The specified media does not exist.')
                    ->withInput();
            }

            // Create the comment
            AdminComment::create([
                'user_id' => $userId,
                'media_id' => $media_id,
                'parent_id' => null,
                'content' => $request->content,
            ]);


            return response()->json([
                'success' => true,
                'comment' => 'Comment added successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Media not found.')
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while adding the comment.')
                ->withInput();
        }
    }

    public function reply(Request $request)
    {
        try {

            $userId = $request->user_id;
            $mediaId = $request->media_id;
            $parentId = $request->parent_id;
            $content = $request->content;


            // Verify media and parent comment exist
            $media = Media::findOrFail($mediaId)->where('status','pending')->first();
            $parentComment = AdminComment::findOrFail($parentId);

            // Verify that parent comment belongs to the specified media
            if ($parentComment->media_id != $mediaId) {
                return redirect()->back()
                    ->with('error', 'The parent comment does not belong to the specified media.')
                    ->withInput();
            }

            // Create the reply
            AdminComment::create([
                'user_id' => $userId,
                'media_id' => $mediaId,
                'parent_id' => $parentId,
                'content' => $content,
            ]);
                return response()->json([
                'success' => true,
                'comment' => 'Reply added successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Media or parent comment not found.')
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while adding the reply.')
                ->withInput();
        }
    }
}
