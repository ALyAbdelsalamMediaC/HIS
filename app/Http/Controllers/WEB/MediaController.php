<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\AdminComment;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use App\Models\Category;
use App\Models\Like;
use App\Models\SubCategory;
use App\Models\User;
use App\Services\Videos\GoogleDriveServiceVideo; // Make sure this service is built
use App\Services\Videos\GoogleDriveServicePDF; // Make sure this service is built
use App\Services\Videos\GoogleDriveServiceImage; // Make sure this service is built
use App\Services\Videos\GoogleDriveServiceThumbnail; // Make sure this service is built
use getID3;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Comment;
use App\Models\Rate;
use App\Models\Review;

class MediaController extends Controller
{
    protected $client;
    protected $driveServiceVideo;
    protected $driveServicePDF;
    protected $driveServiceImage;
    protected $driveServiceThumbnail;

    public function __construct(
        GoogleDriveServiceVideo $driveServiceVideo,
        GoogleDriveServicePDF $driveServicePDF,
        GoogleDriveServiceImage $driveServiceImage,
        GoogleDriveServiceThumbnail $driveServiceThumbnail
    ) {


        $this->driveServiceVideo = $driveServiceVideo;
        $this->client = $this->driveServiceVideo->getClient(); // Ensure this method exists in the service

        $this->driveServicePDF = $driveServicePDF;
        $this->client = $this->driveServicePDF->getClient(); // Ensure this method exists in the service

        $this->driveServiceImage = $driveServiceImage;
        $this->client = $this->driveServiceImage->getClient(); // Ensure this method exists in the service

        $this->driveServiceThumbnail = $driveServiceThumbnail;
        $this->client = $this->driveServiceThumbnail->getClient(); // Ensure this method exists in the service
    }




