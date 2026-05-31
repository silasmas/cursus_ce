import { Head, Link, useForm } from '@inertiajs/react';
import PublicLayout from '../../Components/Layout/PublicLayout';
import LoadingButton from '../../Components/UI/LoadingButton';
import StepIndicator from '../../Components/UI/StepIndicator';

/**
 * Formulaire d'inscription multi-étapes pour les fidèles.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function Register({ step, totalSteps, data, sessions, sessionVacations, stepLabels, legalDocument = null }) {
  const form = useForm({
    ...data,
    accept_terms: data.accept_terms ?? false,
    accept_legal_document: data.accept_legal_document ?? false,
    ecap_is_online: data.ecap_is_online ?? true,
    session_vacation_id: data.session_vacation_id ?? '',
  });

  const selectedSession = sessions.find(
    (session) => String(session.id) === String(form.data.academic_session_id),
  );
  const isEcapSession = selectedSession?.program_slug === 'ecap';
  const isEcapPresentiel = isEcapSession && form.data.ecap_is_online === false;
  const vacationsForSession = (sessionVacations ?? []).filter(
    (vacation) => String(vacation.academic_session_id) === String(form.data.academic_session_id),
  );
  const selectedVacation = (sessionVacations ?? []).find(
    (vacation) => String(vacation.id) === String(form.data.session_vacation_id),
  );

  const submit = (event) => {
    event.preventDefault();
    form.post(`/inscription/etape/${step}`);
  };

  const goBack = () => {
    if (step > 1) {
      window.location.href = `/inscription?etape=${step - 1}`;
    }
  };

  const setBool = (field, value) => {
    form.setData(field, value === 'true' || value === true);
  };

  return (
    <PublicLayout showAuthLinks={false}>
      <Head title={`Inscription – Étape ${step}`} />

      <div className="container-phila py-12">
        <div className="mx-auto max-w-2xl">
          <div className="mb-8 text-center">
            <img src="/images/phila-logo.png" alt="PHILA" className="logo-phila-orange mx-auto mb-4 h-14 w-14 rounded-full" />
            <h1 className="font-display text-2xl font-bold">Inscription PHILA-CE</h1>
            <p className="mt-2 text-sm text-phila-gray-600">
              Complétez votre profil en {totalSteps} étapes pour rejoindre la plateforme.
            </p>
          </div>

          <div className="card">
            <StepIndicator current={step} total={totalSteps} labels={stepLabels} />

            <form onSubmit={submit} className="space-y-5">
              {step === 1 && (
                <>
                  <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Prénom *" error={form.errors.prenom}>
                      <input className="input-field" value={form.data.prenom || ''} onChange={(e) => form.setData('prenom', e.target.value)} required />
                    </Field>
                    <Field label="Nom *" error={form.errors.nom}>
                      <input className="input-field" value={form.data.nom || ''} onChange={(e) => form.setData('nom', e.target.value)} required />
                    </Field>
                  </div>
                  <Field label="Post-nom" error={form.errors.post_nom}>
                    <input className="input-field" value={form.data.post_nom || ''} onChange={(e) => form.setData('post_nom', e.target.value)} />
                  </Field>
                  <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Genre *" error={form.errors.genre}>
                      <select className="input-field" value={form.data.genre || ''} onChange={(e) => form.setData('genre', e.target.value)} required>
                        <option value="">Sélectionner</option>
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                      </select>
                    </Field>
                    <Field label="Date de naissance *" error={form.errors.date_naissance}>
                      <input type="date" className="input-field" value={form.data.date_naissance || ''} onChange={(e) => form.setData('date_naissance', e.target.value)} required />
                    </Field>
                  </div>
                  <Field label="Lieu de naissance" error={form.errors.lieu_naissance}>
                    <input className="input-field" value={form.data.lieu_naissance || ''} onChange={(e) => form.setData('lieu_naissance', e.target.value)} />
                  </Field>
                  <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Nationalité *" error={form.errors.nationalite}>
                      <input className="input-field" value={form.data.nationalite || ''} onChange={(e) => form.setData('nationalite', e.target.value)} required />
                    </Field>
                    <Field label="État civil" error={form.errors.etat_civil}>
                      <input className="input-field" value={form.data.etat_civil || ''} onChange={(e) => form.setData('etat_civil', e.target.value)} />
                    </Field>
                  </div>
                </>
              )}

              {step === 2 && (
                <>
                  <Field label="Adresse e-mail *" error={form.errors.email}>
                    <input type="email" className="input-field" value={form.data.email || ''} onChange={(e) => form.setData('email', e.target.value)} required />
                  </Field>
                  <Field label="Téléphone *" error={form.errors.phone}>
                    <input type="tel" className="input-field" value={form.data.phone || ''} onChange={(e) => form.setData('phone', e.target.value)} required />
                  </Field>
                  <Field label="Profession" error={form.errors.profession}>
                    <input className="input-field" value={form.data.profession || ''} onChange={(e) => form.setData('profession', e.target.value)} />
                  </Field>
                  <Field label="Commune *" error={form.errors.commune_habitation}>
                    <input className="input-field" value={form.data.commune_habitation || ''} onChange={(e) => form.setData('commune_habitation', e.target.value)} required />
                  </Field>
                  <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Quartier" error={form.errors.quartier_habitation}>
                      <input className="input-field" value={form.data.quartier_habitation || ''} onChange={(e) => form.setData('quartier_habitation', e.target.value)} />
                    </Field>
                    <Field label="Adresse" error={form.errors.adresse_numero_avenue}>
                      <input className="input-field" value={form.data.adresse_numero_avenue || ''} onChange={(e) => form.setData('adresse_numero_avenue', e.target.value)} />
                    </Field>
                  </div>
                </>
              )}

              {step === 3 && (
                <>
                  <BoolField label="Êtes-vous né(e) de nouveau ? *" value={form.data.est_ne_de_nouveau} onChange={(v) => setBool('est_ne_de_nouveau', v)} error={form.errors.est_ne_de_nouveau} />
                  <Field label="Année de la nouvelle naissance" error={form.errors.annee_nouvelle_naissance}>
                    <input type="number" className="input-field" value={form.data.annee_nouvelle_naissance || ''} onChange={(e) => form.setData('annee_nouvelle_naissance', e.target.value)} />
                  </Field>
                  <Field label="Église d'acceptation de Jésus" error={form.errors.eglise_acceptation_jesus}>
                    <input className="input-field" value={form.data.eglise_acceptation_jesus || ''} onChange={(e) => form.setData('eglise_acceptation_jesus', e.target.value)} />
                  </Field>
                  <BoolField label="Êtes-vous baptisé(e) par immersion ? *" value={form.data.est_baptise_eau} onChange={(v) => setBool('est_baptise_eau', v)} error={form.errors.est_baptise_eau} />
                  <Field label="Église de baptême" error={form.errors.eglise_bapteme}>
                    <input className="input-field" value={form.data.eglise_bapteme || ''} onChange={(e) => form.setData('eglise_bapteme', e.target.value)} />
                  </Field>
                  <BoolField label="Avez-vous suivi Metamorphoo ? *" value={form.data.est_passe_metamorphoo} onChange={(v) => setBool('est_passe_metamorphoo', v)} error={form.errors.est_passe_metamorphoo} />
                  <Field label="Nom du mentor Metamorphoo" error={form.errors.mentor_metamorphoo_nom}>
                    <input className="input-field" value={form.data.mentor_metamorphoo_nom || ''} onChange={(e) => form.setData('mentor_metamorphoo_nom', e.target.value)} />
                  </Field>
                  <BoolField label="Souhaitez-vous faire Metamorphoo ? *" value={form.data.souhaite_faire_metamorphoo} onChange={(v) => setBool('souhaite_faire_metamorphoo', v)} error={form.errors.souhaite_faire_metamorphoo} />
                </>
              )}

              {step === 4 && (
                <>
                  {sessions.length > 0 && (
                    <Field label="Session académique" error={form.errors.academic_session_id}>
                      <select className="input-field" value={form.data.academic_session_id || ''} onChange={(e) => form.setData('academic_session_id', e.target.value)}>
                        <option value="">Session active par défaut</option>
                        {sessions.map((s) => (
                          <option key={s.id} value={s.id}>{s.name} ({s.code})</option>
                        ))}
                      </select>
                    </Field>
                  )}
                  {isEcapSession && (
                    <BoolField
                      label="Mode de suivi ECAP *"
                      value={form.data.ecap_is_online}
                      onChange={(v) => {
                        setBool('ecap_is_online', v);
                        if (v === true || v === 'true') {
                          form.setData('session_vacation_id', '');
                        }
                      }}
                      error={form.errors.ecap_is_online}
                      trueLabel="En ligne"
                      falseLabel="Présentiel"
                      hint="Présentiel : choisissez une vacation ci-dessous. Le cursus en ligne reste visible en lecture seule."
                    />
                  )}
                  {isEcapPresentiel && (
                    <Field label="Vacation (présentiel) *" error={form.errors.session_vacation_id}>
                      {vacationsForSession.length === 0 ? (
                        <p className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                          Aucune vacation n&apos;est encore configurée pour cette session. Contactez l&apos;administration PHILA.
                        </p>
                      ) : (
                        <select
                          className="input-field"
                          value={form.data.session_vacation_id || ''}
                          onChange={(e) => form.setData('session_vacation_id', e.target.value)}
                          required
                        >
                          <option value="">Choisir une vacation</option>
                          {vacationsForSession.map((vacation) => (
                            <option key={vacation.id} value={vacation.id}>
                              {vacation.name}
                              {vacation.time_range ? ` · ${vacation.time_range}` : ''}
                              {vacation.code ? ` (${vacation.code})` : ''}
                            </option>
                          ))}
                        </select>
                      )}
                    </Field>
                  )}
                  <Field label="Église d'attache" error={form.errors.eglise_attache}>
                    <input className="input-field" value={form.data.eglise_attache || ''} onChange={(e) => form.setData('eglise_attache', e.target.value)} placeholder="PHILA, extension, autre…" />
                  </Field>
                  <BoolField label="Souhaitez-vous œuvrer à PHILA après Apollos ? *" value={form.data.souhaite_oeuvrer_phila_apres_apollos} onChange={(v) => setBool('souhaite_oeuvrer_phila_apres_apollos', v)} error={form.errors.souhaite_oeuvrer_phila_apres_apollos} />
                </>
              )}

              {step === 5 && (
                <>
                  <div className="rounded-xl bg-phila-gray-50 p-5 text-sm leading-relaxed text-phila-gray-600">
                    <p className="font-semibold text-phila-black mb-3">Récapitulatif</p>
                    <p><strong>Nom :</strong> {form.data.prenom} {form.data.post_nom} {form.data.nom}</p>
                    <p><strong>E-mail :</strong> {form.data.email}</p>
                    <p><strong>Téléphone :</strong> {form.data.phone}</p>
                    <p><strong>Commune :</strong> {form.data.commune_habitation}</p>
                    {isEcapPresentiel && selectedVacation && (
                      <p><strong>Vacation :</strong> {selectedVacation.name}</p>
                    )}
                    {isEcapSession && (
                      <p><strong>Mode ECAP :</strong> {form.data.ecap_is_online ? 'En ligne' : 'Présentiel'}</p>
                    )}
                  </div>
                  {legalDocument && (
                    <div className="rounded-xl border border-phila-orange/30 bg-phila-orange-pale/30 p-4 text-sm text-phila-gray-700">
                      <p className="font-semibold text-phila-black">{legalDocument.title}</p>
                      <p className="mt-1 text-xs text-phila-gray-600">Version {legalDocument.version}</p>
                      <a
                        href={legalDocument.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="mt-2 inline-flex font-semibold text-phila-orange hover:underline"
                      >
                        Lire le document PDF →
                      </a>
                    </div>
                  )}
                  {legalDocument && (
                    <label className="flex items-start gap-3 cursor-pointer">
                      <input
                        type="checkbox"
                        className="mt-1"
                        checked={form.data.accept_legal_document}
                        onChange={(e) => form.setData('accept_legal_document', e.target.checked)}
                      />
                      <span className="text-sm text-phila-gray-600">
                        J&apos;ai lu et j&apos;accepte le règlement d&apos;ordre intérieur ECAP (obligatoire pour m&apos;inscrire).
                      </span>
                    </label>
                  )}
                  {form.errors.accept_legal_document && (
                    <p className="text-sm text-red-600">{form.errors.accept_legal_document}</p>
                  )}
                  <label className="flex items-start gap-3 cursor-pointer">
                    <input
                      type="checkbox"
                      className="mt-1"
                      checked={form.data.accept_terms}
                      onChange={(e) => form.setData('accept_terms', e.target.checked)}
                    />
                    <span className="text-sm text-phila-gray-600">
                      J&apos;accepte que mes informations soient utilisées dans le cadre de ma formation à PHILA-CE.
                    </span>
                  </label>
                  {form.errors.accept_terms && (
                    <p className="text-sm text-red-600">{form.errors.accept_terms}</p>
                  )}
                </>
              )}

              <div className="flex gap-3 pt-2">
                {step > 1 && (
                  <button type="button" onClick={goBack} className="btn btn-outline flex-1">
                    Retour
                  </button>
                )}
                <LoadingButton
                  type="submit"
                  processing={form.processing}
                  loadingText="Enregistrement…"
                  className="btn btn-accent flex-1"
                >
                  {step === totalSteps ? 'Confirmer mon inscription' : 'Continuer'}
                </LoadingButton>
              </div>
            </form>
          </div>

          <p className="mt-6 text-center text-sm text-phila-gray-600">
            Déjà inscrit ?{' '}
            <Link href="/connexion" className="font-semibold text-phila-black underline">
              Se connecter
            </Link>
          </p>
        </div>
      </div>
    </PublicLayout>
  );
}

/**
 * Champ de formulaire avec label et message d'erreur.
 */
function Field({ label, error, children }) {
  return (
    <div>
      <label className="label-field">{label}</label>
      {children}
      {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
    </div>
  );
}

/**
 * Champ oui/non pour les questions booléennes.
 */
function BoolField({ label, value, onChange, error, trueLabel = 'Oui', falseLabel = 'Non', hint }) {
  return (
    <div>
      <p className="label-field">{label}</p>
      <div className="flex gap-3">
        {[{ v: true, l: trueLabel }, { v: false, l: falseLabel }].map(({ v, l }) => (
          <button
            key={l}
            type="button"
            onClick={() => onChange(v)}
              className={`flex-1 rounded-xl border px-4 py-3 text-sm font-medium transition ${
              value === v
                ? 'border-phila-orange bg-phila-orange text-white'
                : 'border-phila-gray-100 bg-white text-phila-gray-600 hover:border-phila-orange/40'
            }`}
          >
            {l}
          </button>
        ))}
      </div>
      {hint && <p className="mt-2 text-xs text-phila-gray-600">{hint}</p>}
      {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
    </div>
  );
}
