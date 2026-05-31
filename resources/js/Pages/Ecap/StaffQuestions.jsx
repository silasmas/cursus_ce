import { Head, Link, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import EcapStaffLayout from '../../Components/Layout/EcapStaffLayout';
import ModuleFilterBar from '../../Components/Ecap/ModuleFilterBar';
import QuestionFeed from '../../Components/Ecap/QuestionFeed';
import { fetchQuestionFeed, navigateMentionHref } from '../../lib/ecapQuestionFeed';

/**
 * Fil Q&R acteurs ECAP.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function StaffQuestions({
  roles = {},
  pendingCount = 0,
  visibilityNotice,
  courseModules = [],
  mentionCatalog = {},
  mentionUsers = [],
  hashTags = [],
  activeModuleId: initialModuleId,
  activeAddresseeId: initialAddresseeId,
  activeAuthorId: initialAuthorId,
  feedUrl,
  posts: initialPosts = [],
}) {
  const { flash } = usePage().props;
  const roleLabels = Object.values(roles).join(', ') || 'Acteur ECAP';

  const [posts, setPosts] = useState(initialPosts);
  const [activeModuleId, setActiveModuleId] = useState(initialModuleId ?? null);
  const [activeAddresseeId, setActiveAddresseeId] = useState(initialAddresseeId ?? null);
  const [activeAuthorId, setActiveAuthorId] = useState(initialAuthorId ?? null);
  const [feedLoading, setFeedLoading] = useState(false);

  const loadFeed = useCallback(
    async (filters) => {
      if (!feedUrl) {
        return;
      }

      setFeedLoading(true);

      try {
        const nextPosts = await fetchQuestionFeed(feedUrl, filters);
        setPosts(nextPosts);
      } finally {
        setFeedLoading(false);
      }
    },
    [feedUrl],
  );

  useEffect(() => {
    setPosts(initialPosts);
    setActiveModuleId(initialModuleId ?? null);
    setActiveAddresseeId(initialAddresseeId ?? null);
    setActiveAuthorId(initialAuthorId ?? null);
  }, [initialPosts, initialModuleId, initialAddresseeId, initialAuthorId]);

  useEffect(() => {
    if (!feedUrl) {
      return undefined;
    }

    const interval = window.setInterval(() => {
      loadFeed({ module: activeModuleId, addressee: activeAddresseeId, author: activeAuthorId });
    }, 12000);

    return () => window.clearInterval(interval);
  }, [feedUrl, activeModuleId, activeAddresseeId, activeAuthorId, loadFeed]);

  const applyFilters = (filters) => {
    const module = filters.module ?? null;
    const addressee = filters.addressee ?? null;
    const author = filters.author ?? null;

    setActiveModuleId(module);
    setActiveAddresseeId(addressee);
    setActiveAuthorId(author);

    const params = new URLSearchParams(window.location.search);
    if (module) {
      params.set('module', String(module));
    } else {
      params.delete('module');
    }
    if (addressee) {
      params.set('addressee', String(addressee));
    } else {
      params.delete('addressee');
    }
    if (author) {
      params.set('author', String(author));
    } else {
      params.delete('author');
    }

    const query = params.toString();
    window.history.replaceState({}, '', query ? `?${query}` : window.location.pathname);

    loadFeed({ module, addressee, author });
  };

  const handleMentionNavigate = (href) => {
    navigateMentionHref(href, { router, applyFilters });
  };

  return (
    <EcapStaffLayout active="questions">
      <Head title="Questions ECAP — Acteurs" />

      <div className="bg-[#f0f2f5]">
        <div className="mx-auto max-w-2xl px-4 py-6">
          <Link href="/mon-espace" className="text-sm text-phila-orange hover:underline">
            ← Espace fidèle
          </Link>
          <h1 className="mt-2 font-display text-2xl font-bold text-phila-black">Questions par cours</h1>
          <p className="text-sm text-phila-gray-600">Rôles : {roleLabels}</p>
          {pendingCount > 0 && (
            <p className="mt-2 inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900">
              {pendingCount} question(s) vous attendent
            </p>
          )}

          {flash?.status && (
            <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
              {flash.status}
            </div>
          )}

          <p className="mt-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-900">
            {visibilityNotice}
          </p>

          <ModuleFilterBar
            courseModules={courseModules}
            activeModuleId={activeModuleId}
            loading={feedLoading}
            onSelect={(moduleId) => applyFilters({ module: moduleId, addressee: activeAddresseeId, author: activeAuthorId })}
          />

          <QuestionFeed
            posts={posts}
            replyUrlPrefix="/ecap/acteurs/questions"
            updateReplyUrlPrefix="/ecap/acteurs/questions/reponses"
            mentionCatalog={mentionCatalog}
            mentionUsers={mentionUsers}
            hashTags={hashTags}
            onMentionNavigate={handleMentionNavigate}
            onFeedRefresh={(nextPosts) => {
              if (Array.isArray(nextPosts)) {
                setPosts(nextPosts);
                return;
              }

              loadFeed({ module: activeModuleId, addressee: activeAddresseeId, author: activeAuthorId });
            }}
          />
        </div>
      </div>
    </EcapStaffLayout>
  );
}
