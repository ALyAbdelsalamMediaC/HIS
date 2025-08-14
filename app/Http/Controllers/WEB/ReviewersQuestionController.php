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
        // Logic to display the form for adding a new question
        return view('pages.reviewersQuestions.add', compact('questionGroup'));
    }

    public function add(Request $request)
    {
        $user_id = auth()->user()->id;
        // Validate the incoming request
        $validatedData = $request->validate([
            'question_group_id' => 'required|exists:question_groups,id',
            'question' => 'required|string|max:255',
            'question_type' => 'required|string|max:255',
            'answers' => 'required|string', // Expecting comma-separated answers
        ]);

        // Create a new question
        $question = Question::create([
            'user_id' => $user_id,
            'question_group_id' => $validatedData['question_group_id'],
            'question' => $validatedData['question'],
            'question_type' => $validatedData['question_type'],
        ]);

        // Split the answers string into an array and save each answer
        $answers = array_map('trim', explode(',', $validatedData['answers']));
        foreach ($answers as $answerText) {
            if (!empty($answerText)) {
                Answer::create([
                    'user_id' => $user_id,
                    'question_id' => $question->id,
                    'content' => $answerText,
                ]);
            }
        }

        // Retrieve existing questions and their answers for the given question_group_id
        $questionGroup = QuestionGroup::findOrFail($validatedData['question_group_id']);
        $existingQuestions = Question::where('question_group_id', $validatedData['question_group_id'])
            ->with('answers') // Assuming a 'answers' relationship is defined in the Question model
            ->get();

        // Return to the view with success message and data
        return redirect()->route('pages.reviewersQuestions.add')
            ->with('success', 'Question added successfully.')
            ->with('questionGroup', $questionGroup)
            ->with('existingQuestions', $existingQuestions);
    }

    public function edit(Request $request, $id)
    {
       
        return redirect()->route('pages.reviewersQuestions.edit')->with('success', 'Question updated successfully.');
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

            return redirect()->route('question_groups.index')
                ->with('success', 'Question Group added successfully.');
        

        // Display the add form
        return view('pages.question_groups.add');
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

            return redirect()->route('question_groups.index') // Adjust to your index route
                ->with('success', 'Question Group updated successfully.');
        

        return view('pages.question_groups.edit', compact('questionGroup'));
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

        return redirect()->route('question_groups.index') // Adjust to your index route
            ->with('success', 'Question Group deleted successfully.');
    }

    
}
