<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'survey_id',
        'question_text',
        'question_type',
        'display_mode',
        'randomize_options',
        'order',
    ];

    protected $casts = [
        'randomize_options' => 'boolean',
    ];

    // Relaciones
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
