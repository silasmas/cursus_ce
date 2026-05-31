/**
 * Badges tests / TP sur une étape ou un module.
 *
 * @param {Object} props
 * @param {Object} props.item Étape ou module
 * @param {boolean} [props.muted=false] Style atténué
 * @returns {JSX.Element|null}
 */
export default function AssessmentBadges({ item, muted = false }) {
  if (!item?.has_quiz && !item?.has_tp) {
    return null;
  }

  return (
    <div className="mt-2 flex flex-wrap gap-1.5">
      {item.has_quiz && (
        <span className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ${
          item.quiz_passed === true && !muted
            ? 'bg-green-100 text-green-800'
            : muted
              ? 'bg-phila-gray-100 text-phila-gray-500'
              : 'bg-blue-100 text-blue-800'
        }`}>
          📝 Test{(item.quiz_count ?? 0) > 1 ? ` (${item.quiz_count})` : ''}
          {item.quiz_passed === true && !muted && ' ✓'}
        </span>
      )}
      {item.has_tp && (
        <span className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ${
          item.tp_status === 'approved' && !muted
            ? 'bg-green-100 text-green-800'
            : item.tp_status === 'pending' && !muted
              ? 'bg-amber-100 text-amber-800'
              : muted
                ? 'bg-phila-gray-100 text-phila-gray-500'
                : 'bg-purple-100 text-purple-800'
        }`}>
          📋 TP{(item.tp_count ?? 0) > 1 ? ` (${item.tp_count})` : ''}
          {item.tp_status === 'approved' && !muted && ' ✓'}
        </span>
      )}
    </div>
  );
}
