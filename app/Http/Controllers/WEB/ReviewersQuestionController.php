<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionGroup;
use Illuminate\Http\Request;

class ReviewersQuestionController extends Controller
{

    public function index()
    {
        // Logic to display the form for adding a new question
        return view('pages.reviewersQuestions.index');
    }
    public function view_add()
    {
        $questionGroup = QuestionGroup::all();
        $selectedGroupId = request('question_group_id', session('selected_group_id'));

        // If a group is selected, fetch existing questions for that group
        $existingQuestions = collect();
        if ($selectedGroupId) {
            $existingQuestions = Question::where('question_group_id', $selectedGroupId)
                ->with('answers')
                ->orderBy('order', 'asc')
                ->get();
        }

        // Logic to display the form for adding a new question
        return view('pages.reviewersQuestions.add', compact('questionGroup', 'existingQuestions', 'selectedGroupId'));
    }

    public function switchOrder(Request $request)
{
    $user_id = auth()->user()->id;

    // Validate the incoming request
    $validatedData = $request->validate([
        'question_id_1' => 'required|exists:questions,id',
        'question_id_2' => 'required|exists:questions,id',
    ]);

    try {
        // Find both questions and ensure they belong to the authenticated user and same group
        $question1 = Question::where('id', $validatedData['question_id_1'])
            ->where('user_id', $user_id)
            ->firstOrFail();
        $question2 = Question::where('id', $validatedData['question_id_2'])
            ->where('user_id', $user_id)
            ->where('question_group_id', $question1->question_group_id)
            ->firstOrFail();

        // Swap the order values
        $tempOrder = $question1->order;
        $question1->order = $question2->order;
        $question2->order = $tempOrder;

        // Save both questions
        $question1->save();
        $question2->save();

        // Return JSON response for the popup
        return response()->json([
            'success' => true,
            'message' => 'Question order swapped successfully.',
        ]);
    } catch (\Exception $e) {
        // Return error response
        return response()->json([
            'success' => false,
            'message' => 'Failed to swap question order: ' . $e->getMessage(),
        ], 500);
    }
}

public function reorder(Request $request)
{
    $user_id = auth()->user()->id;

    // Validate the incoming request
    $validatedData = $request->validate([
        'question_group_id' => 'required|exists:question_groups,id',
        'question_ids' => 'required|array',
        'question_ids.*' => 'exists:questions,id',
    ]);

    try {
        // Fetch all questions for the group to ensure they belong to the user
        $questions = Question::where('question_group_id', $validatedData['question_group_id'])
            ->where('user_id', $user_id)
            ->get();

        // Verify all provided question IDs belong to the group
        $existingQuestionIds = $questions->pluck('id')->toArray();
        if (count(array_diff($validatedData['question_ids'], $existingQuestionIds)) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid question IDs provided.',
            ], 400);
        }

