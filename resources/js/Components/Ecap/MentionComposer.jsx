import { useMemo, useRef, useState } from 'react';

/**
 * Extrait le token @ ou # en cours de frappe.
 *
 * @param {string} text Texte complet
 * @param {number} cursorPosition Position du curseur
 * @returns {{ type: string, query: string, start: number }|null}
 */
function activeMentionToken(text, cursorPosition) {
  const before = text.slice(0, cursorPosition);
  const match = before.match(/(^|\s)([@#])([\wÀ-ÿ_-]*)$/u);

  if (!match) {
    return null;
  }

  return {
    type: match[2],
    query: match[3].toLowerCase(),
    start: before.length - match[3].length - 1,
  };
}

/**
 * Compositeur style Facebook (@prof, @fidèle, @tous, #module, #chapitre).
 *
 * @param {Object} props Props du compositeur
 * @returns {JSX.Element}
 */
export default function MentionComposer({
  teachers = [],
  mentionUsers = [],
  courseModules = [],
  hashTags = [],
  defaultModuleId = null,
  processing = false,
  serverErrors = {},
  requireAddressee = true,
  placeholder = 'Quoi de neuf sur votre parcours ECAP ? Utilisez @ et #…',
  submitLabel = 'Publier',
  compact = false,
  onSubmit,
}) {
  const users = mentionUsers.length > 0 ? mentionUsers : teachers;
  const tags = hashTags.length > 0 ? hashTags : courseModules;
  const textareaRef = useRef(null);
  const [body, setBody] = useState('');
  const [courseModuleId, setCourseModuleId] = useState(defaultModuleId ?? tags.find((t) => t.kind === 'module')?.id ?? tags[0]?.id ?? '');
  const [addressAllTeachers, setAddressAllTeachers] = useState(true);
  const [addressedToUserId, setAddressedToUserId] = useState(null);
  const [cursor, setCursor] = useState(0);
  const [errors, setErrors] = useState({});

  const token = useMemo(() => activeMentionToken(body, cursor), [body, cursor]);

  const mentionOptions = useMemo(() => {
    if (!token) {
      return [];
    }

    if (token.type === '@') {
      const options = [];

      if (requireAddressee) {
        options.push({ key: 'all', label: '@tous', hint: 'Tous les enseignants ECAP' });
      }

      users
        .filter(
          (user) =>
            user.mention?.toLowerCase().includes(`@${token.query}`) ||
            user.name?.toLowerCase().includes(token.query),
        )
        .forEach((user) => {
          options.push({
            key: `user-${user.id}`,
            label: user.mention,
            hint: `${user.name}${user.role === 'teacher' ? ' · Enseignant' : ' · Fidèle'}`,
            userId: user.id,
            allTeachers: false,
          });
        });

      return options;
    }

    return tags
      .filter(
        (tag) =>
          tag.tag?.toLowerCase().includes(`#${token.query}`) ||
          tag.name?.toLowerCase().includes(token.query),
      )
      .map((tag) => ({
        key: `${tag.kind ?? 'module'}-${tag.id}`,
        label: tag.tag,
        hint: tag.kind === 'chapter' ? `Chapitre · ${tag.name}` : tag.name,
        moduleId: tag.kind === 'module' ? tag.id : tag.module_id,
        chapterId: tag.kind === 'chapter' ? tag.id : null,
      }));
  }, [token, users, tags, requireAddressee]);

  const applyMention = (insertText, meta = {}) => {
    if (!token) {
      return;
    }

    const before = body.slice(0, token.start);
    const after = body.slice(cursor);
    setBody(`${before}${insertText} ${after}`.replace(/\s+/g, ' ').trimStart());

    if (meta.moduleId) {
      setCourseModuleId(meta.moduleId);
    }

    if (meta.allTeachers) {
      setAddressAllTeachers(true);
      setAddressedToUserId(null);
    }

    if (meta.userId) {
      setAddressAllTeachers(false);
      setAddressedToUserId(meta.userId);
    }

    setTimeout(() => textareaRef.current?.focus(), 0);
  };

  const handleSelect = (option) => {
    if (token?.type === '@') {
      if (option.key === 'all') {
        applyMention('@tous', { allTeachers: true });
      } else {
        applyMention(option.label, { userId: option.userId });
      }
    }

    if (token?.type === '#') {
      applyMention(option.label, { moduleId: option.moduleId });
    }
  };

  const handleSubmit = (event) => {
    event.preventDefault();
    const localErrors = {};

    if (requireAddressee && !courseModuleId && !body.match(/#\w+/iu)) {
      localErrors.course_module_id = 'Associez un module avec #.';
    }

    if (!body.trim()) {
      localErrors.body = 'Écrivez votre message.';
    }

    if (Object.keys(localErrors).length > 0) {
      setErrors(localErrors);
      return;
    }

    const finalAddressAll = requireAddressee ? (addressAllTeachers || !addressedToUserId) : false;

    setErrors({});
    onSubmit({
      course_module_id: courseModuleId ? Number(courseModuleId) : null,
      body: body.trim(),
      address_all_teachers: finalAddressAll,
      addressed_to_user_id: finalAddressAll ? null : addressedToUserId,
    });
  };

  const addresseeSummary = !requireAddressee
    ? 'Réponse libre'
    : addressAllTeachers || !addressedToUserId
      ? '@tous les enseignants'
      : users.find((user) => user.id === addressedToUserId)?.mention ?? '@tous (par défaut)';

  const moduleSummary =
    tags.find((tag) => tag.id === Number(courseModuleId) && (tag.kind === 'module' || !tag.kind))?.name ??
    '— précisez avec #module';

  return (
    <form
      onSubmit={handleSubmit}
      className={`overflow-hidden rounded-2xl border border-phila-gray-100 bg-white shadow-sm ${compact ? '' : ''}`}
    >
      {!compact && (
        <div className="flex items-center gap-3 border-b border-phila-gray-100 px-4 py-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-full bg-phila-orange text-sm font-bold text-white">
            ?
          </div>
          <div className="min-w-0 flex-1">
            <p className="text-sm font-semibold text-phila-black">Publier une question</p>
            <p className="text-xs text-phila-gray-500">
              <strong>@</strong> prof / fidèle / <strong>@tous</strong> · <strong>#</strong> module ou chapitre
            </p>
          </div>
        </div>
      )}

      <div className="relative px-4 py-3">
        <textarea
          ref={textareaRef}
          rows={compact ? 2 : 4}
          className="w-full resize-none border-0 bg-transparent text-[15px] leading-relaxed text-phila-black placeholder:text-phila-gray-400 focus:outline-none focus:ring-0"
          placeholder={placeholder}
          value={body}
          onChange={(event) => {
            setBody(event.target.value);
            setCursor(event.target.selectionStart ?? 0);
          }}
          onClick={(event) => setCursor(event.target.selectionStart ?? 0)}
          onKeyUp={(event) => setCursor(event.target.selectionStart ?? 0)}
        />

        {token && mentionOptions.length > 0 && (
          <ul className="absolute left-4 right-4 top-full z-20 mt-1 max-h-48 overflow-auto rounded-xl border border-phila-gray-100 bg-white py-1 shadow-lg">
            {mentionOptions.map((option) => (
              <li key={option.key}>
                <button
                  type="button"
                  className="flex w-full flex-col px-4 py-2 text-left hover:bg-phila-gray-50"
                  onMouseDown={(event) => {
                    event.preventDefault();
                    handleSelect(option);
                  }}
                >
                  <span className="font-semibold text-phila-black">{option.label}</span>
                  {option.hint && <span className="text-xs text-phila-gray-500">{option.hint}</span>}
                </button>
              </li>
            ))}
          </ul>
        )}
      </div>

      {!compact && (
        <div className="flex flex-wrap gap-2 border-t border-phila-gray-100 bg-phila-gray-50 px-4 py-2 text-xs text-phila-gray-600">
          {requireAddressee && (
            <span className="rounded-full bg-white px-3 py-1">
              Réponse attendue : <strong>{addresseeSummary}</strong>
            </span>
          )}
          <span className="rounded-full bg-white px-3 py-1">
            Module : <strong>{moduleSummary}</strong>
          </span>
          <span className="rounded-full bg-amber-50 px-3 py-1 text-amber-900">Visible par tous les acteurs ECAP</span>
        </div>
      )}

      {(errors.body || errors.course_module_id || serverErrors.body || serverErrors.course_module_id) && (
        <div className="space-y-1 px-4 pb-2 text-xs text-red-600">
          {errors.body && <p>{errors.body}</p>}
          {errors.course_module_id && <p>{errors.course_module_id}</p>}
          {serverErrors.body && <p>{serverErrors.body}</p>}
          {serverErrors.course_module_id && <p>{serverErrors.course_module_id}</p>}
        </div>
      )}

      <div className={`border-t border-phila-gray-100 px-4 ${compact ? 'py-2' : 'py-3'}`}>
        <button type="submit" className="btn btn-accent w-full py-2.5" disabled={processing}>
          {processing ? 'Envoi…' : submitLabel}
        </button>
      </div>
    </form>
  );
}
