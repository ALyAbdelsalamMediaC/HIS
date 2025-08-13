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
        // Logic to handle editing an existing question
        // Not implemented here

        return redirect()->route('pages.reviewersQuestions.edit')->with('success', 'Question updated successfully.');
    }


    // Logic to handle deleting a question
    // Not implemented here 
}
