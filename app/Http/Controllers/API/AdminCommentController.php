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
            $validator = Validator::make($request->all(), [
                'media_id' => 'required|integer|exists:media,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $comments = AdminComment::where('media_id', $request->media_id)->get();

            return response()->json([
                'success' => true,
                'data' => $comments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred.',
            ], 500);
        }
    }
    public function reply(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'content' => 'required|string|min:1|max:5000',
                'media_id' => 'required|integer|exists:media,id',
                'parent_id' => 'required|integer|exists:admin_comments,id',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Get the authenticated user
            $user = auth()->user();
            if (!$user || empty($user->email)) {
                return redirect()->back()
                    ->with('error', 'You must be logged in with a valid email address to reply.')
                    ->withInput();
            }

            // Verify media and parent comment exist
            $media = Media::findOrFail($validator['media_id']);
            $parentComment = AdminComment::findOrFail($validator['parent_id']);

            // Verify that parent comment belongs to the specified media
            if ($parentComment->media_id != $validator['media_id']) {
                return redirect()->back()
                    ->with('error', 'The parent comment does not belong to the specified media.')
                    ->withInput();
            }

            // Create the reply
            AdminComment::create([
                'user_id' => $user->id,
                'media_id' => $validator['media_id'],
                'parent_id' => $$validator['parent_id'],
                'content' => $request->content,
            ]);

            return redirect()->back()
                ->with('success', 'Reply added successfully.');

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
