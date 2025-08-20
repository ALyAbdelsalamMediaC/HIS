<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\SubmissionForm;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class SubmissionFormController extends Controller
{

    /**
     * Display a listing of the submission forms.
     */
    public function index()
    {
        try {
            $submissions = SubmissionForm::all();
            return view('submission_forms.index', compact('submissions'));
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'Failed to retrieve submissions: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new submission form.
     */
    public function create()
    {
        return view('submission_forms.create');
    }

    /**
     * Store a newly created submission form in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'article_title' => 'nullable|string|max:255',
            'thematic_category' => 'nullable|string|max:255',
            'keywords' => 'nullable|string|max:255',
            'abstract_word_count' => 'nullable|string|max:255',
            'video_duration' => 'nullable|string|max:255',
            'supplementary_material' => 'nullable|string|max:255',
            'graphical_abstract' => 'nullable|string|max:255',
            'authors' => 'nullable|string',
            'authors_affiliations' => 'nullable|string',
            'corresponding_author' => 'nullable|string|max:255',
            'peer_reviewers' => 'nullable|string',
            'editor_handling_the_submission' => 'nullable|string|max:255',
            'digital_object_identifier' => 'nullable|string|max:255',
            'submission_date' => 'nullable|date',
            'acceptance_date' => 'nullable|date',
            'revisions_submitted' => 'nullable|string|max:255',
            'version_number' => 'nullable|string|max:255',
            'copyright_holder' => 'nullable|string|max:255',
            'licence_type' => 'nullable|string|max:255',
            'institutional_ethics_approval' => 'nullable|string|max:255',
            'patient_consent' => 'nullable|string|max:255',
            'funding_sources' => 'nullable|string',
            'confilicts_of_interest' => 'nullable|string',
            'corrections_corrigenda' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            SubmissionForm::create($request->all());
            return redirect()->route('submission_forms.index')->with('success', 'Submission created successfully.');
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'Failed to create submission: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified submission form.
     */
    public function show($id)
    {
        try {
            $submission = SubmissionForm::findOrFail($id);
            return view('submission_forms.show', compact('submission'));
        } catch (QueryException $e) {
            return redirect()->route('submission_forms.index')->with('error', 'Failed to retrieve submission: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified submission form.
     */
    public function edit($id)
    {
        try {
            $submission = SubmissionForm::findOrFail($id);
            return view('submission_forms.edit', compact('submission'));
        } catch (QueryException $e) {
            return redirect()->route('submission_forms.index')->with('error', 'Failed to retrieve submission: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified submission form in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'article_title' => 'nullable|string|max:255',
            'thematic_category' => 'nullable|string|max:255',
            'keywords' => 'nullable|string|max:255',
            'abstract_word_count' => 'nullable|string|max:255',
            'video_duration' => 'nullable|string|max:255',
            'supplementary_material' => 'nullable|string|max:255',
            'graphical_abstract' => 'nullable|string|max:255',
            'authors' => 'nullable|string',
            'authors_affiliations' => 'nullable|string',
            'corresponding_author' => 'nullable|string|max:255',
            'peer_reviewers' => 'nullable|string',
            'editor_handling_the_submission' => 'nullable|string|max:255',
            'digital_object_identifier' => 'nullable|string|max:255',
            'submission_date' => 'nullable|date',
            'acceptance_date' => 'nullable|date',
            'revisions_submitted' => 'nullable|string|max:255',
            'version_number' => 'nullable|string|max:255',
            'copyright_holder' => 'nullable|string|max:255',
            'licence_type' => 'nullable|string|max:255',
            'institutional_ethics_approval' => 'nullable|string|max:255',
            'patient_consent' => 'nullable|string|max:255',
            'funding_sources' => 'nullable|string',
            'confilicts_of_interest' => 'nullable|string',
            'corrections_corrigenda' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $submission = SubmissionForm::findOrFail($id);
            $submission->update($request->all());
            return redirect()->route('submission_forms.index')->with('success', 'Submission updated successfully.');
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'Failed to update submission: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified submission form from storage.
     */
    public function destroy($id)
    {
        try {
            $submission = SubmissionForm::findOrFail($id);
            $submission->delete();
            return redirect()->route('submission_forms.index')->with('success', 'Submission deleted successfully.');
        } catch (QueryException $e) {
            return redirect()->route('submission_forms.index')->with('error', 'Failed to delete submission: ' . $e->getMessage());
        }
    }
}
