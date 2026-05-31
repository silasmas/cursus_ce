import { router } from '@inertiajs/react';
import { useState } from 'react';
import UserAvatar from '../UI/UserAvatar';
import MentionComposer from './MentionComposer';
import RichMentionText from './RichMentionText';
import { patchQuestionReply, postQuestionReply } from '../../lib/ecapQuestionFeed';

/**
 * Zone de réponse officielle avec mentions @ #.
 *
 * @param {Object} props Props
 * @returns {JSX.Element|null}
 */
function AnswerBox({ post, replyUrl, mentionUsers, hashTags, onFeedRefresh }) {
  const [processing, setProcessing] = useState(false);

  if (!post.can_reply) {
    return null;
  }

  const handleReply = async (payload) => {
    setProcessing(true);

    try {
      const nextPosts = await postQuestionReply(replyUrl, { ...payload, reply_type: 'answer' });
      onFeedRefresh?.(nextPosts);
    } finally {
      setProcessing(false);
    }
  };

  return (
    <div className="mt-3 border-t border-phila-gray-100 pt-3">
      <p className="mb-2 text-[10px] font-semibold uppercase text-phila-gray-500">Réponse officielle</p>
      <MentionComposer
        compact
        requireAddressee={false}
        mentionUsers={mentionUsers}
        hashTags={hashTags}
        processing={processing}
        placeholder="Répondre avec @ et #…"
        submitLabel="Publier la réponse"
        onSubmit={handleReply}
      />
    </div>
  );
}

/**
 * Zone d'avis pour les autres acteurs ECAP.
 *
 * @param {Object} props Props
 * @returns {JSX.Element|null}
 */
function CommentBox({ post, replyUrl, mentionUsers, hashTags, onFeedRefresh }) {
  const [processing, setProcessing] = useState(false);

  if (!post.can_comment) {
    return null;
  }

  const primaryAnswerId =
    post.my_answer_id ?? post.replies?.find((item) => item.reply_type === 'answer')?.id ?? null;

  const handleComment = async (payload) => {
    setProcessing(true);

    try {
      const nextPosts = await postQuestionReply(replyUrl, {
        ...payload,
        reply_type: 'comment',
        parent_reply_id: primaryAnswerId,
      });
      onFeedRefresh?.(nextPosts);
    } finally {
      setProcessing(false);
    }
  };

  return (
    <div className="mt-3 border-t border-dashed border-phila-gray-200 pt-3">
      <p className="mb-2 text-[10px] font-semibold uppercase text-phila-gray-500">Ajouter un avis</p>
      <MentionComposer
        compact
        requireAddressee={false}
        mentionUsers={mentionUsers}
        hashTags={hashTags}
        processing={processing}
        placeholder="Partager votre avis ou complément…"
        submitLabel="Publier l'avis"
        onSubmit={handleComment}
      />
    </div>
  );
}

/**
 * Pouce utile sur une réponse (fidèle).
 *
 * @param {Object} props
 * @returns {JSX.Element}
 */
function ReplyLikeButton({ reply, likeUrl, onLikeUpdate }) {
  const [liked, setLiked] = useState(reply.liked_by_me);
  const [count, setCount] = useState(reply.likes_count ?? 0);
  const [loading, setLoading] = useState(false);

  const toggle = async () => {
    setLoading(true);

    try {
      const response = await fetch(likeUrl, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      });

      const data = await response.json();
      setLiked(data.liked);
      setCount(data.likes_count);
      onLikeUpdate?.(reply.id, data);
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      type="button"
      onClick={toggle}
      disabled={loading}
      className={`mt-2 flex items-center gap-1 text-xs font-medium transition ${
        liked ? 'text-phila-orange' : 'text-phila-gray-500 hover:text-phila-orange'
      }`}
    >
      <span aria-hidden>{liked ? '👍' : '👍🏽'}</span>
      <span>{count > 0 ? `${count} utile${count > 1 ? 's' : ''}` : 'Utile'}</span>
    </button>
  );
}

