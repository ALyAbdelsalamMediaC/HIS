<?php

namespace App\Http\Controllers\WEB;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ReviewersQuestionController extends Controller
{
 
    public function index()
    {
        // Logic to display the form for adding a new question
        return view('pages.reviewersQuestions.add');
    }

    public function add(Request $request)
    {
        // Logic to handle the addition of a new question
        $validatedData = $request->validate([
            'question' => 'required|string|max:255',
            'details' => 'required|string',
        ]);

        // Save the question to the database (not implemented here)
        
        return redirect()->route('pages.reviewersQuestions.index')->with('success', 'Question added successfully.');
    }

    public function edit(Request $request, $id)
    {
        // Logic to handle editing an existing question
        // Not implemented here

        return redirect()->route('pages.reviewersQuestions.index')->with('success', 'Question updated successfully.');
    }

    
        // Logic to handle deleting a question
        // Not implemented here 
}
