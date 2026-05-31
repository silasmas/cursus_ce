<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Public\LegalDocumentController;
use App\Http\Controllers\Mentor\AppointmentController as MentorAppointmentController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\MenteeController as MentorMenteeController;
use App\Http\Controllers\MentorChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Student\AssessmentController;
use App\Http\Controllers\Student\AssignmentController;
use App\Http\Controllers\Student\ChapterController;
use App\Http\Controllers\Ecap\EcapPrivateChatController;
use App\Http\Controllers\Ecap\StaffMessagesController;
use App\Http\Controllers\Ecap\StaffMeditationController;
use App\Http\Controllers\Student\EcapMemberMessagesController;
use App\Http\Controllers\Ecap\StaffQuestionController;
use App\Http\Controllers\Ecap\StaffQuizGradingController;
use App\Http\Controllers\Ecap\StaffTpController;
use App\Http\Controllers\Student\EcapCalendarController;
use App\Http\Controllers\Student\EcapMeditationController;
use App\Http\Controllers\Student\PortalSearchController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\QuizHistoryController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\MentorController;
use App\Http\Controllers\Student\VacationQuestionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/documents-legaux/{slug}', [LegalDocumentController::class, 'show'])->name('legal-documents.show');
Route::get('/documents-legaux/{slug}/telecharger', [LegalDocumentController::class, 'download'])->name('legal-documents.download');

Route::middleware('guest:member')->group(function () {
  Route::get('/connexion', [LoginController::class, 'create'])->name('login');
  Route::post('/connexion/otp', [LoginController::class, 'sendOtp'])->name('login.otp.send');
  Route::post('/connexion/verifier', [LoginController::class, 'verifyOtp'])->name('login.otp.verify');
  Route::post('/connexion/renvoyer', [LoginController::class, 'resendOtp'])->name('login.otp.resend');
  Route::get('/connexion/changer-email', [LoginController::class, 'resetEmail'])->name('login.reset');

  Route::get('/inscription', [RegisterController::class, 'create'])->name('register');
  Route::post('/inscription/etape/{step}', [RegisterController::class, 'storeStep'])->name('register.step');
});