/**
 * Une réponse ou un avis du fil.
 *
 * @param {Object} props
 * @returns {JSX.Element}
 */
function ReplyItem({
  reply,
  mentionCatalog,
  onMentionNavigate,
  enableLikes,
  likeUrlPrefix,
  updateReplyUrlPrefix,
  onFeedRefresh,
}) {
  const [editing, setEditing] = useState(false);
  const [editBody, setEditBody] = useState(reply.body ?? '');
  const [saving, setSaving] = useState(false);

  const isComment = reply.reply_type === 'comment';

  const saveEdit = async () => {
    if (!updateReplyUrlPrefix || reply.id <= 0) {
      return;
    }

    setSaving(true);

    try {
      const nextPosts = await patchQuestionReply(`${updateReplyUrlPrefix}/${reply.id}`, { body: editBody });
      setEditing(false);
      onFeedRefresh?.(nextPosts);
    } finally {
      setSaving(false);
    }
  };

  return (
    <li className={`flex gap-2 ${isComment ? 'ml-4 border-l-2 border-phila-orange/30 pl-3' : ''}`}>
      <UserAvatar
        avatarUrl={reply.author_avatar_url}
        name={reply.author_name}
        sizeClass="h-8 w-8"
        textClass="text-[10px]"
        className={isComment ? 'bg-phila-gray-700 text-white' : 'bg-phila-black text-white'}
      />
      <div className="min-w-0 flex-1 rounded-2xl bg-white px-3 py-2 shadow-sm">
        <div className="flex flex-wrap items-center gap-2">
          <p className="text-xs font-semibold text-phila-black">{reply.author_name}</p>
          <span
            className={`rounded-full px-2 py-0.5 text-[9px] font-bold uppercase ${
              isComment ? 'bg-phila-gray-100 text-phila-gray-700' : 'bg-phila-orange-pale text-phila-orange'
            }`}
          >
            {reply.reply_type_label ?? (isComment ? 'Avis' : 'Réponse')}
          </span>
          <span className="text-[10px] font-normal text-phila-gray-400">{reply.created_at}</span>
          {reply.edited_at && (
            <span className="text-[10px] text-phila-gray-500">· modifié {reply.edited_at}</span>
          )}
        </div>

        {editing ? (
          <div className="mt-2 space-y-2">
            <textarea
              className="w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
              rows={4}
              value={editBody}
              onChange={(event) => setEditBody(event.target.value)}
            />
            <div className="flex gap-2">
              <button type="button" disabled={saving} onClick={saveEdit} className="btn btn-accent px-3 py-1 text-xs">
                {saving ? 'Enregistrement…' : 'Enregistrer'}
              </button>
              <button type="button" onClick={() => setEditing(false)} className="text-xs text-phila-gray-600 hover:underline">
                Annuler
              </button>
            </div>
          </div>
        ) : (
          <div className="mt-1 text-sm text-phila-gray-800">
            <RichMentionText text={reply.body} mentionCatalog={mentionCatalog} onFilterClick={onMentionNavigate} />
          </div>
        )}

        {!editing && reply.can_edit && updateReplyUrlPrefix && (
          <button
            type="button"
            onClick={() => {
              setEditBody(reply.body ?? '');
              setEditing(true);
            }}
            className="mt-2 text-xs font-semibold text-phila-orange hover:underline"
          >
            Modifier ma réponse
          </button>
        )}

        {enableLikes && reply.id > 0 && likeUrlPrefix && (
          <ReplyLikeButton reply={reply} likeUrl={`${likeUrlPrefix}/${reply.id}/like`} />
        )}
      </div>
    </li>
  );
}

/**
 * Fil de publications type Facebook.
 *
 * @param {Object} props Props
 * @returns {JSX.Element}
 */
