<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use getID3;
use App\Services\Videos\GoogleDriveServiceVideo; // Make sure this service is built
use App\Services\Videos\GoogleDriveServicePDF; // Make sure this service is built
use App\Services\Videos\GoogleDriveServiceImage; // Make sure this service is built
use App\Services\Videos\GoogleDriveServiceThumbnail; // Make sure this service is built
use Google\Service\MyBusinessBusinessInformation\Resource\Categories;
use Illuminate\Support\Facades\Validator; // For input validation
// use App\Services\NotificationService;

class MediaController extends Controller
{
    protected $client;
    protected $driveServiceVideo;
    protected $driveServiceImage;
    protected $driveServicePDF;
    protected $driveServiceThumbnail;
    // protected $notificationService;


    public function __construct(
        GoogleDriveServiceVideo $driveServiceVideo,
        GoogleDriveServicePDF $driveServicePDF,
        GoogleDriveServiceImage $driveServiceImage,
        GoogleDriveServiceThumbnail $driveServiceThumbnail,
        // NotificationService $notificationService

    ) {

        // $this->notificationService = $notificationService;

        $this->driveServiceVideo = $driveServiceVideo;
        $this->client = $this->driveServiceVideo->getClient(); // Ensure this method exists in the service

        $this->driveServicePDF = $driveServicePDF;
        $this->client = $this->driveServicePDF->getClient(); // Ensure this method exists in the service

        $this->driveServiceImage = $driveServiceImage;
        $this->client = $this->driveServiceImage->getClient(); // Ensure this method exists in the service

        $this->driveServiceThumbnail = $driveServiceThumbnail;
        $this->client = $this->driveServiceThumbnail->getClient(); // Ensure this method exists in the service
    }
    public function show()
    {
        try {
            $categoriesPending = Category::with(['media' => function ($query) {
                $query->whereIn('status', ['pending', 'inreview'])->withCount('comments', 'likes');
            }])->get();
            $users = User::all();

            $categories = Category::with(['media' => function ($query) {
                $query->whereNotIn('status', ['pending', 'inreview'])->withCount('comments', 'likes');
            }])->get();
            return response()->json([
                'success' => true,
                'message' => 'Media categories retrieved successfully.',
                'data' => [
                    'categories' => $categories,
                    'users' => $users,
                    'categoriesPending' => $categoriesPending
                ]
            ], 200);
        } catch (Exception $e) {
            LaravelLog::error('Media retrieval error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve media.'], 500);
        }
    }
    public function store(Request $request)
    {
$mention = $request->mention;
eval('$arr_mention = ' . $mention . ';');


// Get the original file name
        $originalName = $request->file('file')->getClientOriginalName();

        try {
            // Validate input
            $validated = $request->validate([
                'year' => 'required|digits:4',
                'month' => 'required',
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'required|file|mimes:mp4,avi,mov|max:51200', // 50MB limit
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'thumbnail_path' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'is_featured' => 'nullable|boolean',
                'is_favorite' => 'nullable|boolean',
                // 'mention' => 'nullable|array',
                // 'mention.*' => 'nullable|string|max:255',
            ]);

            $category = Category::firstOrCreate(
                [
                    'name' => $validated['year']
                ],
                [
                    'user_id' => $validated['user_id'],
                    'description' => "Category for year {$validated['year']}"
                ]
            );

            // Find or create subcategory (month)
            $subCategory = SubCategory::firstOrCreate(
                [
                    'name' => $validated['month'],
                    'category_id' => $category->id
                ],
                [
                    'description' => "Subcategory for {$validated['month']} {$validated['year']}"
                ]
            );

            $mentions = collect($arr_mention)
                ->filter()
                ->map(fn($item) => trim($item))
                ->values()
                ->toArray();

            $getID3 = new getID3();
            $duration = null;

            // Get video duration
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $videoPath = $request->file('file')->getRealPath();
                $fileInfo = $getID3->analyze($videoPath);
                $duration = isset($fileInfo['playtime_seconds']) ? floatval($fileInfo['playtime_seconds']) : null;
            }


            $video = null;
            if ($request->hasFile('file')) {
                $driveService = new GoogleDriveServiceVideo();
                if ($request->file('file')->isValid()) {
                    $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                    $url = $driveService->uploadFile($request->file('file'), $filename);
                    $video = 'https://drive.google.com/file/d/' . $url . '/preview';
                }
            }

            $pdf = null;
            if ($request->hasFile('pdf')) {
                $driveServicePDF = new GoogleDriveServicePDF();
                if ($request->file('pdf')->isValid()) {
                    $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                    $url = $driveServicePDF->uploadPdf($request->file('pdf'), $filename);
                    $pdf = 'https://drive.google.com/file/d/' . $url . '/preview';
                }
            }

            // Store thumbnail if exists
            $thumbnailPath = null;

            if ($request->hasFile('thumbnail_path')) {
                $driveServiceThumbnail = new GoogleDriveServiceThumbnail();
                if ($request->file('thumbnail_path')->isValid()) {
                    $filename = time() . '_' . $request->file('thumbnail_path')->getClientOriginalName();
                    $url = $driveServiceThumbnail->uploadThumbnail($request->file('thumbnail_path'), $filename);
                    $thumbnailPath =  'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
                }
            }

            $imagePath = null;

            if ($request->hasFile('image_path')) {
                $driveServiceImage = new GoogleDriveServiceImage();
                if ($request->file('image_path')->isValid()) {
                    $filename = time() . '_' . $request->file('image_path')->getClientOriginalName();
                    $url = $driveServiceImage->uploadImage($request->file('image_path'), $filename);
                    $imagePath =  'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
                }
            }


            // Save to database
            $media = Media::create([
                'user_id' => $validated['user_id'],
                'category_id' => $category->id,
                'sub_category_id' => $subCategory->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'file_path' => $video,
                'pdf' => $pdf,
                'mention' => json_encode($mentions),
                'thumbnail_path' => $thumbnailPath,
                'image_path' => $imagePath,
                'status' => 'pending', // Default status
                'is_featured' => $request->boolean('is_featured'),
                'is_favorite' => $request->boolean('is_favorite'),
                'duration' => $duration, // Save duration

            ]);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_upload_success',
                'description' => 'Uploaded media: ' . $media->title,
            ]);
            $user_name = User::find($validated['user_id'])->name;

            $user = $validated['user_id'];
            $title = "New " . $media->status . " media uploaded: ";
            $body = "The " . $media->title . " uploaded successfull with status "  . $media->status . " by " . $user_name . ". Please review it.";
            $route = "/media_details/";

            // $this->notificationService->sendNotification(
            //     $request->user(), // HR user as sender
            //     $user,            // Requesting user as receiver
            //     $title,
            //     $body,
            //     $route,
            //     $media->id
            // );

            return response()->json([
                'message' => 'Media uploaded successfully.' . ' (Duration: ' . ($duration ? round($duration, 2) : 'N/A') . ' seconds)',
                'media' => $media
            ], 201);
        } catch (Exception $e) {
            LaravelLog::error('Media upload error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_upload_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Media upload failed.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function views(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'video_id' => 'required|string'
            ]);

            $video_id = $validated['video_id'];
            $media = Media::where('file_path', $video_id)->first();

            if (!$media) {
                throw new Exception('Media not found.');
            }

            $media->views += 1;
            $media->save();

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_view_success',
                'description' => 'Viewed media: ' . $media->title,
            ]);

            return response()->json($media, 200);
        } catch (Exception $e) {
            LaravelLog::error('Media view error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_view_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to update media views.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function recently_Added(Request $request)
    {
        $userId = auth()->check() ? auth()->user()->id : null;

        try {
            if (!auth()->check()) {
                $contentswithout = Category::with(['media' => function ($query) {
                    $query->where('status', 'published')
                        ->withCount('comments');
                }])->orderBy('created_at', 'desc')->take(10)->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Recently added media retrieved successfully without.',
                    'data' => $contentswithout
                ], 200);
            } else {
                $contents = Category::with(['media' => function ($query) use ($userId) {
                    $query->where('status', 'published')
                        ->withCount(['comments', 'likes'])
                        ->withExists(['likes as is_liked' => function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        }]);
                }])->orderBy('created_at', 'desc')->take(10)->get();

                foreach ($contents as $category) {
                    foreach ($category->media as $media) {
                        $is_favorite = Bookmark::where('media_id', $media->id)
                            ->where('user_id', $userId)
                            ->exists();
                        $media->is_favorite = $is_favorite;
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Recently added media retrieved successfully.',
                    'data' => $contents
                ], 200);
            }
        } catch (Exception $e) {
            // Log the error to Laravel's log system
            LaravelLog::error('Recently Added error: ' . $e->getMessage());

            // If you have a Log model for database logging
            if ($userId) { // Only log to database if $userId is set
                Log::create([
                    'user_id' => $userId,
                    'type' => 'recently_added_error',
                    'description' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch recently added media.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function featured(Request $request)
    {

        try {
            $userId = auth()->check() ? auth()->user()->id : null;


            if (!auth()->check()) {
                $contents = Category::with(['media' => function ($query) {
                    $query->where('status', 'published')
                        ->where('is_featured', true)
                        ->withCount('comments');
                }])->orderBy('created_at', 'desc')->take(10)->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Featured media retrieved successfully.',
                    'data' => $contents
                ], 200);
            } else {
                $contents = Category::with(['media' => function ($query) use ($userId) {
                    $query->where('status', 'published')
                        ->where('is_featured', true)
                        ->withCount(['comments', 'likes'])
                        ->withExists(['likes as is_liked' => function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        }]);
                }])->orderBy('created_at', 'desc')->take(10)->get();

                // Optimize bookmark check
                $bookmarkedMediaIds = Bookmark::where('user_id', $userId)
                    ->pluck('media_id')
                    ->toArray();

                // Add is_favorite to each media item
                foreach ($contents as $category) {
                    foreach ($category->media as $media) {
                        $media->is_favorite = in_array($media->id, $bookmarkedMediaIds);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Featured media retrieved successfully.',
                    'data' => $contents
                ], 200);
            }
        } catch (Exception $e) {
            // Log to Laravel's logging system
            LaravelLog::error('Featured error: ' . $e->getMessage());

            // Log to database only if $userId is set
            if ($userId) {
                Log::create([
                    'user_id' => $userId,
                    'type' => 'featured_error',
                    'description' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch featured media.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {

        try {

            // Validate input
            $validated = $request->validate([
                'year' => 'required|digits:4',
                'month' => 'required',
                'user_id' => 'required|exists:users,id',
                'media_id' => 'required|exists:media,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'nullable|file|mimes:mp4,avi,mov|max:51200', // 50MB limit
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'thumbnail_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'is_featured' => 'nullable|boolean',
                'is_favorite' => 'nullable|boolean',
                'mention' => 'nullable|array',
                'mention.*' => 'nullable|string|max:255',
            ]);
            $media = Media::findOrFail($validated['media_id']);

            $category = Category::firstOrCreate(
                [
                    'name' => $validated['year']
                ],
                [
                    'user_id' => $validated['user_id'],
                    'description' => "Category for year {$validated['year']}"
                ]
            );

            // Find or create subcategory (month)
            $subCategory = SubCategory::firstOrCreate(
                [
                    'name' => $validated['month'],
                    'category_id' => $category->id
                ],
                [
                    'description' => "Subcategory for {$validated['month']} {$validated['year']}"
                ]
            );

            // Clean up mentions array
            $mentions = collect($request->input('mention', []))
                ->filter()
                ->map(fn($item) => trim($item))
                ->values()
                ->toArray();

            $getID3 = new getID3();
            $duration = $media->duration;

            // Update video duration if new file is uploaded
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $videoPath = $request->file('file')->getRealPath();
                $fileInfo = $getID3->analyze($videoPath);
                $duration = isset($fileInfo['playtime_seconds']) ? floatval($fileInfo['playtime_seconds']) : null;
            }

            // Update video file on Google Drive
            $video = $media->file_path;
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $driveServiceVideo = new GoogleDriveServiceVideo();
                // Delete old file from Google Drive if exists
                if ($media->file_path) {
                    $fileId = $driveServiceVideo->getFileIdFromUrl($media->file_path);
                    if ($fileId) {
                        $driveServiceVideo->deleteFile($fileId);
                    }
                }
                // Upload new file
                $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                $video = $driveServiceVideo->uploadFile($request->file('file'), $filename);
                $video = 'https://drive.google.com/file/d/' . $video . '/preview';
            }

            // Update PDF file on Google Drive
            $pdf = $media->pdf;
            if ($request->hasFile('pdf') && $request->file('pdf')->isValid()) {
                $driveServicePDF = new GoogleDriveServicePDF();
                // Delete old PDF from Google Drive if exists
                if ($media->pdf) {
                    $fileId = $driveServicePDF->getFileIdFromUrl($media->pdf);
                    if ($fileId) {
                        $driveServicePDF->deleteFile($fileId);
                    }
                }
                // Upload new PDF

                $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                $pdf = $driveServicePDF->uploadPdf($request->file('pdf'), $filename);
                $pdf = 'https://drive.google.com/file/d/' . $pdf . '/preview';
            }

            // Update thumbnail if exists
            $thumbnailPath = $media->thumbnail_path;
            if ($request->hasFile('thumbnail_path') && $request->file('thumbnail_path')->isValid()) {
                $driveServiceThumbnail = new GoogleDriveServiceThumbnail();
                // Delete old thumbnail from Google Drive if exists
                if ($media->thumbnail_path) {
                    $fileId = $driveServiceThumbnail->getFileIdFromUrl($media->thumbnail_path);
                    if ($fileId) {
                        $driveServiceThumbnail->deleteFile($fileId);
                    }
                }
                // Upload new thumbnail
                $filename = time() . '_' . $request->file('thumbnail_path')->getClientOriginalName();
                $thumbnailPath = $driveServiceThumbnail->uploadThumbnail($request->file('thumbnail_path'), $filename);
                $thumbnailPath = 'https://lh3.googleusercontent.com/d/' . $thumbnailPath . '=w1000?authuser=0';
            }

            // Update image if exists
            $imagePath = $media->image_path;
            if ($request->hasFile('image_path') && $request->file('image_path')->isValid()) {
                $driveServiceImage = new GoogleDriveServiceImage();
                // Delete old image from Google Drive if exists
                if ($media->image_path) {
                    $fileId = $driveServiceImage->getFileIdFromUrl($media->image_path);
                    if ($fileId) {
                        $driveServiceImage->deleteFile($fileId);
                    }
                }
                // Upload new image
                $filename = time() . '_' . $request->file('image_path')->getClientOriginalName();
                $imagePath = $driveServiceImage->uploadImage($request->file('image_path'), $filename);
                $imagePath = 'https://lh3.googleusercontent.com/d/' . $imagePath . '=w1000?authuser=0';
            }

            // Update database
            $media->update([
                'category_id' => $category->id,
                'sub_category_id' => $subCategory->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'file_path' => $video,
                'pdf' => $pdf,
                'thumbnail_path' => $thumbnailPath,
                'image_path' => $imagePath,
                'is_featured' => $request->boolean('is_featured'),
                'is_favorite' => $request->boolean('is_favorite'),
                'mention' => json_encode($mentions),
                'duration' => $duration,
            ]);

            // Log success
            Log::create([
                'user_id' => $validated['user_id'],
                'type' => 'media_update_success',
                'description' => 'Updated media: ' . $media->title,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Media updated successfully.',
                'data' => $media
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Media update error: ' . $e->getMessage());

            Log::create([
                'user_id' => $validated['user_id'],
                'type' => 'media_update_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Media update failed: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getAllCategories(Request $request)
    {
        try {
            // Fetch all categories with their subcategories
            $categories = Category::with(['subcategories' => function ($query) {
                $query->orderByRaw("FIELD(name, 'December', 'November', 'October', 'September', 'August', 'July', 'June', 'May', 'April', 'March', 'February', 'January')");
            }])
                ->orderBy('name', 'desc')
                ->get();
            return response()->json([
                'success' => true,
                'message' => 'Categories and subcategories retrieved successfully.',
                'data' => [
                    'categories' => $categories,
                ]
            ], 200);
        } catch (Exception $e) {
            // Log to Laravel's logging system
            Log::error('Categories retrieval error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve categories.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function subCategoryDetails(Request $request)
    {
        try {

            // Validate sub_category_id and user_id
            $validator = Validator::make($request->all(), [
                'sub_category_id' => 'required|integer',
                'user_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid input.',
                    'message' => $validator->errors()->first(),
                ], 400);
            }
            $subCategoryId = (int) $request->sub_category_id;
            $userId = (int) $request->user_id;
            $subCategoryDetails = Media::where('sub_category_id', $subCategoryId)
                ->where('user_id', $userId)
                ->where('status', 'published')

                ->with(['likes', 'comments'])->withExists(['likes as is_liked' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Sub Category details retrieved successfully.',
                'data' =>  $subCategoryDetails,

            ], 200);
        } catch (Exception $e) {
            LaravelLog::error('Sub Category details retrieval error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve categories.'], 500);
        }
    }

    public function viewsCount(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'media_id' => 'required|integer'
            ]);

            $media_id = $validated['media_id'];
            $media = Media::find($media_id);

            if (!$media) {
                throw new Exception('Media not found.');
            }

            // Increment the views column
            $media->increment('views');

            return response()->json([
                'views' => $media->views,
                'message' => 'Views count updated and retrieved successfully.'
            ], 200);
        } catch (Exception $e) {
            LaravelLog::error('Media views count error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update or retrieve media views count.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getMediaByUserId(Request $request)
    {
        try {
            $userId = auth()->check() ? auth()->user()->id : null;

            // Query for Pending Media
            $PendingMediaQuery = Media::query()
                ->when($userId, function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                })
                ->where('status', 'pending')
                ->with(['category'])
                ->withCount(['AdminComment', 'likes'])
                ->when($userId, function ($query) use ($userId) {
                    return $query->withExists(['likes as is_liked' => function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    }]);
                });

            $PendingMedia = $PendingMediaQuery->first();

            // Query for Published Media
            $PublishedMediaQuery = Media::query()
                ->when($userId, function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                })
                ->where('status', '!=', 'pending')
                ->with(['category'])
                ->withCount(['comments', 'likes'])
                ->when($userId, function ($query) use ($userId) {
                    return $query->withExists(['likes as is_liked' => function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    }]);
                });

            $PublishedMedia = $PublishedMediaQuery->first();

            // Add is_favorite only if user is authenticated
            if ($PendingMedia != null) {
                $PendingMedia->is_favorite = Bookmark::where('user_id', $userId)->exists();
            }else {
                $PendingMedia = [];
            }


            if ($PublishedMedia != null) {
                $PublishedMedia->is_favorite = Bookmark::where('user_id', $userId)->exists();
            }
            else {
                $PublishedMedia = [];
            }

            // Return the media details
            return response()->json([
                'success' => true,
                'message' => 'Media retrieved successfully.',
                'data' => [
                    'pending' => [$PendingMedia],
                    'published' => [$PublishedMedia]
                ]
            ], 200);
        } catch (Exception $e) {
            LaravelLog::error('Error retrieving media: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve media.'], 500);
        }
    }

    public function getMediaByMediaId(Request $request)
    {
        try {
            $media_id = (int) $request->media_id;
            $userId = auth()->check() ? auth()->user()->id : null;

            // Query for Media
            $mediaQuery = Media::where('id', $media_id)
                ->with(['category'])
                ->withCount(['comments', 'likes'])
                ->when($userId, function ($query) use ($userId) {
                    return $query->withExists(['likes as is_liked' => function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    }]);
                });

            $media = $mediaQuery->first();
            // Add is_favorite only if user is authenticated
            if ($media != null) {
                $media->is_favorite = Bookmark::where('user_id', $userId)
                    ->where('media_id', $media_id)
                    ->exists();
            } else {
                $media =[];
            }
            // Return the media details
            return response()->json([
                'success' => true,
                'message' => 'Media retrieved successfully.',
                'data' => $media
            ], 200);
        } catch (Exception $e) {
            LaravelLog::error('Error retrieving media: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve media.'], 500);
        }
    }

    public function getMediaByCategoryId(Request $request)
    {
        try {
            $category_id = (int) $request->category_id;
            $userId = auth()->check() ? auth()->user()->id : null;

            // Query for Media
            $mediaQuery = Media::where('category_id', $category_id)
                ->where('status', '!=', 'pending')
                ->with(['category'])
                ->withCount(['comments', 'likes'])
                ->when($userId, function ($query) use ($userId) {
                    return $query->withExists(['likes as is_liked' => function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    }]);
                });

            $media = $mediaQuery->first();

            // Add is_favorite only if user is authenticated and media exists
            if ($media != null) {
                $media->is_favorite = Bookmark::where('user_id', $userId)
                    ->where('media_id', $media->id)
                    ->exists();
                    return response()->json([
                'success' => true,
                'message' => 'Media retrieved successfully.',
                'data' =>[$media]
            ], 200);
            } 
            else {
                $media = [];
                return response()->json([
                'success' => true,
                'message' => 'Media retrieved successfully.',
                'data' =>$media
            ], 200);
            }

            
        } catch (Exception $e) {
            LaravelLog::error('Error retrieving media: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve media.'], 500);
        }
    }
    public function destroy(Request $request)
    {
        try {
            $id = $request->input('media_id');
            $user_id = $request->input('user_id');

            $media = Media::where('id', $id)->first();

            // Delete video file from Google Drive if exists
            if ($media->file_path) {
                $fileId = $this->driveServiceVideo->getFileIdFromUrl($media->file_path);
                if ($fileId) {
                    $this->driveServiceVideo->deleteFile($fileId);
                }
            }

            // Delete PDF file from Google Drive if exists
            if ($media->pdf) {
                $fileId = $this->driveServicePDF->getFileIdFromUrl($media->pdf);
                if ($fileId) {
                    $this->driveServicePDF->deleteFile($fileId);
                }
            }

            // Delete thumbnail from Google Drive if exists
            if ($media->thumbnail_path) {
                $fileId = $this->driveServiceThumbnail->getFileIdFromUrl($media->thumbnail_path);
                if ($fileId) {
                    $this->driveServiceThumbnail->deleteFile($fileId);
                }
            }

            // Delete image from Google Drive if exists
            if ($media->image_path) {
                $fileId = $this->driveServiceImage->getFileIdFromUrl($media->image_path);
                if ($fileId) {
                    $this->driveServiceImage->deleteFile($fileId);
                }
            }

            // Log the deletion
            Log::create([
                'user_id' => $user_id,
                'type' => 'media_delete_success',
                'description' => 'Deleted media: ' . $media->title,
            ]);

            // Delete the media record from the database
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            LaravelLog::error('Media delete error: ' . $e->getMessage());

            Log::create([
                'user_id' => $user_id,
                'type' => 'media_delete_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete media: ' . $e->getMessage()
            ], 500);
        }
    }
}
