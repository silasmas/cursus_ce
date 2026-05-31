<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ecap_inscriptions');

        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('academic_session_id')->nullable()->after('user_id')->constrained()->nullOnDelete();

            $table->string('prenom')->nullable()->after('academic_session_id');
            $table->string('nom')->nullable()->after('prenom');
            $table->string('post_nom')->nullable()->after('nom');
            $table->string('genre')->nullable()->index()->after('post_nom');
            $table->string('etat_civil')->nullable()->index()->after('genre');
            $table->string('nationalite')->nullable()->after('etat_civil');
            $table->string('nationalite_autre')->nullable()->after('nationalite');
            $table->string('lieu_naissance')->nullable()->after('nationalite_autre');
            $table->date('date_naissance')->nullable()->after('lieu_naissance');

            $table->string('profession')->nullable()->after('phone');
            $table->string('commune_habitation')->nullable()->after('profession');
            $table->string('quartier_habitation')->nullable()->after('commune_habitation');
            $table->string('adresse_numero_avenue')->nullable()->after('quartier_habitation');
            $table->string('contact_email')->nullable()->after('adresse_numero_avenue');

            $table->string('vacation_choice')->nullable()->index()->after('contact_email');
            $table->string('vacation_autre')->nullable()->after('vacation_choice');

            $table->boolean('est_ne_de_nouveau')->nullable()->after('vacation_autre');
            $table->string('annee_nouvelle_naissance')->nullable()->after('est_ne_de_nouveau');
            $table->text('eglise_acceptation_jesus')->nullable()->after('annee_nouvelle_naissance');

            $table->boolean('est_baptise_eau')->nullable()->after('eglise_acceptation_jesus');
            $table->string('eglise_bapteme')->nullable()->after('est_baptise_eau');
            $table->boolean('est_passe_metamorphoo')->nullable()->after('eglise_bapteme');
            $table->string('mentor_metamorphoo_nom')->nullable()->after('est_passe_metamorphoo');
            $table->boolean('souhaite_faire_metamorphoo')->nullable()->after('mentor_metamorphoo_nom');

            $table->string('eglise_attache')->nullable()->after('souhaite_faire_metamorphoo');
            $table->string('eglise_attache_autre')->nullable()->after('eglise_attache');
            $table->boolean('souhaite_oeuvrer_phila_apres_apollos')->nullable()->after('eglise_attache_autre');

            $table->string('google_form_response_id')->nullable()->unique()->after('souhaite_oeuvrer_phila_apres_apollos');
            $table->timestamp('inscription_submitted_at')->nullable()->after('google_form_response_id');
            $table->json('inscription_source_payload')->nullable()->after('inscription_submitted_at');

            $table->index(['academic_session_id', 'genre'], 'profiles_academic_session_genre_index');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex('profiles_academic_session_genre_index');
            $table->dropUnique(['google_form_response_id']);
            $table->dropForeign(['academic_session_id']);
            $table->dropColumn([
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
                'profession',
                'commune_habitation',
                'quartier_habitation',
                'adresse_numero_avenue',
                'contact_email',
                'vacation_choice',
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
            ]);
        });

        // Ne recrée pas ecap_inscriptions automatiquement (déploiement manuel si besoin).
    }
};