export default function QuestionFeed({
  posts = [],
  replyUrlPrefix = null,
  updateReplyUrlPrefix = null,
  mentionCatalog = null,
  mentionUsers = [],
  hashTags = [],
  likeUrlPrefix = null,
  enableLikes = false,
  onFeedRefresh = null,
  onMentionNavigate = null,
}) {
  const [expanded, setExpanded] = useState({});

  if (posts.length === 0) {
    return (
      <div className="rounded-2xl border border-dashed border-phila-gray-200 bg-white px-6 py-12 text-center text-sm text-phila-gray-500">
        Aucune question pour ce filtre. Soyez le premier à publier !
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {posts.map((post) => {
        const replies = post.replies ?? [];
        const showAllReplies = expanded[post.id];
        const visibleReplies = showAllReplies ? replies : replies.slice(0, 3);
        const hiddenCount = replies.length - visibleReplies.length;
        const replyUrl = replyUrlPrefix ? `${replyUrlPrefix}/${post.id}/reponses` : null;

        return (
          <article
            id={`question-${post.id}`}
            key={post.id}
            className="overflow-hidden rounded-2xl border border-phila-gray-100 bg-white shadow-sm transition-shadow"
          >
            <header className="flex items-start gap-3 px-4 py-3">
              <UserAvatar
                avatarUrl={post.author_avatar_url}
                name={post.author_name}
                sizeClass="h-10 w-10"
                textClass="text-sm"
                className="bg-phila-orange text-white"
              />
              <div className="min-w-0 flex-1">
                <p className="text-sm font-semibold text-phila-black">{post.author_name}</p>
                <p className="text-xs text-phila-gray-500">
                  {post.created_at}
                  {(post.reply_count ?? replies.length) > 0 && (
                    <span className="ml-2 font-semibold text-phila-orange">
                      · {post.reply_count ?? replies.length} contribution{(post.reply_count ?? replies.length) > 1 ? 's' : ''}
                    </span>
                  )}
                </p>
              </div>
              {post.is_pending && (
                <span className="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-900">
                  En attente
                </span>
              )}
            </header>

            <div className="px-4 pb-3 text-[15px] leading-relaxed text-phila-black">
              <RichMentionText text={post.body} mentionCatalog={mentionCatalog} onFilterClick={onMentionNavigate} />
              <div className="mt-3 space-y-1 rounded-xl bg-phila-gray-50 px-3 py-2 text-xs text-phila-gray-600">
                <p>
                  <span className="font-semibold text-phila-black">Destinataire :</span> {post.addressee_label}
                </p>
                <p>{post.visibility_label}</p>
              </div>
            </div>

            {replies.length > 0 && (
              <div className="border-t border-phila-gray-100 bg-phila-gray-50/80 px-4 py-3">
                {hiddenCount > 0 && !showAllReplies && (
                  <button
                    type="button"
                    className="mb-2 text-xs font-semibold text-phila-orange hover:underline"
                    onClick={() => setExpanded((state) => ({ ...state, [post.id]: true }))}
                  >
                    Voir les {replies.length} contributions
                  </button>
                )}
                <ul className="space-y-3">
                  {visibleReplies.map((reply, index) => (
                    <ReplyItem
                      key={`${post.id}-${reply.id}-${index}`}
                      reply={reply}
                      mentionCatalog={mentionCatalog}
                      onMentionNavigate={onMentionNavigate}
                      enableLikes={enableLikes}
                      likeUrlPrefix={likeUrlPrefix}
                      updateReplyUrlPrefix={updateReplyUrlPrefix}
                      onFeedRefresh={onFeedRefresh}
                    />
                  ))}
                </ul>
              </div>
            )}

            {replyUrl && (
              <div className="px-4 pb-4">
                <AnswerBox
                  post={post}
                  replyUrl={replyUrl}
                  mentionUsers={mentionUsers}
                  hashTags={hashTags}
                  onFeedRefresh={onFeedRefresh}
                />
                <CommentBox
                  post={post}
                  replyUrl={replyUrl}
                  mentionUsers={mentionUsers}
                  hashTags={hashTags}
                  onFeedRefresh={onFeedRefresh}
                />
              </div>
            )}
          </article>
        );
      })}
    </div>
  );
}
