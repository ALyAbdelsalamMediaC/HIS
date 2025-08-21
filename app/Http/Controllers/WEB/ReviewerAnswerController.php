<?php

namespace App\Http\Controllers\WEB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\ReviewerAnswer;
use Illuminate\Http\Request;

class ReviewerAnswerController extends Controller
{
    public function store(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:questions,id',
            'content' => 'nullable|string|max:255',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the authenticated user's ID
        $user_id = auth()->user()->id;

        // Create a new ReviewerAnswer record
        ReviewerAnswer::create([
            'question_id' => $request->question_id,
            'user_id' => $user_id,
            'media_id' => $id,
            'content' => $request->content,
        ]);

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Answer submitted successfully.');
    }

    public function edit($id)
    {
        // Find the ReviewerAnswer by ID
        $answer = ReviewerAnswer::findOrFail($id);

        // Check if the authenticated user owns the answer
        if ($answer->user_id !== auth()->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Load questions for the form (assuming you have a Question model)
        $questions = \App\Models\Question::all();

        // Return the edit view
        return view('reviewer_answers.edit', compact('answer', 'questions'));
    }

    public function update(Request $request, $id)
    {
        // Find the ReviewerAnswer by ID
        $answer = ReviewerAnswer::findOrFail($id);

        // Check if the authenticated user owns the answer
        if ($answer->user_id !== auth()->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:questions,id',
            'content' => 'nullable|string|max:255',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update the ReviewerAnswer
        $answer->update([
            'question_id' => $request->question_id,
            'content' => $request->content,
        ]);

        // Redirect back with a success message
        return redirect()->route('media.show', $answer->media_id)
            ->with('success', 'Answer updated successfully.');
    }

    public function destroy($id)
    {
        // Find the ReviewerAnswer by ID
        $answer = ReviewerAnswer::findOrFail($id);

        // Check if the authenticated user owns the answer
        if ($answer->user_id !== auth()->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the ReviewerAnswer
        $answer->delete();

        // Redirect back with a success message
        return redirect()->route('media.show', $answer->media_id)
            ->with('success', 'Answer deleted successfully.');
    }
}