    public function getall(Request $request)
    {
        try {
            $user = Auth::user();

            $categories = Category::all();
            $subCategories = collect(); // Default to empty collection
            
            // Get all subcategories grouped by category for frontend use
            $allSubCategories = SubCategory::with('category')->get();
            $subCategoriesByCategory = $allSubCategories->groupBy('category_id');
            
            // Handle both 'year' and 'category' parameters for backward compatibility
            $yearOrCategory = $request->input('year') ?? $request->input('category');
            
            if ($yearOrCategory) {
                $category = Category::where('name', $yearOrCategory)->first();
                if ($category) {
                    $subCategories = SubCategory::where('category_id', $category->id)->get();
                }
            } else {
                // Optionally, fetch all subcategories if no category is selected
                $subCategories = SubCategory::all();
            }
            if ($this->client->isAccessTokenExpired()) {
                return redirect('https://his.mc-apps.org/get-google-token.php?redirect=' . urlencode(url()->current()));
            }

            $query = Media::with('category', 'subCategory', 'comments')
                ->withCount('comments');

            // Apply role-based filtering
            if ($user->role === 'reviewer') {
                // For reviewers: get media where they are in assigned_to and status is pending or published
                $query->whereJsonContains('assigned_to', $user->id)
                    ->whereIn('status', ['inreview', 'published', 'declined']);
            } else {
                // For admins: apply status filter only if provided, otherwise get all media
                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }
            }

            // Search by title
            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->input('search') . '%');
            }

            // Filter by category name (handle both 'year' and 'category' parameters)
            $yearOrCategory = $request->input('year') ?? $request->input('category');
            if ($yearOrCategory) {
                $query->whereHas('category', function ($q) use ($yearOrCategory) {
                    $q->where('name', $yearOrCategory);
                });
            }

            // Filter by sub categories name (handle both 'month' and 'sub_categories' parameters)
            $monthOrSubCategory = $request->input('month') ?? $request->input('sub_categories');
            if ($monthOrSubCategory) {
                $query->whereHas('subCategory', function ($q) use ($monthOrSubCategory) {
                    $q->where('name', $monthOrSubCategory);
                });
            }

            // Filter by date (created_at)
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            // Order by latest and paginate
            $media = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

            // Get all users with role 'reviewer'
            $reviewers = User::where('role', 'reviewer')->get();

            return view('pages.content.videos', compact('media', 'reviewers', 'categories', 'subCategories', 'subCategoriesByCategory'));
        } catch (Exception $e) {
           LaravelLog::error('Media getall error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return back()->with('error', 'Failed to fetch media: ' . $e->getMessage());
        }
    }
    public function assignTo(Request $request, $id)
    {
        try {
            // Validate the request
            $request->validate([
                'reviewer_ids' => 'required|array|min:1',
                'reviewer_ids.*' => 'exists:users,id'
            ]);

            // Get reviewers from request body
            $reviewersArray = $request->input('reviewer_ids', []);

            // Clean up any whitespace, filter out empty values, and cast to int
            $reviewersArray = array_filter(array_map(function ($id) {
                return (int) trim($id);
            }, $reviewersArray));

            // Convert to JSON (array of integers)
            $reviewersJson = json_encode(array_values($reviewersArray));

            // Update media table using Eloquent
            Media::where('id', $id)
                ->update([
                    'assigned_to' => $reviewersJson,
                    'status' => 'inreview'
                ]);

            return back()->with('success', 'Reviewers assigned successfully.');
        } catch (Exception $e) {
            LaravelLog::error('Assign to error: ' . $e->getMessage());
            return back()->with('error', 'Failed to assign reviewers.');
        }
    }
    public function recently_Added()
    {
        try {
            $contents = Media::with('category')->orderBy('created_at', 'desc')->take(10)->get();
            return view('pages.content.recently_added', compact('contents'));
        } catch (Exception $e) {
            LaravelLog::error('Recently Added error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'recently_added_error',
                'description' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to fetch recently added media.');
        }
    }

    public function getone($id, $status)
    {
        $media = Media::with(['category', 'likes'])->findOrFail($id);

        // Get count of likes
        $likesCount = $media->likes->count();

        // Get comments with replies and user data - ORDER BY DESC for newest first
        $commentsData = Comment::where('media_id', $id)
            ->whereNull('parent_id')
            ->with(['replies' => function ($query) {
                $query->orderBy('created_at', 'desc')
                    ->with('user');
            }, 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $commentsCount = $commentsData->count();

        // You can pass these to the view if needed:
        // compact('media', 'likesCount', 'commentsCount', 'commentsData')
        $user = Auth::user();
        $userLiked = false;
        if ($user) {
            $userLiked = Like::where('user_id', $user->id)
                ->where('media_id', $media->id)
                ->exists();
        }

        if ($status === 'inreview') {
            if ($user && $user->role === 'admin') {
                $adminComments = AdminComment::where('media_id', $id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $reviewers = Review::where('media_id', $id)
                    ->whereNull('parent_id')
                    ->with(['replies' => function ($query) {
                        $query->orderBy('created_at', 'desc')
                            ->with('user');
                    }, 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($review) use ($id) {
                        // Get the rate for this reviewer and media
                        $rate = Rate::where('media_id', $id)
                            ->where('user_id', $review->user_id)
                            ->value('rate');
                        $review->rate = $rate;
                        return $review;
                    });
                $replys = Review::where('media_id', $id)->whereNotNull('parent_id')->get();
                $replysCount = $replys->count();
                $assignedReviewers = json_decode($media->assigned_to, true) ?? [];
                $assignedReviewersCount = count($assignedReviewers);
                // Fetch admin's own rate
                $myRate = Rate::where('media_id', $id)
                    ->where('user_id', $user->id)
                    ->value('rate');
                return view('pages.content.video.single_video_inreview_admin', compact('adminComments', 'media', 'likesCount', 'commentsCount', 'commentsData', 'userLiked', 'reviewers', 'replys', 'replysCount', 'assignedReviewersCount', 'myRate'));
            } elseif ($user && $user->role === 'reviewer') {
                $commentsData = Review::where('media_id', $id)
                    ->whereNull('parent_id')
                    ->with(['replies' => function ($query) {
                        $query->orderBy('created_at', 'desc')
                            ->with('user');
                    }, 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                $replys = Review::where('media_id', $id)->whereNotNull('parent_id')->get();
                $replysCount = $replys->count();
                $commentsCount = $commentsData->count();
                // Fetch reviewer's own rate
                $myRate = Rate::where('media_id', $id)
                    ->where('user_id', $user->id)
                    ->value('rate');
                return view('pages.content.video.single_video_inreview_reviewer', compact('media', 'likesCount', 'replys', 'replysCount', 'commentsCount', 'commentsData', 'userLiked', 'myRate'));
            }
        } elseif ($status === 'published') {
            return view('pages.content.video.single_video_published', compact('media', 'likesCount', 'commentsCount', 'commentsData', 'userLiked'));
        } elseif ($status === 'pending') {
            $adminComments = AdminComment::where('media_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            $assignedReviewerIds = json_decode($media->assigned_to, true) ?? [];
            $assignedReviewers = User::whereIn('id', $assignedReviewerIds)->get();

            // Get all users with role 'reviewer'
            $reviewers = User::where('role', 'reviewer')->get();

            return view('pages.content.video.single_video_pending', compact('media', 'commentsData', 'adminComments', 'assignedReviewers', 'reviewers'));
        } elseif ($status === 'declined') {
            $user = Auth::user();
            $adminComments = AdminComment::where('media_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            $assignedReviewerIds = json_decode($media->assigned_to, true) ?? [];
            $assignedReviewers = User::whereIn('id', $assignedReviewerIds)->get();

            // Get all users with role 'reviewer'
            $reviewers = User::where('role', 'reviewer')->get();

            if ($user && $user->role === 'reviewer') {
                $commentsData = Review::where('media_id', $id)
                    ->whereNull('parent_id')
                    ->with(['replies' => function ($query) {
                        $query->orderBy('created_at', 'desc')
                            ->with('user');
                    }, 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                $replys = Review::where('media_id', $id)->whereNotNull('parent_id')->get();
                $replysCount = $replys->count();
                $commentsCount = $commentsData->count();
                $myRate = Rate::where('media_id', $id)
                    ->where('user_id', $user->id)
                    ->value('rate');
                return view('pages.content.video.single_video_declined_reviwer', compact('media', 'replys', 'replysCount', 'commentsCount', 'commentsData', 'myRate'));
            }

            // Default: admin view
            return view('pages.content.video.single_video_declined', compact('media', 'commentsData', 'adminComments', 'assignedReviewers', 'reviewers'));
        }

        // Default fallback
        return back()->with('error', 'Invalid status or permissions.');
    }
    public function validation()
    {
        $categories = Category::all();

        if ($this->client->isAccessTokenExpired()) {
            return redirect('https://his.mc-apps.org/get-google-token.php?redirect=' . urlencode(url()->current()));
        } else {
            return view('pages.content.add_video', compact('categories'));
        }
    }

    public function create()
    {
        $categories = Category::all();
        $users = User::all();

        return view('pages.content.add_video', compact('categories', 'users'));
    }
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'year' => 'required|digits:4',
                'month' => 'required',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => $request->input('uploaded_video_path') ? 'nullable' : 'required|file|mimes:mp4|max:1048576', // 1GB in KB
                'uploaded_video_path' => 'nullable|string|regex:/^' . preg_quote(storage_path('app/uploads/'), '/') . '.+$/', // Validate path
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB
                'thumbnail_path' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB
                'is_featured' => 'nullable|boolean',
                'mention' => 'nullable|array',
                'mention.*' => 'nullable|string|max:255',
            ]);
    
            // Validate uploaded_video_path if provided
            if ($request->input('uploaded_video_path') && !file_exists($request->input('uploaded_video_path'))) {
                throw new \Exception('Invalid video path provided.');
            }
    
            $category = Category::firstOrCreate(
                [
                    'name' => $validated['year'],
                    'user_id' => Auth::id()
                ],
                [
                    'description' => "Category for year {$validated['year']}"
                ]
            );
    
            $subCategory = SubCategory::firstOrCreate(
                [
                    'name' => $validated['month'],
                    'category_id' => $category->id
                ],
                [
                    'description' => "Subcategory for {$validated['month']} {$validated['year']}"
                ]
            );
    
            $mentions = collect($request->input('mention', []))
                ->filter()
                ->map(fn($item) => trim($item))
                ->values()
                ->toArray();
    
            $getID3 = new getID3();
            $duration = null;
            $video = null;
    
            // Handle chunked upload
            $assembledPath = $request->input('uploaded_video_path');
            if ($assembledPath && file_exists($assembledPath)) {
                $videoPath = $assembledPath;
                $fileInfo = $getID3->analyze($videoPath);
                $duration = isset($fileInfo['playtime_seconds']) ? floatval($fileInfo['playtime_seconds']) : null;
                $driveService = new GoogleDriveServiceVideo();
                $filename = time() . '_' . basename($videoPath);
                LaravelLog::info('About to upload to Google Drive', ['videoPath' => $videoPath, 'filename' => $filename]);
                $url = $driveService->uploadFile(new \Illuminate\Http\File($videoPath), $filename);
                LaravelLog::info('Google Drive upload returned', ['url' => $url]);
                if (!$url) {
                    throw new \Exception('Failed to retrieve Google Drive file ID.');
                }
                $video = 'https://drive.google.com/file/d/' . $url . '/preview';
                unlink($videoPath); // Clean up
                LaravelLog::info('Google Drive upload complete, file ID: ' . $url);
            } elseif ($request->hasFile('file')) {
                if ($request->file('file')->isValid()) {
                    $videoPath = $request->file('file')->getRealPath();
                    $fileInfo = $getID3->analyze($videoPath);
                    $duration = isset($fileInfo['playtime_seconds']) ? floatval($fileInfo['playtime_seconds']) : null;
                    $driveService = new GoogleDriveServiceVideo();
                    $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                    LaravelLog::info('About to upload to Google Drive', ['videoPath' => $videoPath, 'filename' => $filename]);
                    $url = $driveService->uploadFile($request->file('file'), $filename);
                    LaravelLog::info('Google Drive upload returned', ['url' => $url]);
                    if (!$url) {
                        throw new \Exception('Failed to retrieve Google Drive file ID.');
                    }
                    $video = 'https://drive.google.com/file/d/' . $url . '/preview';
                    LaravelLog::info('Google Drive upload complete, file ID: ' . $url);
                }
            } else {
                throw new \Exception('No video file provided.');
            }
    
            $pdf = null;
            if ($request->hasFile('pdf') && $request->file('pdf')->isValid()) {
                $driveServicePDF = new GoogleDriveServicePDF();
                $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                $url = $driveServicePDF->uploadPdf($request->file('pdf'), $filename);
                $pdf = 'https://drive.google.com/file/d/' . $url . '/preview';
            }
    
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail_path') && $request->file('thumbnail_path')->isValid()) {
                $driveServiceThumbnail = new GoogleDriveServiceThumbnail();
                $filename = time() . '_' . $request->file('thumbnail_path')->getClientOriginalName();
                $url = $driveServiceThumbnail->uploadThumbnail($request->file('thumbnail_path'), $filename);
                $thumbnailPath = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
            }
    
            $imagePath = null;
            if ($request->hasFile('image_path') && $request->file('image_path')->isValid()) {
                $driveServiceImage = new GoogleDriveServiceImage();
                $filename = time() . '_' . $request->file('image_path')->getClientOriginalName();
                $url = $driveServiceImage->uploadImage($request->file('image_path'), $filename);
                $imagePath = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
            }
    
            $media = Media::create([
                'category_id' => $category->id,
                'sub_category_id' => $subCategory->id,
                'title' => $validated['title'],
                'user_id' => Auth::id(),
                'description' => $validated['description'] ?? null,
                'file_path' => $video,
                'pdf' => $pdf,
                'status' => 'published',
                'mention' => json_encode($mentions),
                'thumbnail_path' => $thumbnailPath,
                'image_path' => $imagePath,
                'is_featured' => $request->boolean('is_featured'),
                'duration' => $duration,
            ]);
    
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_upload_success',
                'description' => 'Uploaded media: ' . $media->title,
            ]);
    
            return redirect()->route('content.videos')->with('success', 'Media uploaded successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            LaravelLog::error('Validation error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->with('error', 'Validation failed: ' . $e->getMessage());
        } catch (\Google\Service\Exception $e) {
            LaravelLog::error('Google Drive error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->with('error', 'Google Drive upload failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            LaravelLog::error('Media upload error: ' . $e->getMessage(), ['exception' => $e]);
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_upload_error',
                'description' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Media upload failed: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $media = Media::with('category')->findOrFail($id);
            $categories = Category::all();
            $users = User::all();
            $year = Category::where('id', $media->category_id)->first();
            $yearName = $year['name'];

            $month = SubCategory::where('id', $media->sub_category_id)->first();
            $monthName = $month['name'];
            return view('pages.content.edit_video', compact('media', 'categories', 'users', 'yearName', 'monthName'));
        } catch (Exception $e) {
            LaravelLog::error('Media edit error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load media for editing.');
        }
    }
    public function update(Request $request, $id)
    {

        try {
            $media = Media::findOrFail($id);

            // Validate input
            $validated = $request->validate([
                'year' => 'required|digits:4',
                'month' => 'required',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'nullable|file|mimes:mp4,avi,mov|max:51200', // 50MB limit
                'uploaded_video_path' => 'nullable|string',
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'thumbnail_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'is_featured' => 'nullable|boolean',
                'is_favorite' => 'nullable|boolean',
                'mention' => 'nullable|array',
                'mention.*' => 'nullable|string|max:255',
            ]);

            $category = Category::firstOrCreate(
                [
                    'name' => $validated['year'],
                    'user_id' => Auth::id()
                ],
                [
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

            // Handle chunked upload for update (like store)
            $video = $media->file_path;
            if ($request->filled('uploaded_video_path')) {
                $assembledPath = $request->input('uploaded_video_path');
                if ($assembledPath && file_exists($assembledPath)) {
                    $videoPath = $assembledPath;
                    $fileInfo = $getID3->analyze($videoPath);
                    $duration = isset($fileInfo['playtime_seconds']) ? floatval($fileInfo['playtime_seconds']) : null;
                    $driveService = new GoogleDriveServiceVideo();
                    $filename = time() . '_' . basename($videoPath);
                    LaravelLog::info('About to upload to Google Drive', ['videoPath' => $videoPath, 'filename' => $filename]);
                    $url = $driveService->uploadFile(new \Illuminate\Http\File($videoPath), $filename);
                    LaravelLog::info('Google Drive upload returned', ['url' => $url]);
                    if (!$url) {
                        throw new \Exception('Failed to retrieve Google Drive file ID.');
                    }
                    $video = 'https://drive.google.com/file/d/' . $url . '/preview';
                    unlink($videoPath); // Clean up
                    LaravelLog::info('Google Drive upload complete, file ID: ' . $url);
                }
            } else if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $videoPath = $request->file('file')->getRealPath();
                $fileInfo = $getID3->analyze($videoPath);
                $duration = isset($fileInfo['playtime_seconds']) ? floatval($fileInfo['playtime_seconds']) : null;
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
                'status' => 'pending',
                'thumbnail_path' => $thumbnailPath,
                'image_path' => $imagePath,
                'is_featured' => $request->boolean('is_featured'),
                'is_favorite' => $request->boolean('is_favorite'),
                'mention' => json_encode($mentions),
                'duration' => $duration,
            ]);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_update_success',
                'description' => 'Updated media: ' . $media->title,
            ]);

            return redirect()->route('content.videos')->with('success', 'Media updated successfully.');
        } catch (Exception $e) {
            LaravelLog::error('Media update error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_update_error',
                'description' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Media update failed: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $media = Media::findOrFail($id);

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
                'user_id' => Auth::id(),
                'type' => 'media_delete_success',
                'description' => 'Deleted media: ' . $media->title,
            ]);

            // Delete the media record from the database
            $media->delete();

            return redirect()->route('content.videos')->with('success', 'Media deleted successfully.');
        } catch (Exception $e) {
            LaravelLog::error('Media delete error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_delete_error',
                'description' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete media: ' . $e->getMessage());
        }
    }

    public function stream($id)
    {
        try {
            $media = Media::findOrFail($id);
            $filePath = $media->file_path;

            // Extract Google Drive file ID from the URL
            $fileId = null;
            if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $filePath, $matches)) {
                $fileId = $matches[1];
            }

            if (!$fileId) {
                abort(404, 'Google Drive file ID not found.');
            }

            $driveService = new \Google\Service\Drive($this->client);
            $file = $driveService->files->get($fileId, ['alt' => 'media']);

            $response = new StreamedResponse(function () use ($file) {
                $chunkSize = 1024 * 1024; // 1MB chunks
                $stream = $file->getBody();
                while (!$stream->eof()) {
                    echo $stream->read($chunkSize);
                    flush();
                }
            });

            $response->headers->set('Content-Type', 'video/mp4');
            $response->headers->set('Content-Length', $file->getHeaderLine('Content-Length'));
            $response->headers->set('Accept-Ranges', 'bytes');

            return $response;
        } catch (Exception $e) {
            LaravelLog::error('Media streaming error: ' . $e->getMessage());
            abort(500, 'Error streaming video.');
        }
    }

    /**
     * Handle Resumable.js chunked video upload
     */
    public function uploadChunk(Request $request)
    {
        try {
            $resumableIdentifier = $request->input('resumableIdentifier');
            $resumableFilename = $request->input('resumableFilename');
            $resumableChunkNumber = (int)$request->input('resumableChunkNumber');
            $resumableTotalChunks = (int)$request->input('resumableTotalChunks');
    
            if (!$resumableIdentifier || !$resumableFilename || !$resumableChunkNumber || !$resumableTotalChunks) {
                LaravelLog::error('Missing Resumable.js parameters', ['request' => $request->all()]);
                return response()->json(['error' => 'Missing upload parameters'], 400);
            }
    
            $tempDir = storage_path('app/resumable-temp/' . $resumableIdentifier);
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    LaravelLog::error('Failed to create temp directory: ' . $tempDir, ['permissions' => fileperms($tempDir) ?? 'not exists']);
                    return response()->json(['error' => 'Server error: Cannot create temp directory'], 500);
                }
            } elseif (!is_writable($tempDir)) {
                LaravelLog::error('Temp directory not writable: ' . $tempDir, ['permissions' => fileperms($tempDir)]);
                return response()->json(['error' => 'Server error: Temp directory not writable'], 500);
            }
    
            if ($request->hasFile('file')) {
                LaravelLog::info('Received chunk ' . $resumableChunkNumber . ' for ' . $resumableIdentifier, ['file_size' => $request->file('file')->getSize()]);
                $chunk = $request->file('file');
                $chunkPath = $tempDir . "/chunk_{$resumableChunkNumber}";
                $chunk->move($tempDir, "chunk_{$resumableChunkNumber}");
                if (!file_exists($chunkPath)) {
                    LaravelLog::error('Failed to move chunk to: ' . $chunkPath, ['error' => error_get_last()]);
                    return response()->json(['error' => 'Failed to save chunk'], 500);
                }
            } else {
                LaravelLog::error('No chunk file provided', ['request' => $request->all()]);
                return response()->json(['error' => 'No chunk file provided'], 400);
            }
    
            $allChunksPresent = true;
            for ($i = 1; $i <= $resumableTotalChunks; $i++) {
                if (!file_exists($tempDir . "/chunk_{$i}")) {
                    $allChunksPresent = false;
                    break;
                }
            }
    
            if ($allChunksPresent) {
                $uniqueName = uniqid() . '_' . $resumableFilename;
                $uploadsDir = storage_path('app/uploads');
                if (!file_exists($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }
                $finalPath = $uploadsDir . '/' . $uniqueName;
                $out = @fopen($finalPath, 'ab');
                if (!$out) {
                    LaravelLog::error('Failed to open output file: ' . $finalPath, ['error' => error_get_last(), 'permissions' => fileperms(dirname($finalPath))]);
                    return response()->json(['error' => 'Failed to assemble file'], 500);
                }
                for ($i = 1; $i <= $resumableTotalChunks; $i++) {
                    $chunkPath = $tempDir . "/chunk_{$i}";
                    $in = @fopen($chunkPath, 'rb');
                    if (!$in) {
                        fclose($out);
                        LaravelLog::error('Failed to open chunk: ' . $chunkPath, ['error' => error_get_last()]);
                        return response()->json(['error' => 'Failed to read chunk'], 500);
                    }
                    if (stream_copy_to_stream($in, $out) === false) {
                        fclose($in);
                        fclose($out);
                        LaravelLog::error('Failed to copy chunk to output: ' . $chunkPath, ['error' => error_get_last()]);
                        return response()->json(['error' => 'Failed to assemble file'], 500);
                    }
                    fclose($in);
                    unlink($chunkPath);
                }
                fclose($out);
                if (!rmdir($tempDir)) {
                    LaravelLog::warning('Failed to remove temp directory: ' . $tempDir, ['contents' => scandir($tempDir)]);
                }
                LaravelLog::info('Assembled file at: ' . $finalPath, ['file_size' => filesize($finalPath)]);
                // Extra debug: check if file exists before returning
                if (!file_exists($finalPath)) {
                    LaravelLog::error('Final assembled file does not exist before response', ['finalPath' => $finalPath]);
                    return response()->json(['error' => 'Assembled file missing'], 500);
                }
                return response()->json(['path' => $finalPath]);
            }
    
            return response()->json(['chunk' => $resumableChunkNumber, 'success' => true]);
        } catch (\Exception $e) {
            LaravelLog::error('Chunk upload error at chunk ' . $resumableChunkNumber, [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Chunk upload failed: ' . $e->getMessage()], 500);
        }
        // Fallback: always return JSON if something unexpected happens
        LaravelLog::error('uploadChunk reached unexpected end without response', ['request' => $request->all()]);
        return response()->json(['error' => 'Unexpected server error in uploadChunk'], 500);
    }

    /**
     * Handle Resumable.js GET request to check if a chunk exists
     */
    public function testChunk(Request $request)
    {
        $resumableIdentifier = $request->input('resumableIdentifier');
        $resumableChunkNumber = $request->input('resumableChunkNumber');
        $tempDir = storage_path('app/resumable-temp/' . $resumableIdentifier);
        $chunkPath = $tempDir . "/chunk_{$resumableChunkNumber}";
        if (file_exists($chunkPath)) {
            return response('', 200); // Chunk exists
        } else {
            return response('', 204); // Chunk does not exist
        }
    }

    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,inreview,published,declined',
        ]);
        $media = Media::findOrFail($id);
        $currentStatus = $media->status;
        $newStatus = $request->input('status');
        $assignedReviewerIds = json_decode($media->assigned_to, true) ?? [];
        if (in_array($currentStatus, ['pending', 'declined']) && $newStatus === 'inreview' && count($assignedReviewerIds) < 1) {
            return back()->with('error', 'You must assign at least one reviewer before moving to In Review.');
        }
        $media->status = $newStatus;
        $media->save();
        return redirect()->route('content.video', ['id' => $media->id, 'status' => $media->status])
            ->with('success', 'Status updated successfully.');
    }
}
