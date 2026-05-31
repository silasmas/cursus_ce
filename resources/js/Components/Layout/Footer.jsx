/**
 * Pied de page public PHILA-CE.
 *
 * @returns {JSX.Element}
 */
export default function Footer() {
  return (
    <footer className="border-t border-phila-gray-100 bg-white py-10">
      <div className="container-phila flex flex-col items-center gap-4 text-center sm:flex-row sm:justify-between sm:text-left">
        <div>
          <p className="font-display text-sm font-bold text-phila-black">PHILA – Cité d&apos;Exaucement</p>
          <p className="mt-1 text-sm text-phila-gray-600">Cultiver la vie de Christ</p>
        </div>
        <p className="text-xs text-phila-gray-400">
          © {new Date().getFullYear()} PHILA-CE. Tous droits réservés.
        </p>
      </div>
    </footer>
  );
}
