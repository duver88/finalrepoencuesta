<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'order',
        'color',
        'image',
    ];

    // Relaciones
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    // Métodos útiles - Solo contar votos válidos
    public function getVoteCountAttribute()
    {
        return $this->votes()->valid()->count();
    }

    public function getVotePercentageAttribute()
    {
        $totalVotes = $this->question->votes()->valid()->count();
        if ($totalVotes == 0) return 0;
        return round(($this->vote_count / $totalVotes) * 100, 2);
    }
}