        // Update order based on the provided question_ids array
        DB::transaction(function () use ($validatedData, $user_id) {
            foreach ($validatedData['question_ids'] as $index => $questionId) {
                Question::where('id', $questionId)
                    ->where('user_id', $user_id)
                    ->update(['order' => $index + 1]);
            }
        });

        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => 'Questions reordered successfully.',
        ]);
    } catch (\Exception $e) {
        // Return error response
        return response()->json([
            'success' => false,
            'message' => 'Failed to reorder questions: ' . $e->getMessage(),
        ], 500);
    }

    public function add(Request $request)
    {
        $user_id = auth()->user()->id;
        // Validate the incoming request
        $validatedData = $request->validate([
            'question_group_id' => 'required|exists:question_groups,id',
            'question' => 'required|string|max:255',
            'question_type' => 'required|in:text,multiple_choice,single_choice',
            'answers' => 'nullable|array', // Answers array for multiple/single choice questions
        ]);

        // Create a new question
        $question = Question::create([
            'user_id' => $user_id,
            'question_group_id' => $validatedData['question_group_id'],
            'content' => $validatedData['question'],
            'type' => $validatedData['question_type'],
        ]);

        // Handle answers array and save each answer
        if (isset($validatedData['answers']) && is_array($validatedData['answers'])) {
            foreach ($validatedData['answers'] as $answerText) {
                if (!empty(trim($answerText))) {
                    Answer::create([
                        'user_id' => $user_id,
                        'question_id' => $question->id,
                        'content' => trim($answerText),
                    ]);
                }
            }
        }

        // Return to the view with success message and data
        return redirect()->route('reviewersQuestions.view_add')
            ->with('success', 'Question added successfully.')
            ->with('selected_group_id', $validatedData['question_group_id']);
    }

    public function view_edit(Request $request)
    {
        $validatedData = $request->validate([
            'question_id' => 'required|exists:questions,id',

        ]);
        $user_id = auth()->user()->id;

        $question_answers = Question::where('id', $validatedData['question_id'])->where('user_id', $user_id)
            ->with('answers')
            ->get();

        return view('pages.reviewersQuestions.add', compact('question_answers'));
    }

    public function edit(Request $request, $id)
    {
        $user_id = auth()->user()->id;

        // Validate the incoming request
        $validatedData = $request->validate([
            'question_group_id' => 'required|exists:question_groups,id',
            'question' => 'required|string|max:255',
            'question_type' => 'required|in:text,multiple_choice,single_choice',
            'answers' => 'nullable|array',
        ]);

        // Find the question or fail
        $question = Question::where('id', $id)->where('user_id', $user_id)->firstOrFail();

        // Update the question
        $question->update([
            'question_group_id' => $validatedData['question_group_id'],
            'content' => $validatedData['question'],
            'type' => $validatedData['question_type'],
        ]);

        // Delete existing answers if question type is multiple_choice or single_choice
        if (in_array($validatedData['question_type'], ['multiple_choice', 'single_choice'])) {
            Answer::where('question_id', $question->id)->delete();

            // Create new answers
            if (isset($validatedData['answers']) && is_array($validatedData['answers'])) {
                foreach ($validatedData['answers'] as $answerText) {
                    if (!empty(trim($answerText))) {
                        Answer::create([
                            'user_id' => $user_id,
                            'question_id' => $question->id,
                            'content' => trim($answerText),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('reviewersQuestions.view_add')
            ->with('success', 'Question updated successfully.')
            ->with('selected_group_id', $validatedData['question_group_id']);
    }

    public function delete($id)
    {
        $user_id = auth()->user()->id;

        // Find the question or fail
        $question = Question::where('id', $id)->where('user_id', $user_id)->firstOrFail();

        // Delete associated answers
        Answer::where('question_id', $question->id)->delete();

        // Delete the question
        $question->delete();

        return redirect()->route('reviewersQuestions.view_add')
            ->with('success', 'Question deleted successfully.');
    }

    public function addGroup(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Create a new QuestionGroup
        QuestionGroup::create([
            'user_id' => auth()->user()->id,
            'name' => $validatedData['name'],
        ]);

        return redirect()->route('reviewersQuestions.view_add')
            ->with('success', 'Question Group added successfully.');
    }

    public function editGroup(Request $request, $id)
    {
        $questionGroup = QuestionGroup::findOrFail($id);

        if ($questionGroup->user_id !== auth()->user()->id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $questionGroup->update([
            'name' => $validatedData['name'],
        ]);

        return redirect()->route('reviewersQuestions.view_add')
            ->with('success', 'Question Group updated successfully.');
    }

    public function deleteGroup($id)
    {
        $questionGroup = QuestionGroup::findOrFail($id);

        if ($questionGroup->user_id !== auth()->user()->id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        if ($questionGroup->questions()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete Question Group with associated questions.');
        }

        $questionGroup->delete();

        return redirect()->route('reviewersQuestions.view_add') // Adjust to your index route
            ->with('success', 'Question Group deleted successfully.');
    }
}
