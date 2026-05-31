import { Head, Link } from '@inertiajs/react';
import EcapStaffLayout from '../../Components/Layout/EcapStaffLayout';

/**
 * Page d'accès refusé pour l'espace acteurs ECAP (style modale).
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function AccessDenied({
  title = 'Accès non autorisé',
  feature,
  requiredRole,
  yourRoles = [],
  hint,
  backUrl = '/ecap/acteurs/questions',
}) {
  return (
    <EcapStaffLayout active="">
      <Head title={`${title} — Acteurs ECAP`} />

      <div className="flex min-h-[70vh] items-center justify-center px-4 py-10">
        <div
          className="w-full max-w-md rounded-3xl border border-phila-gray-100 bg-white p-8 text-center shadow-xl"
          role="alertdialog"
          aria-labelledby="access-denied-title"
          aria-describedby="access-denied-desc"
        >
          <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-amber-100 text-4xl">
            🔒
          </div>

          <h1 id="access-denied-title" className="mt-6 font-display text-2xl font-bold text-phila-black">
            {title}
          </h1>

          {feature && (
            <p className="mt-2 text-sm font-semibold text-phila-orange">{feature}</p>
          )}

          <p id="access-denied-desc" className="mt-4 text-sm leading-relaxed text-phila-gray-600">
            {hint}
          </p>

          <div className="mt-6 space-y-2 rounded-2xl bg-phila-gray-50 px-4 py-3 text-left text-xs text-phila-gray-700">
            <p>
              <span className="font-semibold text-phila-black">Rôle requis :</span> {requiredRole}
            </p>
            {yourRoles.length > 0 ? (
              <p>
                <span className="font-semibold text-phila-black">Vos rôles :</span> {yourRoles.join(', ')}
              </p>
            ) : (
              <p className="text-amber-800">Aucune affectation ECAP active sur votre compte.</p>
            )}
          </div>

          <Link
            href={backUrl}
            className="btn btn-accent mt-8 inline-flex w-full justify-center py-3 text-sm"
          >
            Retour aux questions ECAP
          </Link>

          <p className="mt-4 text-[10px] text-phila-gray-400">
            Si vous pensez qu&apos;il s&apos;agit d&apos;une erreur, contactez l&apos;administration (menu Acteurs ECAP).
          </p>
        </div>
      </div>
    </EcapStaffLayout>
  );
}
