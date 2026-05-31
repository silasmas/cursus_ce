import { Head, Link, usePage } from '@inertiajs/react';
import MentorLayout from '../../Components/Layout/MentorLayout';
import UserAvatar from '../../Components/UI/UserAvatar';

/**
 * Liste des mentorés actifs du mentor.
 */
export default function Mentees({ mentees }) {
  const { flash } = usePage().props;

  return (
    <MentorLayout active="mentees">
      <Head title="Mes mentorés" />
      <div className="container-phila py-10">
        <div className="mb-6 flex items-center justify-between">
          <h1 className="font-display text-2xl font-bold">Mes mentorés ({mentees.length})</h1>
          <Link href="/mentor" className="text-sm text-phila-orange hover:underline">← Tableau de bord</Link>
        </div>

        {flash?.status && (
          <div className="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{flash.status}</div>
        )}

        {mentees.length === 0 ? (
          <div className="card text-center text-sm text-phila-gray-600">
            Aucun mentoré actif pour le moment.
          </div>
        ) : (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {mentees.map((mentee) => (
              <Link
                key={mentee.assignment_id}
                href={`/mentor/mentore/${mentee.assignment_id}`}
                className="card block transition hover:border-phila-orange/40 hover:shadow-md"
              >
                <div className="flex items-start gap-3">
                  <UserAvatar
                    avatarUrl={mentee.avatar_url}
                    name={mentee.name}
                    initials={mentee.initials}
                    size="sm"
                  />
                  <div className="min-w-0 flex-1">
                    <p className="font-display font-bold">{mentee.name}</p>
                    <p className="text-xs text-phila-gray-600">{mentee.program}</p>
                    <div className="mt-1 flex flex-wrap gap-2 text-[10px] text-phila-gray-500">
                      {mentee.gender && <span>{mentee.gender}</span>}
                      {mentee.age != null && <span>{mentee.age} ans</span>}
                      {mentee.country && <span>{mentee.country}</span>}
                    </div>
                    <p className="mt-1 text-[10px] text-phila-gray-400">Depuis le {mentee.started_at}</p>
                    {mentee.pending_submissions > 0 && (
                      <p className="mt-2 text-xs font-semibold text-amber-700">
                        {mentee.pending_submissions} TP à corriger
                      </p>
                    )}
                  </div>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>
    </MentorLayout>
  );
}
