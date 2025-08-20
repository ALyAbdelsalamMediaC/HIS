<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionForm extends Model
{
    protected $table = 'submission_form';

    protected $fillable = [
        'user_id',
        'media_id',
        'article_title',
        'thematic_category',
        'keywords',
        'abstract_word_count',
        'video_duration',
        'supplementary_material',
        'graphical_abstract',
        'authors',
        'authors_affiliations',
        'corresponding_author',
        'peer_reviewers',
        'editor_handling_the_submission',
        'digital_object_identifier',
        'submission_date',
        'acceptance_date',
        'revisions_submitted',
        'version_number',
        'copyright_holder',
        'licence_type',
        'institutional_ethics_approval',
        'patient_consent',
        'funding_sources',
        'confilicts_of_interest',
        'corrections_corrigenda',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'acceptance_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
