import { useEffect, useRef, useState } from 'react';
import LoadingButton from './LoadingButton';

/**
 * Chat mentor / mentoré avec envoi AJAX et polling.
 *
 * @param {Object} props
 * @param {Array} props.initialMessages Messages initiaux
 * @param {string} props.pollUrl URL de polling JSON
 * @param {string} props.sendUrl URL POST envoi message
 * @param {boolean} props.enabled Chat actif
 * @param {string} [props.placeholder] Placeholder input
 * @param {boolean} [props.compact=false] Mode compact (fenêtre flottante)
 * @returns {JSX.Element}
 */
export default function MentorChat({
  initialMessages,
  pollUrl,
  sendUrl,
  enabled,
  placeholder = 'Écrire un message…',
  compact = false,
}) {
  const [messages, setMessages] = useState(initialMessages ?? []);
  const [body, setBody] = useState('');
  const [sending, setSending] = useState(false);
  const bottomRef = useRef(null);
  const lastIdRef = useRef(
    initialMessages?.length ? Math.max(...initialMessages.map((m) => m.id)) : 0,
  );

  useEffect(() => {
    setMessages(initialMessages ?? []);
    if (initialMessages?.length) {
      lastIdRef.current = Math.max(...initialMessages.map((m) => m.id));
    }
  }, [initialMessages]);

  useEffect(() => {
    if (!enabled || !pollUrl) {
      return undefined;
    }

    const poll = async () => {
      try {
        const response = await fetch(`${pollUrl}?since=${lastIdRef.current}`, {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });

        if (!response.ok) {
          return;
        }

        const data = await response.json();

        if (data.messages?.length) {
          setMessages((prev) => {
            const ids = new Set(prev.map((m) => m.id));
            const merged = [...prev];

            for (const msg of data.messages) {
              if (!ids.has(msg.id)) {
                merged.push(msg);
              }
            }

            return merged;
          });
          lastIdRef.current = Math.max(lastIdRef.current, ...data.messages.map((m) => m.id));
        }
      } catch {
        // Polling silencieux
      }
    };

    const interval = setInterval(poll, 4000);
    poll();

    return () => clearInterval(interval);
  }, [enabled, pollUrl]);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const sendMessage = async () => {
    const text = body.trim();

    if (!text || sending || !sendUrl) {
      return;
    }

    setSending(true);

    try {
      const response = await fetch(sendUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ body: text }),
      });

      if (response.ok) {
        const data = await response.json();

        if (data.message) {
          setMessages((prev) => {
            if (prev.some((m) => m.id === data.message.id)) {
              return prev;
            }

            return [...prev, data.message];
          });
          lastIdRef.current = Math.max(lastIdRef.current, data.message.id);
        }

        setBody('');
      }
    } finally {
      setSending(false);
    }
  };

  const handleSubmit = (event) => {
    event.preventDefault();
    sendMessage();
  };

  const handleKeyDown = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      sendMessage();
    }
  };

  if (!enabled) {
    return (
      <div className="rounded-xl bg-phila-gray-50 p-4 text-sm text-phila-gray-600">
        Le chat s&apos;active lorsque vous atteignez l&apos;étape Métamorpho.
      </div>
    );
  }

  return (
    <div className="flex h-full flex-col space-y-3">
      <div className="flex items-center gap-2 text-xs text-green-700">
        <span className="h-2 w-2 animate-pulse rounded-full bg-green-500" />
        Chat actif — messages en direct
      </div>
      <div className={`flex-1 space-y-2 overflow-y-auto rounded-xl bg-phila-gray-50 p-3 ${compact ? 'max-h-64' : 'max-h-72'}`}>
        {messages.length === 0 ? (
          <p className="text-sm text-phila-gray-500">Aucun message pour le moment.</p>
        ) : (
          messages.map((msg) => (
            <div
              key={msg.id}
              className={`rounded-lg px-3 py-2 text-sm ${msg.is_mine ? 'ml-8 bg-phila-orange-pale' : 'mr-8 bg-white'}`}
            >
              <p className="text-xs font-semibold text-phila-gray-500">{msg.is_mine ? 'Vous' : msg.sender_name}</p>
              <p>{msg.body}</p>
              <p className="mt-1 text-[10px] text-phila-gray-400">{msg.created_at}</p>
            </div>
          ))
        )}
        <div ref={bottomRef} />
      </div>
      <form onSubmit={handleSubmit} className="flex gap-2">
        <input
          className="input-field flex-1"
          placeholder={placeholder}
          value={body}
          onChange={(e) => setBody(e.target.value)}
          onKeyDown={handleKeyDown}
          disabled={sending}
        />
        <LoadingButton
          type="submit"
          processing={sending}
          loadingText="Envoi…"
          disabled={!body.trim()}
          className="btn btn-accent shrink-0"
        >
          Envoyer
        </LoadingButton>
      </form>
    </div>
  );
}
