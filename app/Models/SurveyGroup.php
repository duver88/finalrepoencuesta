<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SurveyGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'restrict_voting',
    ];

    protected $casts = [
        'restrict_voting' => 'boolean',
    ];

    /**
     * Boot del modelo para generar slug automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (empty($group->slug)) {
                $group->slug = Str::slug($group->name);

                // Verificar si ya existe y agregar número si es necesario
                $originalSlug = $group->slug;
                $count = 1;
                while (static::where('slug', $group->slug)->exists()) {
                    $group->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($group) {
            // Siempre actualizar el slug cuando cambie el nombre
            if ($group->isDirty('name')) {
                $group->slug = Str::slug($group->name);

                // Verificar si ya existe y agregar número si es necesario
                $originalSlug = $group->slug;
                $count = 1;
                while (static::where('slug', $group->slug)->where('id', '!=', $group->id)->exists()) {
                    $group->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Obtener todas las encuestas de este grupo
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    /**
     * Verificar si un usuario (por fingerprint) ya votó en alguna encuesta del grupo
     */
    public function hasVotedInGroup(string $fingerprint): bool
    {
        if (!$this->restrict_voting) {
            return false;
        }

        // Optimizado: usar JOIN directo para mejor rendimiento
        return Vote::join('questions', 'votes.question_id', '=', 'questions.id')
            ->join('surveys', 'questions.survey_id', '=', 'surveys.id')
            ->where('surveys.survey_group_id', $this->id)
            ->where('votes.fingerprint', $fingerprint)
            ->where('votes.is_valid', true)
            ->exists();
    }

    /**
     * Obtener la encuesta en la que el usuario ya votó (si existe)
     */
    public function getVotedSurvey(string $fingerprint): ?Survey
    {
        if (!$this->restrict_voting) {
            return null;
        }

        // Optimizado: usar JOIN directo en lugar de whereHas para mejor rendimiento
        $vote = Vote::join('questions', 'votes.question_id', '=', 'questions.id')
            ->join('surveys', 'questions.survey_id', '=', 'surveys.id')
            ->where('surveys.survey_group_id', $this->id)
            ->where('votes.fingerprint', $fingerprint)
            ->where('votes.is_valid', true)
            ->select('votes.*', 'surveys.id as survey_id', 'surveys.title as survey_title')
            ->first();

        if (!$vote) {
            return null;
        }

        // Crear objeto Survey con los datos obtenidos
        $survey = new Survey();
        $survey->id = $vote->survey_id;
        $survey->title = $vote->survey_title;
        $survey->exists = true;

        return $survey;
    }

    /**
     * Obtener el token que fue usado por un fingerprint en el grupo
     */
    public function getUsedTokenByFingerprint(string $fingerprint): ?SurveyToken
    {
        if (!$this->restrict_voting) {
            return null;
        }

        // Buscar el token usado por este fingerprint en cualquier encuesta del grupo
        return SurveyToken::join('surveys', 'survey_tokens.survey_id', '=', 'surveys.id')
            ->join('votes', 'survey_tokens.id', '=', 'votes.survey_token_id')
            ->where('surveys.survey_group_id', $this->id)
            ->where('votes.fingerprint', $fingerprint)
            ->where('votes.is_valid', true)
            ->where('survey_tokens.status', 'used')
            ->select('survey_tokens.*')
            ->first();
    }
}