Route::middleware('auth:member')->group(function () {
  Route::get('/mon-espace', [DashboardController::class, 'index'])->name('dashboard');
  Route::get('/mon-espace/recherche', [PortalSearchController::class, 'index'])->name('portal.search');
  Route::get('/mon-espace/profil', [ProfileController::class, 'show'])->name('profile.show');
  Route::match(['put', 'post'], '/mon-espace/profil', [ProfileController::class, 'update'])->name('profile.update');
  Route::get('/mon-espace/membres/{user}', [ProfileController::class, 'showMember'])->name('members.show');
  Route::get('/mon-espace/cours/{chapter}', [ChapterController::class, 'show'])->name('chapter.show');
  Route::post('/mon-espace/cours/{chapter}/terminer', [ChapterController::class, 'complete'])->name('chapter.complete');

  Route::get('/mon-espace/tests/{assessment}', [AssessmentController::class, 'show'])->name('assessment.show');
  Route::post('/mon-espace/tests/{assessment}/demarrer', [AssessmentController::class, 'start'])->name('assessment.start');
  Route::post('/mon-espace/tests/{assessment}/tenter/{attempt}/soumettre', [AssessmentController::class, 'submit'])->name('assessment.submit');
  Route::get('/mon-espace/tests/{assessment}/resultat/{attempt}', [AssessmentController::class, 'result'])->name('assessment.result');
  Route::get('/mon-espace/mes-quiz', [QuizHistoryController::class, 'index'])->name('quiz.history');

  Route::get('/mon-espace/cours/{chapter}/tp', [AssignmentController::class, 'index'])->name('assignment.index');
  Route::post('/mon-espace/tp/{assessment}/soumettre', [AssignmentController::class, 'store'])->name('assignment.store');

  Route::get('/mon-espace/mentor', [MentorController::class, 'show'])->name('mentor.mentee');
  Route::post('/mon-espace/mentor/message', [MentorChatController::class, 'menteeSend'])->name('mentor.message');
  Route::post('/mon-espace/mentor/rendez-vous/{appointment}/reponse', [MentorController::class, 'respondToAppointment'])->name('mentor.appointment.respond');
  Route::post('/mon-espace/mentor/avis', [MentorController::class, 'submitFeedback'])->name('mentor.feedback');
  Route::post('/mon-espace/mentor/avis-cloture', [MentorController::class, 'submitClosureFeedback'])->name('mentor.closure-feedback');
  Route::get('/mon-espace/mentor/chat', [MentorChatController::class, 'menteePoll'])->name('mentor.chat.poll');

  Route::get('/mon-espace/ecap/questions', [VacationQuestionController::class, 'index'])->name('ecap.questions.index');
  Route::get('/mon-espace/ecap/questions/feed', [VacationQuestionController::class, 'feed'])->name('ecap.questions.feed');
  Route::post('/mon-espace/ecap/questions', [VacationQuestionController::class, 'store'])->name('ecap.questions.store');
  Route::post('/mon-espace/ecap/questions/reponses/{reply}/like', [VacationQuestionController::class, 'toggleLike'])->name('ecap.questions.like');
  Route::get('/mon-espace/ecap/calendrier', [EcapCalendarController::class, 'index'])->name('ecap.calendar.index');
  Route::get('/mon-espace/ecap/meditation', [EcapMeditationController::class, 'index'])->name('ecap.meditation.index');
  Route::post('/mon-espace/ecap/meditation/{template}', [EcapMeditationController::class, 'submit'])->name('ecap.meditation.submit');
  Route::get('/mon-espace/ecap/messages', [EcapMemberMessagesController::class, 'index'])->name('ecap.member.messages');
  Route::get('/mon-espace/ecap/chat/messages', [EcapPrivateChatController::class, 'index'])->name('ecap.chat.messages');
  Route::get('/mon-espace/ecap/chat/unread', [EcapPrivateChatController::class, 'unread'])->name('ecap.chat.unread');
  Route::post('/mon-espace/ecap/chat/lu', [EcapPrivateChatController::class, 'markAllRead'])->name('ecap.chat.mark-all-read');
  Route::post('/mon-espace/ecap/chat/messages', [EcapPrivateChatController::class, 'store'])->name('ecap.chat.send');

  Route::get('/mon-espace/notifications', [NotificationController::class, 'index'])->name('notifications.index');
  Route::post('/mon-espace/notifications/{notification}/lu', [NotificationController::class, 'markRead'])->name('notifications.read');
  Route::post('/mon-espace/notifications/tout-lu', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

  Route::middleware('ecap.staff')->prefix('ecap/acteurs')->name('ecap.staff.')->group(function () {
    Route::get('/questions', [StaffQuestionController::class, 'index'])->name('questions.index');
    Route::get('/questions/feed', [StaffQuestionController::class, 'feed'])->name('questions.feed');
    Route::post('/questions/{question}/reponses', [StaffQuestionController::class, 'reply'])->name('questions.reply');
    Route::patch('/questions/reponses/{reply}', [StaffQuestionController::class, 'updateReply'])->name('questions.reply.update');
    Route::post('/questions/reponses/{reply}/like', [StaffQuestionController::class, 'toggleLike'])->name('questions.like');
    Route::get('/tp', [StaffTpController::class, 'teacherIndex'])->name('tps.index');
    Route::post('/tp', [StaffTpController::class, 'store'])->name('tps.store');
    Route::get('/corrections-tp', [StaffTpController::class, 'supervisorIndex'])->name('tp-corrections.index');
    Route::post('/corrections-tp/{submission}', [StaffTpController::class, 'grade'])->name('tp-corrections.grade');
    Route::get('/corrections-quiz', [StaffQuizGradingController::class, 'index'])->name('quiz-grading.index');
    Route::get('/corrections-quiz/{attempt}', [StaffQuizGradingController::class, 'show'])->name('quiz-grading.show');
    Route::post('/corrections-quiz/{attempt}', [StaffQuizGradingController::class, 'grade'])->name('quiz-grading.grade');
    Route::patch('/corrections-quiz/{attempt}', [StaffQuizGradingController::class, 'updateGraded'])->name('quiz-grading.update');
    Route::post('/corrections-quiz/{attempt}/avis', [StaffQuizGradingController::class, 'storeComment'])->name('quiz-grading.comment');
    Route::post('/corrections-quiz/{attempt}/unlock', [StaffQuizGradingController::class, 'releaseLock'])->name('quiz-grading.unlock');
    Route::get('/messages', [StaffMessagesController::class, 'index'])->name('messages.index');
    Route::get('/meditation', [StaffMeditationController::class, 'index'])->name('meditation.index');
    Route::post('/meditation/modeles', [StaffMeditationController::class, 'storeTemplate'])->name('meditation.template.store');
    Route::post('/meditation/remises/{submission}', [StaffMeditationController::class, 'review'])->name('meditation.review');
  });

  Route::middleware('mentor')->prefix('mentor')->name('mentor.')->group(function () {
    Route::get('/', [MentorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/mentores', [\App\Http\Controllers\Mentor\MenteeListController::class, 'index'])->name('mentees.index');
    Route::get('/formulaires', [\App\Http\Controllers\Mentor\FormsHubController::class, 'index'])->name('forms.index');
    Route::post('/tp', [\App\Http\Controllers\Mentor\TpSubmissionController::class, 'store'])->name('tp.store');
    Route::post('/accompagnement/cloturer', [\App\Http\Controllers\Mentor\AssignmentClosureController::class, 'store'])->name('closure.store');
    Route::post('/rendez-vous', [MentorAppointmentController::class, 'store'])->name('appointments.store');
    Route::patch('/rendez-vous/{appointment}', [MentorAppointmentController::class, 'update'])->name('appointments.update');
    Route::get('/soumissions', [\App\Http\Controllers\Mentor\SubmissionController::class, 'index'])->name('submissions.index');
    Route::post('/soumissions/{submission}/valider', [\App\Http\Controllers\Mentor\SubmissionController::class, 'review'])->name('submissions.review');
    Route::get('/mentore/{assignment}', [MentorMenteeController::class, 'show'])->name('mentee.show');
    Route::post('/mentore/{assignment}/message', [MentorChatController::class, 'mentorSend'])->name('mentee.message');
    Route::post('/mentore/{assignment}/tp', [MentorMenteeController::class, 'submitTp'])->name('mentee.tp');
    Route::patch('/mentore/{assignment}/tp/{submission}', [MentorMenteeController::class, 'updateTp'])->name('mentee.tp.update');
    Route::post('/mentore/{assignment}/rendez-vous', [MentorAppointmentController::class, 'storeForMentee'])->name('mentee.appointment');
    Route::get('/mentore/{assignment}/chat', [MentorChatController::class, 'mentorPoll'])->name('mentee.chat.poll');
  });

  Route::post('/deconnexion', [LoginController::class, 'destroy'])->name('logout');
});
