import { Head, Link, usePage } from '@inertiajs/react';
import EcapRegistrationCountdown from '../Components/Landing/EcapRegistrationCountdown';
import RegistrationStatusBanner from '../Components/Landing/RegistrationStatusBanner';
import PublicLayout from '../Components/Layout/PublicLayout';
import { useRegistrationModal } from '../Components/Layout/RegistrationModalContext';
import { isPublicRegistrationOpen, resolvePublicRegistration } from '../lib/publicRegistration';

const CTA_LABELS = {
  open: 'Commencer mon inscription',
  upcoming: 'Inscriptions pas encore ouvertes',
  closed: 'Inscriptions closes',
  disabled: 'Inscriptions indisponibles',
};

/**
 * Page publique unique de présentation de la plateforme PHILA-CE.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function Landing({ features, ecapSession, registration }) {
  const { flash } = usePage().props;
  const reg = registration ?? resolvePublicRegistration(usePage().props) ?? {};
  const registrationOpen = isPublicRegistrationOpen(reg);
  const status = reg.status ?? 'disabled';
  const ctaLabel = CTA_LABELS[status] ?? CTA_LABELS.disabled;

  return (
    <PublicLayout>
      <LandingContent
        features={features}
        ecapSession={ecapSession}
        reg={reg}
        registrationOpen={registrationOpen}
        ctaLabel={ctaLabel}
        flash={flash}
      />
    </PublicLayout>
  );
}

/**
 * Contenu de la landing (séparé pour accéder au contexte modale).
 */
function LandingContent({ features, ecapSession, reg, registrationOpen, ctaLabel, flash }) {
  const { openRegistrationInfo } = useRegistrationModal();

  return (
    <>
      <Head title="PHILA-CE – Plateforme de formation" />

      <section className="hero-gradient relative overflow-hidden text-white">
        <div className="absolute inset-0 opacity-20">
          <div className="absolute -left-20 top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl" />
          <div className="absolute -right-10 bottom-10 h-80 w-80 rounded-full bg-white/5 blur-3xl" />
        </div>

        <div className="container-phila relative py-24 sm:py-32">
          <div className="mx-auto max-w-3xl text-center">
            <img
              src="/images/phila-logo.png"
              alt="PHILA"
              className="logo-phila-orange mx-auto mb-8 h-24 w-24 rounded-full border border-phila-orange/30 shadow-2xl"
            />
            <p className="mb-4 text-xs font-medium uppercase tracking-[0.25em] text-white/70">
              Cité d&apos;Exaucement
            </p>
            <h1 className="font-display text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">
              Exposer pour{' '}
              <span className="text-phila-orange">Manifester</span>
            </h1>
            <p className="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-white/80">
              La plateforme de formation PHILA-CE vous accompagne pour grandir dans la foi,
              approfondir la Parole de Dieu et marcher dans votre appel au sein de la famille PHILA.
            </p>

            {flash?.error && (
              <div className="mx-auto mt-6 max-w-xl rounded-xl border border-red-300/40 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                {flash.error}
              </div>
            )}

            <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
              {registrationOpen ? (
                <Link href="/inscription" className="btn btn-accent px-8 py-3.5">
                  {CTA_LABELS.open}
                </Link>
              ) : (
                <button
                  type="button"
                  onClick={openRegistrationInfo}
                  className="rounded-xl border border-white/20 bg-white/10 px-8 py-3.5 text-sm text-white/90 hover:border-phila-orange hover:bg-phila-orange/10"
                >
                  {ctaLabel}
                </button>
              )}
              <Link href="/connexion" className="btn border border-white/30 px-8 py-3.5 text-white hover:border-phila-orange hover:bg-phila-orange/10">
                J&apos;ai déjà un compte
              </Link>
            </div>
          </div>
        </div>
      </section>

      <RegistrationStatusBanner registration={reg} />

      {registrationOpen && ecapSession && (
        <EcapRegistrationCountdown session={ecapSession} />
      )}

      <section className="py-20">
        <div className="container-phila">
          <div className="mx-auto mb-14 max-w-2xl text-center">
            <p className="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-phila-gray-600">
              Notre mission
            </p>
            <h2 className="font-display text-3xl font-bold text-phila-black sm:text-4xl">
              Une formation pour manifester la vie de Christ
            </h2>
            <p className="mt-4 text-phila-gray-600 leading-relaxed">
              Exposer chaque personne à la Parole de Dieu et à l&apos;œuvre de son Esprit
              pour que la vie de Christ se manifeste en lui personnellement et par lui socialement.
            </p>
          </div>

          <div className="grid gap-6 sm:grid-cols-2">
            {features.map((feature) => (
              <article key={feature.title} className="card group hover:-translate-y-1 hover:shadow-md transition-all">
                <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-phila-orange text-white">
                  <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <h3 className="font-display text-lg font-bold text-phila-black">{feature.title}</h3>
                <p className="mt-2 text-sm leading-relaxed text-phila-gray-600">{feature.description}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="border-t border-phila-gray-100 bg-white py-20">
        <div className="container-phila text-center">
          <h2 className="font-display text-3xl font-bold text-phila-black">
            Tu n&apos;es pas ici par hasard. Dieu t&apos;attend.
          </h2>
          <p className="mx-auto mt-4 max-w-xl text-phila-gray-600">
            Rejoignez la communauté de formation PHILA-CE et avancez dans votre parcours spirituel.
          </p>
          <div className="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
            {registrationOpen ? (
              <Link href="/inscription" className="btn btn-accent px-8">
                S&apos;inscrire maintenant
              </Link>
            ) : (
              <button type="button" onClick={openRegistrationInfo} className="btn btn-outline px-8">
                {ctaLabel}
              </button>
            )}
            <Link href="/connexion" className="btn btn-outline px-8">
              Se connecter
            </Link>
          </div>
        </div>
      </section>
    </>
  );
}
