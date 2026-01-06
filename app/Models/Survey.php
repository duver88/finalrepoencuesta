<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Survey extends Model
{
    protected $fillable = [
        'title',
        'description',
        'banner',
        'og_image',
        'slug',
        'public_slug',
        'is_active',
        'is_finished',
        'show_results',
        'results_display_mode',
        'one_vote_per_minute_per_option',
        'published_at',
        'finished_at',
        'views_count',
        'survey_group_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_finished' => 'boolean',
        'show_results' => 'boolean',
        'one_vote_per_minute_per_option' => 'boolean',
        'published_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    // Generar slug automáticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($survey) {
            if (empty($survey->slug)) {
                $survey->slug = Str::slug($survey->title) . '-' . Str::random(6);
            }

            // Generar public_slug ofuscado (12 caracteres aleatorios)
            if (empty($survey->public_slug)) {
                do {
                    $survey->public_slug = Str::random(12);
                } while (self::where('public_slug', $survey->public_slug)->exists());
            }
        });
    }

    // Relaciones
    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function tokens()
    {
        return $this->hasMany(SurveyToken::class);
    }

    public function group()
    {
        return $this->belongsTo(SurveyGroup::class, 'survey_group_id');
    }

    // Métodos útiles - SOLO CONTAR VOTOS VÁLIDOS (con tokens o manuales)
    public function getTotalVotesAttribute()
    {
        return $this->votes()->valid()->distinct('fingerprint')->count();
    }

    // Incrementar contador de visitas
    public function incrementViews()
    {
        $this->increment('views_count');
    }
}
