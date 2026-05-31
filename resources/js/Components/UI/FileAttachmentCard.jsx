/**
 * Icône selon l'extension ou le type MIME.
 *
 * @param {string|null} fileName
 * @returns {string}
 */
export function fileTypeIcon(fileName) {
  const extension = (fileName ?? '').split('.').pop()?.toLowerCase() ?? '';

  if (['pdf'].includes(extension)) {
    return '📕';
  }

  if (['doc', 'docx', 'odt'].includes(extension)) {
    return '📘';
  }

  if (['xls', 'xlsx', 'csv'].includes(extension)) {
    return '📗';
  }

  if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(extension)) {
    return '🖼️';
  }

  if (['zip', 'rar', '7z'].includes(extension)) {
    return '🗜️';
  }

  return '📎';
}

/**
 * Carte fichier joint avec icône et lien de téléchargement.
 *
 * @param {Object} props
 * @param {string|null} props.url URL du fichier
 * @param {string} [props.label] Libellé
 * @param {string|null} [props.fileName] Nom affiché
 * @param {string} [props.subtitle] Sous-titre (date, auteur…)
 * @returns {JSX.Element|null}
 */
export default function FileAttachmentCard({ url, label = 'Fichier joint', fileName = null, subtitle = null }) {
  if (!url) {
    return null;
  }

  const displayName = fileName ?? label;
  const icon = fileTypeIcon(displayName);

  return (
    <a
      href={url}
      target="_blank"
      rel="noopener noreferrer"
      download
      className="flex items-center gap-3 rounded-xl border border-phila-gray-200 bg-white px-4 py-3 shadow-sm transition hover:border-phila-orange/40 hover:bg-phila-orange-pale/30"
    >
      <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-phila-gray-50 text-2xl">
        {icon}
      </span>
      <span className="min-w-0 flex-1">
        <span className="block truncate text-sm font-semibold text-phila-black">{label}</span>
        <span className="block truncate text-xs text-phila-gray-500">{displayName}</span>
        {subtitle && <span className="mt-0.5 block text-[10px] text-phila-gray-400">{subtitle}</span>}
      </span>
      <span className="shrink-0 text-xs font-semibold text-phila-orange">Télécharger</span>
    </a>
  );
}
