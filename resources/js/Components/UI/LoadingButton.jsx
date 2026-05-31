/**
 * Bouton avec état de chargement pendant le traitement.
 *
 * @param {Object} props
 * @param {boolean} [props.processing=false] En cours de traitement
 * @param {string} [props.loadingText='Traitement…'] Texte pendant chargement
 * @param {React.ReactNode} props.children Libellé normal
 * @param {string} [props.className=''] Classes CSS
 * @param {string} [props.type='button'] Type HTML
 * @param {boolean} [props.disabled=false] Désactivé
 * @param {Function} [props.onClick] Clic
 * @returns {JSX.Element}
 */
export default function LoadingButton({
  processing = false,
  loadingText = 'Traitement…',
  children,
  className = '',
  type = 'button',
  disabled = false,
  onClick,
  ...rest
}) {
  const isDisabled = disabled || processing;

  return (
    <button
      type={type}
      disabled={isDisabled}
      onClick={onClick}
      className={`relative inline-flex items-center justify-center gap-2 ${className} ${processing ? 'pointer-events-none opacity-80' : ''}`}
      {...rest}
    >
      {processing && (
        <span
          className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
          aria-hidden="true"
        />
      )}
      <span>{processing ? loadingText : children}</span>
    </button>
  );
}
