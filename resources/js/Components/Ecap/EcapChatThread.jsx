import { usePage } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { playMessageReceivedSound, playMessageSentSound } from '../../lib/portalSounds';

const POLL_MS = 2000;

/**
 * Fil de conversation ECAP avec polling par interlocuteur.
 *
 * @param {Object} props Props du fil
 * @returns {JSX.Element}
 */
export default function EcapChatThread({
  initialMessages = [],
  peerUserId,
  pollUrl,
  sendUrl,
  disabled = false,
  className = 'max-h-[min(60vh,520px)]',
  mineVariant = 'member',
}) {
  const { auth } = usePage().props;
  const viewerId = auth?.user?.id ?? null;
  const [messages, setMessages] = useState(initialMessages);
  const [body, setBody] = useState('');
  const [sending, setSending] = useState(false);
  const [loading, setLoading] = useState(false);
  const lastIdRef = useRef(0);
  const scrollRef = useRef(null);
  const textareaRef = useRef(null);

  const isMine = useCallback(
    (message) => {
      if (typeof message.is_mine === 'boolean') {
        return message.is_mine;
      }

      if (viewerId && message.sender_user_id) {
        return Number(message.sender_user_id) === Number(viewerId);
      }

      return false;
    },
    [viewerId],
  );

  const syncLastId = useCallback((list) => {
    if (list.length > 0) {
      lastIdRef.current = list[list.length - 1].id;
    } else {
      lastIdRef.current = 0;
    }
  }, []);

  useEffect(() => {
    setMessages(initialMessages);
    syncLastId(initialMessages);
  }, [initialMessages, syncLastId]);

  useEffect(() => {
    if (!peerUserId || !pollUrl) {
      return undefined;
    }

    const loadThread = async () => {
      setLoading(true);

      try {
        const response = await fetch(`${pollUrl}?peer=${peerUserId}`, {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });

        if (response.ok) {
          const data = await response.json();
          const list = data.messages ?? [];
          setMessages(list);
          syncLastId(list);
        }
      } finally {
        setLoading(false);
      }
    };

    loadThread();
  }, [peerUserId, pollUrl, syncLastId]);

  useEffect(() => {
    if (!pollUrl || !peerUserId || disabled) {
      return undefined;
    }

    const interval = window.setInterval(async () => {
      const since = lastIdRef.current;
      const response = await fetch(`${pollUrl}?peer=${peerUserId}&since=${since}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });

      if (!response.ok) {
        return;
      }

      const data = await response.json();

      if (data.messages?.length) {
        let hasIncoming = false;

        setMessages((current) => {
          const merged = [...current];

          data.messages.forEach((message) => {
            if (!merged.some((row) => row.id === message.id)) {
              if (!isMine(message)) {
                hasIncoming = true;
              }

              merged.push(message);
            }
          });

          syncLastId(merged);

          return merged;
        });

        if (hasIncoming) {
          playMessageReceivedSound();
        }
      }
    }, POLL_MS);

    return () => window.clearInterval(interval);
  }, [pollUrl, peerUserId, disabled, syncLastId, isMine]);

  useEffect(() => {
    scrollRef.current?.scrollTo({ top: scrollRef.current.scrollHeight, behavior: 'smooth' });
  }, [messages]);

  const resizeTextarea = () => {
    const element = textareaRef.current;

    if (!element) {
      return;
    }

    element.style.height = 'auto';
    element.style.height = `${Math.min(element.scrollHeight, 120)}px`;
  };

  useEffect(() => {
    resizeTextarea();
  }, [body]);

  const handleSend = async (event) => {
    event.preventDefault();

    if (sending || !body.trim() || !peerUserId || disabled) {
      return;
    }

    setSending(true);

    try {
      const response = await fetch(sendUrl, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          recipient_user_id: peerUserId,
          body: body.trim(),
        }),
      });

      if (!response.ok) {
        const errorPayload = await response.json().catch(() => ({}));
        const message = errorPayload.message ?? 'Impossible d\'envoyer le message.';

        window.alert(message);

        return;
      }

      const data = await response.json();

      if (data.message) {
        setMessages((current) => {
          const merged = [...current, data.message];
          syncLastId(merged);
          return merged;
        });
        setBody('');
        playMessageSentSound();
      }
    } finally {
      setSending(false);
    }
  };

  const mineBubbleClass =
    mineVariant === 'staff'
      ? 'rounded-br-md bg-emerald-600 text-white'
      : 'rounded-br-md bg-phila-orange text-white';

  const handleKeyDown = (event) => {
    if (event.key === 'Enter' && !event.shiftKey && !sending) {
      event.preventDefault();
      handleSend(event);
    }
  };

  return (
    <div className="flex min-h-0 flex-1 flex-col">
      <div ref={scrollRef} className={`min-h-0 flex-1 space-y-2 overflow-y-auto p-4 ${className}`}>
        {loading && messages.length === 0 && (
          <p className="text-center text-xs text-phila-gray-500">Chargement…</p>
        )}
        {!loading && messages.length === 0 && (
          <p className="text-center text-xs text-phila-gray-500">Aucun message. Écrivez le premier.</p>
        )}
        {messages.map((message) => {
          const mine = isMine(message);

          return (
            <div key={message.id} className={`flex ${mine ? 'justify-end' : 'justify-start'}`}>
              <div
                className={`max-w-[85%] rounded-2xl px-3 py-2 text-sm shadow-sm ${
                  mine ? mineBubbleClass : 'rounded-bl-md bg-white text-phila-black ring-1 ring-phila-gray-100'
                }`}
              >
                {!mine && message.sender_name && (
                  <p className="mb-0.5 text-[10px] font-semibold text-phila-orange">{message.sender_name}</p>
                )}
                <p className="whitespace-pre-wrap break-words">{message.body}</p>
                <p className={`mt-1 text-[10px] ${mine ? 'text-white/70' : 'text-phila-gray-400'}`}>
                  {message.created_at_time || message.created_at}
                </p>
              </div>
            </div>
          );
        })}
      </div>
      <form onSubmit={handleSend} className="flex shrink-0 items-end gap-2 border-t border-phila-gray-100 bg-white p-3">
        <textarea
          ref={textareaRef}
          rows={1}
          className="min-h-[44px] max-h-[120px] min-w-0 flex-1 resize-none rounded-3xl border border-phila-gray-200 px-4 py-2.5 text-sm leading-snug disabled:bg-phila-gray-50"
          placeholder="Écrire un message… (Entrée pour envoyer)"
          value={body}
          disabled={disabled || !peerUserId || sending}
          onChange={(event) => setBody(event.target.value)}
          onKeyDown={handleKeyDown}
        />
        <button
          type="submit"
          disabled={sending || disabled || !peerUserId || !body.trim()}
          aria-busy={sending}
          className={`mb-0.5 min-w-[7.5rem] shrink-0 rounded-full px-5 py-2.5 text-sm font-semibold transition ${
            sending ? 'cursor-wait bg-phila-orange/80 text-white' : 'btn btn-accent'
          }`}
        >
          {sending ? (
            <span className="inline-flex items-center gap-2">
              <span
                className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
                aria-hidden
              />
              Envoi…
            </span>
          ) : (
            'Envoyer'
          )}
        </button>
      </form>
    </div>
  );
}
