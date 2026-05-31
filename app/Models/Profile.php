<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'academic_session_id',
        'prenom',
        'nom',
        'post_nom',
        'genre',
        'etat_civil',
        'nationalite',
        'nationalite_autre',
        'lieu_naissance',
        'date_naissance',
        'phone',
        'profession',
        'commune_habitation',
        'quartier_habitation',
        'adresse_numero_avenue',
        'contact_email',
        'vacation_choice',
        'session_vacation_id',
        'vacation_autre',
        'est_ne_de_nouveau',
        'annee_nouvelle_naissance',
        'eglise_acceptation_jesus',
        'est_baptise_eau',
        'eglise_bapteme',
        'est_passe_metamorphoo',
        'mentor_metamorphoo_nom',
        'souhaite_faire_metamorphoo',
        'eglise_attache',
        'eglise_attache_autre',
        'souhaite_oeuvrer_phila_apres_apollos',
        'google_form_response_id',
        'inscription_submitted_at',
        'inscription_source_payload',
        'avatar_path',
        'bio',
        'locale',
        'accepted_legal_document_id',
        'legal_document_accepted_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'inscription_source_payload' => 'array',
            'legal_document_accepted_at' => 'datetime',
            'date_naissance' => 'date',
            'est_ne_de_nouveau' => 'boolean',
            'est_baptise_eau' => 'boolean',
            'est_passe_metamorphoo' => 'boolean',
            'souhaite_faire_metamorphoo' => 'boolean',
            'souhaite_oeuvrer_phila_apres_apollos' => 'boolean',
            'inscription_submitted_at' => 'datetime',
        ];
    }

    /**
     * Document légal accepté à l'inscription.
     */
    public function acceptedLegalDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'accepted_legal_document_id');
    }

    /**
     * Titre d’enregistrement Filament : nom d’inscription ou compte lié.
     */
    public function getDisplayLabelAttribute(): string
    {
        $composed = trim(implode(' ', array_filter([
            $this->prenom,
            $this->post_nom,
            $this->nom,
        ])));

        if ($composed !== '') {
            return $composed;
        }

        return $this->user?->name ?? ('#'.$this->id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    /**
     * Vacation présentiel choisie à l'inscription ECAP.
     */
    public function sessionVacation(): BelongsTo
    {
        return $this->belongsTo(SessionVacation::class);
    }
}
