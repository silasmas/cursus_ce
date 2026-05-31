import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import AvatarCropModal from '../../Components/Profile/AvatarCropModal';
import AppLayout from '../../Components/Layout/AppLayout';
import UserAvatar from '../../Components/UI/UserAvatar';

/**
 * Bannière de retour utilisateur (succès ou erreur).
 *
 * @param {Object} props
 * @returns {JSX.Element|null}
 */
function ProfileFeedbackBanner({ feedback, onClose }) {
  if (!feedback?.message) {
    return null;
  }

  const isSuccess = feedback.type === 'success';

  return (
    <div
      className={`mt-4 flex items-start justify-between gap-3 rounded-xl border px-4 py-3 text-sm ${
        isSuccess
          ? 'border-green-200 bg-green-50 text-green-800'
          : 'border-red-200 bg-red-50 text-red-800'
      }`}
      role="alert"
    >
      <p>{feedback.message}</p>
      <button type="button" onClick={onClose} className="shrink-0 text-lg leading-none opacity-60 hover:opacity-100">
        ×
      </button>
    </div>
  );
}

/**
 * Page profil utilisateur (fidèle/acteur).
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function ProfileShow({
  profile,
  isMentor = false,
  isMentee = false,
  assignedMentor = null,
  backUrl = '/mon-espace',
  backLabel = '← Mon espace',
}) {
  const { flash } = usePage().props;
  const fileInputRef = useRef(null);
  const previewUrlRef = useRef(null);
  const [cropSrc, setCropSrc] = useState(null);
  const [savedAvatarUrl, setSavedAvatarUrl] = useState(profile?.avatar_url ?? null);
  const [localPreviewUrl, setLocalPreviewUrl] = useState(null);
  const [feedback, setFeedback] = useState(null);

  const form = useForm({
    name: profile?.name ?? '',
    prenom: profile?.prenom ?? '',
    post_nom: profile?.post_nom ?? '',
    nom: profile?.nom ?? '',
    profession: profile?.profession ?? '',
    commune_habitation: profile?.commune_habitation ?? '',
    quartier_habitation: profile?.quartier_habitation ?? '',
    adresse_numero_avenue: profile?.adresse_numero_avenue ?? '',
    bio: profile?.bio ?? '',
    avatar: null,
    remove_avatar: false,
  });

  useEffect(() => {
    const nextAvatar = profile?.avatar_url ?? flash?.avatar_url ?? null;

    if (nextAvatar) {
      setSavedAvatarUrl(nextAvatar);
    }
  }, [profile?.avatar_url, flash?.avatar_url]);

  useEffect(() => {
    if (flash?.status) {
      setFeedback({ type: 'success', message: flash.status });
    } else if (flash?.error) {
      setFeedback({ type: 'error', message: flash.error });
    }
  }, [flash?.status, flash?.error]);

  useEffect(() => {
    return () => {
      if (previewUrlRef.current) {
        URL.revokeObjectURL(previewUrlRef.current);
      }
    };
  }, []);

  const avatarPreview = useMemo(() => {
    if (form.data.remove_avatar) {
      return null;
    }

    if (localPreviewUrl) {
      return localPreviewUrl;
    }

    return savedAvatarUrl;
  }, [form.data.remove_avatar, localPreviewUrl, savedAvatarUrl]);

  const submit = (event) => {
    event.preventDefault();

    form.post('/mon-espace/profil', {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        if (previewUrlRef.current) {
          URL.revokeObjectURL(previewUrlRef.current);
          previewUrlRef.current = null;
        }

        setLocalPreviewUrl(null);
        form.setData('avatar', null);
        form.setData('remove_avatar', false);

        router.reload({
          only: ['auth', 'profile', 'flash'],
          preserveScroll: true,
        });
      },
      onError: () => {
        const firstError = Object.values(form.errors)[0];

        setFeedback({
          type: 'error',
          message: firstError ?? 'Impossible d\'enregistrer le profil. Vérifiez les champs.',
        });
      },
    });
  };

  const handleAvatarChange = (event) => {
    const file = event.target.files?.[0];

    if (file) {
      setCropSrc(URL.createObjectURL(file));
    }
  };

  const handleCropConfirm = (blob) => {
    const file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });

    if (previewUrlRef.current) {
      URL.revokeObjectURL(previewUrlRef.current);
    }

    const preview = URL.createObjectURL(file);
    previewUrlRef.current = preview;

    form.setData('avatar', file);
    form.setData('remove_avatar', false);
    setLocalPreviewUrl(preview);
    setCropSrc(null);

    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const removeAvatar = () => {
    if (previewUrlRef.current) {
      URL.revokeObjectURL(previewUrlRef.current);
      previewUrlRef.current = null;
    }

    form.setData('avatar', null);
    form.setData('remove_avatar', true);
    setLocalPreviewUrl(null);

    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const hasFormErrors = Object.keys(form.errors).length > 0;

  return (
    <AppLayout>
      <Head title="Mon profil" />

      {cropSrc && (
        <AvatarCropModal
          imageSrc={cropSrc}
          onConfirm={handleCropConfirm}
          onCancel={() => {
            setCropSrc(null);

            if (fileInputRef.current) {
              fileInputRef.current.value = '';
            }
          }}
        />
      )}

      <div className="container-phila py-8">
        <Link href={backUrl} className="text-sm text-phila-orange hover:underline">
          {backLabel}
        </Link>
        <h1 className="mt-2 font-display text-2xl font-bold text-phila-black">Mon profil</h1>
        <p className="text-sm text-phila-gray-600">Modifiez vos informations personnelles (hors identifiants uniques).</p>

        <ProfileFeedbackBanner feedback={feedback} onClose={() => setFeedback(null)} />

        {hasFormErrors && !feedback && (
          <div className="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            Veuillez corriger les erreurs indiquées sous les champs.
          </div>
        )}

        <div className="mt-4 flex flex-wrap gap-2">
          {isMentor && (
            <span className="rounded-full bg-phila-orange px-3 py-1 text-xs font-semibold text-white">
              ★ Mentor Métamorpho
            </span>
          )}
          {isMentee && (
            <span className="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">
              Mentoré Métamorpho
            </span>
          )}
        </div>

        {isMentee && assignedMentor && (
          <div className="mt-4 flex items-center gap-4 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4">
            {assignedMentor.avatar_url ? (
              <img src={assignedMentor.avatar_url} alt="" className="h-14 w-14 rounded-full object-cover bg-white" />
            ) : (
              <span className="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-200 text-lg font-bold text-indigo-800">
                {assignedMentor.initials}
              </span>
            )}
            <div>
              <p className="text-xs font-semibold uppercase text-indigo-600">Votre mentor</p>
              <p className="font-display font-bold text-phila-black">{assignedMentor.name}</p>
              {assignedMentor.started_at && (
                <p className="text-xs text-phila-gray-500">Depuis le {assignedMentor.started_at}</p>
              )}
              <Link href="/mon-espace/mentor" className="mt-1 inline-block text-xs text-phila-orange hover:underline">
                Ouvrir l&apos;espace mentoré →
              </Link>
            </div>
          </div>
        )}

        <form onSubmit={submit} className="mt-6 grid gap-4 rounded-2xl border border-phila-gray-100 bg-white p-5 sm:grid-cols-2">
          <div className="sm:col-span-2 flex flex-wrap items-center gap-4 border-b border-phila-gray-100 pb-5">
            <UserAvatar
              avatarUrl={avatarPreview}
              name={form.data.name || profile?.name}
              sizeClass="h-20 w-20"
              textClass="text-xl"
            />
            <div className="flex flex-wrap gap-2">
              <input ref={fileInputRef} type="file" accept="image/*" className="hidden" onChange={handleAvatarChange} />
              <button
                type="button"
                onClick={() => fileInputRef.current?.click()}
                className="rounded-xl border border-phila-gray-200 px-4 py-2 text-sm font-medium hover:bg-phila-gray-50"
              >
                Changer la photo
              </button>
              {(savedAvatarUrl || localPreviewUrl) && !form.data.remove_avatar && (
                <button
                  type="button"
                  onClick={removeAvatar}
                  className="rounded-xl border border-red-200 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                >
                  Supprimer
                </button>
              )}
            </div>
            {form.errors.avatar && <p className="w-full text-xs text-red-600">{form.errors.avatar}</p>}
            <p className="w-full text-xs text-phila-gray-500">
              Pensez à cliquer sur « Enregistrer » après avoir choisi ou recadré votre photo.
            </p>
          </div>

          <Field label="Nom affiché" error={form.errors.name}>
            <input
              className="w-full rounded-xl border border-phila-gray-200 px-3 py-2"
              value={form.data.name}
              onChange={(event) => form.setData('name', event.target.value)}
            />
          </Field>

          <Field label="E-mail (non modifiable)">
            <input className="w-full rounded-xl border border-phila-gray-200 bg-phila-gray-50 px-3 py-2 text-phila-gray-500" value={profile?.email ?? ''} disabled />
          </Field>

          <Field label="Téléphone (non modifiable)">
            <input className="w-full rounded-xl border border-phila-gray-200 bg-phila-gray-50 px-3 py-2 text-phila-gray-500" value={profile?.phone ?? ''} disabled />
          </Field>

          <Field label="Prénom" error={form.errors.prenom}>
            <input className="w-full rounded-xl border border-phila-gray-200 px-3 py-2" value={form.data.prenom} onChange={(event) => form.setData('prenom', event.target.value)} />
          </Field>

          <Field label="Post-nom" error={form.errors.post_nom}>
            <input className="w-full rounded-xl border border-phila-gray-200 px-3 py-2" value={form.data.post_nom} onChange={(event) => form.setData('post_nom', event.target.value)} />
          </Field>

          <Field label="Nom" error={form.errors.nom}>
            <input className="w-full rounded-xl border border-phila-gray-200 px-3 py-2" value={form.data.nom} onChange={(event) => form.setData('nom', event.target.value)} />
          </Field>

          <Field label="Profession" error={form.errors.profession}>
            <input className="w-full rounded-xl border border-phila-gray-200 px-3 py-2" value={form.data.profession} onChange={(event) => form.setData('profession', event.target.value)} />
          </Field>

          <Field label="Commune" error={form.errors.commune_habitation}>
            <input className="w-full rounded-xl border border-phila-gray-200 px-3 py-2" value={form.data.commune_habitation} onChange={(event) => form.setData('commune_habitation', event.target.value)} />
          </Field>

          <Field label="Quartier" error={form.errors.quartier_habitation}>
            <input className="w-full rounded-xl border border-phila-gray-200 px-3 py-2" value={form.data.quartier_habitation} onChange={(event) => form.setData('quartier_habitation', event.target.value)} />
          </Field>

          <Field label="Adresse (n° / avenue)" error={form.errors.adresse_numero_avenue}>
            <input className="w-full rounded-xl border border-phila-gray-200 px-3 py-2" value={form.data.adresse_numero_avenue} onChange={(event) => form.setData('adresse_numero_avenue', event.target.value)} />
          </Field>

          <div className="sm:col-span-2">
            <Field label="Bio" error={form.errors.bio}>
              <textarea className="w-full rounded-xl border border-phila-gray-200 px-3 py-2" rows={4} value={form.data.bio} onChange={(event) => form.setData('bio', event.target.value)} />
            </Field>
          </div>

          <div className="sm:col-span-2">
            <button type="submit" disabled={form.processing} className="btn btn-accent px-5 py-2.5 text-sm">
              {form.processing ? 'Enregistrement…' : 'Enregistrer'}
            </button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}

function Field({ label, children, error }) {
  return (
    <label className="block">
      <span className="mb-1 block text-xs font-semibold uppercase tracking-wide text-phila-gray-600">{label}</span>
      {children}
      {error && <span className="mt-1 block text-xs text-red-600">{error}</span>}
    </label>
  );
}
