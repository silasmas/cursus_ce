import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Components/Layout/AppLayout';
import UserAvatar from '../../Components/UI/UserAvatar';

/**
 * Profil public d'un membre ECAP (lecture seule).
 *
 * @param {Object} props Props Inertia
 * @param {Object} props.member Données du membre
 * @param {string} props.backUrl URL de retour
 * @param {string} props.backLabel Libellé retour
 * @param {boolean} props.canMessage Bouton message acteur
 * @param {string|null} props.messageUrl Lien messagerie
 * @returns {JSX.Element}
 */
export default function MemberShow({
  member,
  backUrl = '/mon-espace/ecap/questions',
  backLabel = '← Questions ECAP',
  canMessage = false,
  messageUrl = null,
}) {
  const location = [member?.commune_habitation, member?.quartier_habitation].filter(Boolean).join(', ');

  return (
    <AppLayout>
      <Head title={member?.name ? `${member.name} — Profil` : 'Profil membre'} />

      <div className="container-phila py-8">
        <Link href={backUrl} className="text-sm font-semibold text-phila-orange hover:underline">
          {backLabel}
        </Link>

        <div className="card mt-4 max-w-2xl">
          <div className="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
            <UserAvatar
              avatarUrl={member?.avatar_url}
              name={member?.name}
              sizeClass="h-24 w-24"
              textClass="text-2xl"
              className="bg-phila-orange text-white"
            />
            <div className="min-w-0 flex-1 text-center sm:text-left">
              <h1 className="font-display text-2xl font-bold text-phila-black">{member?.name}</h1>
              {member?.profession && (
                <p className="mt-1 text-sm text-phila-gray-600">{member.profession}</p>
              )}
              {member?.ecap_roles?.length > 0 && (
                <div className="mt-3 flex flex-wrap justify-center gap-2 sm:justify-start">
                  {member.ecap_roles.map((role) => (
                    <span
                      key={role}
                      className="rounded-full bg-phila-orange-pale px-3 py-1 text-xs font-semibold text-phila-orange"
                    >
                      {role}
                    </span>
                  ))}
                </div>
              )}
            </div>
          </div>

          {location && (
            <p className="mt-6 text-sm text-phila-gray-600">
              <span className="font-semibold text-phila-black">Localisation :</span> {location}
            </p>
          )}

          {member?.bio && (
            <div className="mt-4 rounded-xl bg-phila-gray-50 px-4 py-3 text-sm leading-relaxed text-phila-gray-700">
              {member.bio}
            </div>
          )}

          {canMessage && messageUrl && (
            <div className="mt-6">
              <Link href={messageUrl} className="btn btn-accent px-5 py-2 text-sm">
                Envoyer un message
              </Link>
            </div>
          )}
        </div>
      </div>
    </AppLayout>
  );
}
