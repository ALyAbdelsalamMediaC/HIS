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
        $selectedGroupId = session('selected_group_id');
        
        // If a group is selected, fetch existing questions for that group
        $existingQuestions = collect();
        if ($selectedGroupId) {
            $existingQuestions = Question::where('question_group_id', $selectedGroupId)
                ->with('answers')
                ->get();
        }
        
        // Logic to display the form for adding a new question
        return view('pages.reviewersQuestions.add', compact('questionGroup', 'existingQuestions', 'selectedGroupId'));
    }

    public function add(Request $request)
    {
        $user_id = auth()->user()->id;
        // Validate the incoming request
        $validatedData = $request->validate([
            'question_group_id' => 'required|exists:question_groups,id',
            'question' => 'required|string|max:255',
            'question_type' => 'required|string|max:255',
            'answers' => 'nullable|array', // Answers array for multiple/single choice questions
        ]);
        dd($validatedData);
        // dd($validatedData);
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

    public function edit(Request $request, $id)
    {
       
        return redirect()->route('reviewersQuestions.view_add')->with('success', 'Question updated successfully.');
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
