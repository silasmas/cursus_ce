import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Components/Layout/AppLayout';
import EcapSessionTimeline from '../../Components/Dashboard/EcapSessionTimeline';

/**
 * Page calendrier timeline ECAP (fidèle).
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function Calendar({ timeline }) {
  return (
    <AppLayout>
      <Head title="Calendrier ECAP" />

      <div className="container-phila py-8">
        <Link href="/mon-espace" className="text-sm text-phila-orange hover:underline">
          ← Mon espace
        </Link>
        <h1 className="mt-2 font-display text-2xl font-bold text-phila-black">Calendrier de session</h1>
        <p className="text-sm text-phila-gray-600">
          Modules, activités et périodes de votre génération ECAP.
        </p>

        {!timeline?.has_session ? (
          <div className="mt-8 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-10 text-center">
            <p className="text-4xl">📅</p>
            <p className="mt-3 font-semibold text-amber-900">Session ECAP non trouvée</p>
            <p className="mt-2 text-sm text-amber-800">
              Vérifiez que votre inscription à une session ECAP active est bien enregistrée sur votre profil.
            </p>
          </div>
        ) : timeline.items.length === 0 ? (
          <div className="mt-8 rounded-2xl border border-dashed border-phila-gray-200 bg-white px-6 py-10 text-center">
            <p className="text-4xl">📅</p>
            <p className="mt-3 font-semibold text-phila-black">Calendrier en préparation</p>
            <p className="mt-2 text-sm text-phila-gray-600">
              L&apos;administration n&apos;a pas encore publié les dates pour la session{' '}
              <strong>{timeline.session_name}</strong>.
            </p>
            <p className="mt-2 text-xs text-phila-gray-500">
              Admin : Sessions & calendrier → ouvrir la session → onglet « Calendrier (modules & activités) ».
            </p>
          </div>
        ) : (
          <div className="mt-6">
            <EcapSessionTimeline timeline={timeline} />
          </div>
        )}
      </div>
    </AppLayout>
  );
}
