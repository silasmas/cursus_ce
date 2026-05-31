import { Head, Link, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import AppLayout from '../../Components/Layout/AppLayout';
import MentionComposer from '../../Components/Ecap/MentionComposer';
import ModuleFilterBar from '../../Components/Ecap/ModuleFilterBar';
import QuestionFeed from '../../Components/Ecap/QuestionFeed';
import { fetchQuestionFeed, navigateMentionHref } from '../../lib/ecapQuestionFeed';

/**
 * Résout le payload avec @tous par défaut.
 *
 * @param {Object} payload Données formulaire
 * @param {Array} teachers Enseignants
 * @param {Array} courseModules Modules
 * @returns {Object}
 */
function resolvePayload(payload, teachers, courseModules) {
  let courseModuleId = payload.course_module_id;
  let addressAllTeachers = payload.address_all_teachers ?? true;
  let addressedToUserId = payload.addressed_to_user_id;

  if (!courseModuleId && payload.body) {
    const module = courseModules.find((item) => payload.body.toLowerCase().includes(item.tag.toLowerCase()));
    if (module) {
      courseModuleId = module.id;
    }
  }

  if (!addressAllTeachers && !addressedToUserId && payload.body) {
    if (/@tous\b/i.test(payload.body)) {
      addressAllTeachers = true;
    } else {
      const mentionMatch = payload.body.match(/@([\wÀ-ÿ_-]+)/iu);
      if (mentionMatch) {
        const handle = `@${mentionMatch[1]}`;
        const teacher = teachers.find((item) => item.mention.toLowerCase() === handle.toLowerCase());
        if (teacher) {
          addressedToUserId = teacher.id;
          addressAllTeachers = false;
        }
      }
    }
  }

  if (!addressedToUserId) {
    addressAllTeachers = true;
  }

  return {
    course_module_id: courseModuleId,
    body: payload.body,
    address_all_teachers: addressAllTeachers,
    addressed_to_user_id: addressAllTeachers ? null : addressedToUserId,
  };
}

/**
 * Fil Q&R ECAP fidèle.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function VacationQuestions({
  hasEcapSession,
  sessionName,
  visibilityNotice,
  courseModules = [],
  teachers = [],
  mentionCatalog = {},
  mentionUsers = [],
  hashTags = [],
  activeModuleId: initialModuleId,
  activeAddresseeId: initialAddresseeId,
  activeAuthorId: initialAuthorId,
  feedUrl,
  posts: initialPosts = [],
}) {
  const { flash, errors } = usePage().props;
  const [posts, setPosts] = useState(initialPosts);
  const [activeModuleId, setActiveModuleId] = useState(initialModuleId ?? null);
  const [activeAddresseeId, setActiveAddresseeId] = useState(initialAddresseeId ?? null);
  const [activeAuthorId, setActiveAuthorId] = useState(initialAuthorId ?? null);
  const [feedLoading, setFeedLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [composerKey, setComposerKey] = useState(0);

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
    const params = new URLSearchParams(window.location.search);
    const questionId = params.get('question');

    if (!questionId) {
      return;
    }

    window.setTimeout(() => {
      const element = document.getElementById(`question-${questionId}`);

      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        element.classList.add('ring-2', 'ring-phila-orange');
      }
    }, 400);
  }, [posts]);

  useEffect(() => {
    if (!feedUrl || !hasEcapSession) {
      return undefined;
    }

    const interval = window.setInterval(() => {
      loadFeed({ module: activeModuleId, addressee: activeAddresseeId, author: activeAuthorId });
    }, 12000);

    return () => window.clearInterval(interval);
  }, [feedUrl, hasEcapSession, activeModuleId, activeAddresseeId, activeAuthorId, loadFeed]);

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

  const handlePublish = (payload) => {
    const data = resolvePayload(payload, teachers, courseModules);

    if (!data.course_module_id) {
      return;
    }

    setSubmitting(true);

    router.post('/mon-espace/ecap/questions', data, {
      preserveScroll: true,
      onSuccess: () => {
        setComposerKey((value) => value + 1);
        loadFeed({
          module: data.course_module_id,
          addressee: activeAddresseeId,
          author: activeAuthorId,
        });
      },
      onFinish: () => setSubmitting(false),
    });
  };

  return (
    <AppLayout>
      <Head title="Questions ECAP" />

      <div className="min-h-screen bg-[#f0f2f5]">
        <div className="mx-auto max-w-2xl px-4 py-6">
          <div className="mb-4">
            <Link href="/mon-espace" className="text-sm text-phila-orange hover:underline">
              ← Mon espace
            </Link>
            <h1 className="mt-1 font-display text-2xl font-bold text-phila-black">Fil ECAP</h1>
            {sessionName && <p className="text-sm text-phila-gray-600">Session {sessionName}</p>}
          </div>

          {flash?.status && (
            <div className="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
              {flash.status}
            </div>
          )}

          {!hasEcapSession ? (
            <div className="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
              <p className="font-semibold">Session ECAP requise</p>
              <p className="mt-2 text-sm">Inscrivez-vous à une session ECAP active pour utiliser le fil de questions.</p>
            </div>
          ) : (
            <>
              <p className="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-900">
                {visibilityNotice}
              </p>

              <MentionComposer
                key={composerKey}
                teachers={teachers}
                mentionUsers={mentionUsers}
                courseModules={courseModules}
                hashTags={hashTags}
                defaultModuleId={activeModuleId ?? courseModules[0]?.id}
                processing={submitting}
                serverErrors={errors}
                onSubmit={handlePublish}
              />

              <ModuleFilterBar
                courseModules={courseModules}
                activeModuleId={activeModuleId}
                loading={feedLoading}
                onSelect={(moduleId) => applyFilters({ module: moduleId, addressee: activeAddresseeId, author: activeAuthorId })}
              />

              <QuestionFeed
                posts={posts}
                mentionCatalog={mentionCatalog}
                mentionUsers={mentionUsers}
                hashTags={hashTags}
                enableLikes
                likeUrlPrefix="/mon-espace/ecap/questions/reponses"
                onMentionNavigate={handleMentionNavigate}
                onFeedRefresh={(nextPosts) => {
                  if (Array.isArray(nextPosts)) {
                    setPosts(nextPosts);
                    return;
                  }

                  loadFeed({ module: activeModuleId, addressee: activeAddresseeId, author: activeAuthorId });
                }}
              />
            </>
          )}
        </div>
      </div>
    </AppLayout>
  );
}
