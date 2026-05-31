import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

/**
 * Formate un décompte en jours, heures et minutes.
 *
 * @param {number} totalSeconds Secondes restantes
 * @returns {{ days: number, hours: number, minutes: number }}
 */
function formatCountdown(totalSeconds) {
  const days = Math.floor(totalSeconds / 86400);
  const hours = Math.floor((totalSeconds % 86400) / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);

  return { days, hours, minutes };
}

/**
 * Bandeau ECAP avec countdown (affiché uniquement si inscriptions ouvertes).
 *
 * @param {Object} props
 * @param {Object} props.session Données session ECAP
 * @returns {JSX.Element|null}
 */
export default function EcapRegistrationCountdown({ session }) {
  const [remaining, setRemaining] = useState(session?.seconds_remaining ?? null);

  useEffect(() => {
    if (session?.seconds_remaining == null) {
      return undefined;
    }

    setRemaining(session.seconds_remaining);

    const interval = window.setInterval(() => {
      setRemaining((value) => (value == null || value <= 0 ? 0 : value - 1));
    }, 1000);

    return () => window.clearInterval(interval);
  }, [session?.seconds_remaining]);

  if (!session?.is_registration_open) {
    return null;
  }

  const countdown = remaining != null ? formatCountdown(remaining) : null;

  return (
    <section className="border-y border-phila-orange/20 bg-phila-orange-pale py-14">
      <div className="container-phila">
        <div className="mx-auto max-w-3xl rounded-2xl border border-phila-orange/30 bg-white p-8 text-center shadow-sm">
          <p className="text-xs font-semibold uppercase tracking-[0.2em] text-phila-orange">ECAP</p>
          <h2 className="mt-2 font-display text-2xl font-bold text-phila-black">{session.name}</h2>
          {session.generation_number && (
            <p className="mt-1 text-sm text-phila-gray-600">{session.generation_number}ᵉ session ECAP</p>
          )}
          <p className="mt-3 inline-flex rounded-full bg-phila-black px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">
            Inscriptions ouvertes
          </p>

          {session.starts_on && session.ends_on && (
            <p className="mt-4 text-sm text-phila-gray-600">
              Session du {session.starts_on} au {session.ends_on}
              {session.modules_scheduled > 0 && ` · ${session.modules_scheduled} module(s) planifié(s)`}
            </p>
          )}

          {countdown && remaining > 0 && (
            <div className="mt-6 flex justify-center gap-4">
              {[
                { label: 'Jours', value: countdown.days },
                { label: 'Heures', value: countdown.hours },
                { label: 'Minutes', value: countdown.minutes },
              ].map((unit) => (
                <div key={unit.label} className="min-w-[72px] rounded-xl bg-phila-orange-pale px-3 py-3">
                  <strong className="block font-display text-2xl font-extrabold text-phila-orange">{unit.value}</strong>
                  <span className="text-[10px] font-semibold uppercase tracking-wide text-phila-gray-600">{unit.label}</span>
                </div>
              ))}
            </div>
          )}

          <p className="mt-4 text-sm text-phila-gray-600">
            Rejoignez la prochaine session ECAP avant la clôture des inscriptions.
          </p>

          <Link href="/inscription" className="btn btn-accent mt-6 px-8">
            S&apos;inscrire à ECAP
          </Link>
        </div>
      </div>
    </section>
  );
}
