import { userTimezoneLabel } from '../../utils/appointmentTime';

/**
 * Sélecteur date + heure (fuseau local du navigateur).
 *
 * @param {Object} props
 * @param {string} props.value Valeur datetime-local
 * @param {Function} props.onChange Callback changement
 * @param {boolean} [props.required=false] Champ obligatoire
 * @param {string} [props.label='Date et heure'] Label
 * @returns {JSX.Element}
 */
export default function DateTimePicker({
  value,
  onChange,
  required = false,
  label = 'Date et heure',
}) {
  const timezone = userTimezoneLabel();

  return (
    <div>
      <label className="label-field">{label}</label>
      <input
        type="datetime-local"
        className="input-field"
        value={value}
        onChange={(e) => onChange(e.target.value)}
        required={required}
        min={new Date().toISOString().slice(0, 16)}
      />
      <p className="mt-1 text-[11px] text-phila-gray-500">
        Fuseau horaire : <strong>{timezone}</strong> — l&apos;heure affichée correspond à votre appareil.
      </p>
    </div>
  );
}
