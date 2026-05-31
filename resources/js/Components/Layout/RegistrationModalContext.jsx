import { createContext, useContext } from 'react';

export const RegistrationModalContext = createContext(null);

/**
 * Ouvre la modale d'inscription ou redirige si les inscriptions sont ouvertes.
 *
 * @returns {{ openRegistrationInfo: Function, registrationOpen: boolean }}
 */
export function useRegistrationModal() {
  const context = useContext(RegistrationModalContext);

  if (!context) {
    throw new Error('useRegistrationModal doit être utilisé dans PublicLayout');
  }

  return context;
}
