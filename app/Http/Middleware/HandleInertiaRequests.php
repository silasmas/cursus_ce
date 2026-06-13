<?php

namespace App\Http\Middleware;

use App\Models\EcapStaffAssignment;
use App\Models\MentorAssignment;
use App\Models\MentorProfile;
use App\Services\Ecap\EcapQuizGradingService;
use App\Services\Ecap\EcapPrivateChatService;
use App\Services\Student\StudentQuizHistoryService;
use App\Services\Ecap\EcapSessionTimelineService;
use App\Services\Ecap\EcapStaffRoleService;
use App\Services\Ecap\VacationQuestionService;
use App\Services\Student\MentorPortalService;
use App\Support\UserPresentation;
use App\Services\Portal\MemberSurveyService;
use App\Services\Portal\PortalNotificationService;
use App\Services\Public\RegistrationAvailabilityService;
use App\Services\Mentor\MentorSettingService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Closure;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * N'applique pas Inertia au panneau Filament (Livewire) pour éviter les conflits de session.
     *
     * @param  Request  $request  Requête HTTP
     * @param  Closure  $next  Suite du pipeline
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin', 'admin/*')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $member = $request->user('member');

        $isMentor = false;
        $isEcapStaff = false;
        $ecapStaffPendingQuestions = 0;
        $ecapStaffPendingQuizGrading = 0;
        $hasEcapSession = false;
        $ecapStaffRoles = ['keys' => [], 'labels' => []];

        if ($member) {
            $isMentor = MentorProfile::query()->where('user_id', $member->id)->exists()
                || MentorAssignment::query()
                    ->where('mentor_id', $member->id)
                    ->where('status', 'active')
                    ->exists();

            $isEcapStaff = EcapStaffAssignment::query()
                ->where('user_id', $member->id)
                ->where('is_active', true)
                ->exists();

            if ($isEcapStaff) {
                $roleService = app(EcapStaffRoleService::class);
                $ecapStaffRoles = [
                    'keys' => $roleService->activeRoleKeys($member),
                    'labels' => $roleService->activeRoleLabels($member),
                    'can_grade_quiz' => $roleService->canGradeQuizzes($member),
                ];
                $ecapStaffPendingQuestions = app(VacationQuestionService::class)
                    ->pendingCountForStaff($member);
                $ecapStaffPendingQuizGrading = app(EcapQuizGradingService::class)
                    ->pendingCountForStaff($member);
            }

            $hasEcapSession = app(VacationQuestionService::class)->studentSession($member) !== null
                || $member->profile?->academic_session_id !== null;
        }

        $mentorPendingSubmissions = 0;
        $studentPendingQuizGrading = 0;
        $isMentee = false;
        $assignedMentor = null;
        $avatarUrl = null;
        $notifications = [];
        $unreadNotifications = 0;

        if ($member) {
            $member->loadMissing('profile');
            $memberPresentation = UserPresentation::for($member);
            $avatarUrl = $memberPresentation['avatar_url'];

            $mentorAssignment = app(MentorPortalService::class)->metamorphoAssignmentForMentee($member);
            $isMentee = $mentorAssignment !== null;
            $assignedMentor = $mentorAssignment
                ? app(MentorPortalService::class)->mentorProfilePayload($mentorAssignment)
                : null;
            $notificationService = app(PortalNotificationService::class);
            $notifications = $notificationService->recentForUser($member, 8);
            $unreadNotifications = $notificationService->unreadCount($member);
            $studentPendingQuizGrading = app(StudentQuizHistoryService::class)
              ->pendingGradingCountForUser($member);
        }

        if ($isMentor && $member) {
            $menteeIds = MentorAssignment::query()
                ->where('mentor_id', $member->id)
                ->where('status', 'active')
                ->pluck('mentee_id');

            $mentorPendingSubmissions = \App\Models\AssignmentSubmission::query()
                ->whereIn('user_id', $menteeIds)
                ->where('mentor_status', 'pending')
                ->whereNotNull('submitted_at')
                ->count();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $member ? [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'avatar_url' => $avatarUrl,
                    'isMentor' => $isMentor,
                    'isMentee' => $isMentee,
                    'assignedMentor' => $assignedMentor,
                    'mentorPendingSubmissions' => $mentorPendingSubmissions,
                    'isEcapStaff' => $isEcapStaff,
                    'ecapStaffPendingQuestions' => $ecapStaffPendingQuestions,
                    'ecapStaffPendingQuizGrading' => $ecapStaffPendingQuizGrading,
                    'studentPendingQuizGrading' => $studentPendingQuizGrading,
                    'hasEcapSession' => $hasEcapSession,
                ] : null,
            ],
            'notifications' => [
                'items' => $notifications,
                'unread_count' => $unreadNotifications,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'app' => [
                'name' => config('app.name', 'PHILA-CE'),
            ],
            'publicRegistration' => Inertia::always(
                $this->publicRegistrationPayload($request),
            ),
            'ecapPrivateChat' => fn () => $member
                ? app(EcapPrivateChatService::class)->payloadForUser($member)
                : null,
            'ecapTimeline' => fn () => $member
                ? app(EcapSessionTimelineService::class)->forUser($member)
                : null,
            'ecapStaffRoles' => $ecapStaffRoles,
            'ecapStaffChat' => fn () => $member && app(EcapPrivateChatService::class)->isStaffChatActor($member)
                ? $this->ecapStaffChatPayload($member)
                : null,
            'analytics' => fn () => $this->analyticsPayload($request),
            'mentorNotifications' => fn () => app(MentorSettingService::class)->frontendNotificationPreferences(),
            'memberSurvey' => fn () => $member
                ? app(MemberSurveyService::class)->promptPayload($member)
                : null,
        ];
    }

    /**
     * Configuration analytics partagée avec le portail Inertia (hors admin).
     *
     * @return array<string, mixed>
     */
    private function analyticsPayload(Request $request): array
    {
        if ($request->is('admin', 'admin/*')) {
            return ['enabled' => false, 'driver' => 'none'];
        }

        $driver = (string) config('analytics.driver', 'none');

        if ($driver === 'plausible' && filled(config('analytics.plausible.domain'))) {
            return [
                'enabled' => true,
                'driver' => 'plausible',
                'plausibleDomain' => config('analytics.plausible.domain'),
                'plausibleScriptUrl' => config('analytics.plausible.script_url'),
            ];
        }

        if ($driver === 'ga' && filled(config('analytics.ga.measurement_id'))) {
            return [
                'enabled' => true,
                'driver' => 'ga',
                'gaMeasurementId' => config('analytics.ga.measurement_id'),
            ];
        }

        return ['enabled' => false, 'driver' => 'none'];
    }

    /**
     * Payload bouton messagerie acteurs ECAP.
     *
     * @return array<string, mixed>|null
     */
    private function ecapStaffChatPayload(\App\Models\User $member): ?array
    {
        $chatService = app(EcapPrivateChatService::class);

        if ($chatService->sessionForChatUser($member) === null) {
            return null;
        }

        return [
            'enabled' => true,
            'inbox_url' => '/ecap/acteurs/messages',
            'unread_url' => '/mon-espace/ecap/chat/unread',
            'unread_count' => $chatService->unreadCountForActor($member),
        ];
    }

    /**
     * Données d'inscription publiques partagées avec toutes les pages Inertia (hors admin).
     *
     * @return array<string, mixed>
     */
    private function publicRegistrationPayload(Request $request): array
    {
        if ($request->is('admin', 'admin/*')) {
            return RegistrationAvailabilityService::disabledPayload('');
        }

        try {
            return app(RegistrationAvailabilityService::class)->publicPayload();
        } catch (QueryException) {
            return RegistrationAvailabilityService::disabledPayload();
        }
    }
}
