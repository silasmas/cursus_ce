<?php

namespace App\Filament\Resources\AssessmentAttempts\Pages;

use App\Filament\Resources\AssessmentAttempts\AssessmentAttemptResource;
use App\Services\Ecap\EcapQuizGradingNotifier;
use App\Services\Student\AssessmentAttemptGradingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

/**
 * Page admin de correction d'une tentative avec verrou collaboratif.
 */
class GradeAssessmentAttempt extends Page
{
  use InteractsWithRecord;

  protected static string $resource = AssessmentAttemptResource::class;

  protected static ?string $navigationLabel = 'Corriger';

  protected static bool $shouldRegisterNavigation = false;

  /**
   * @var array<string, mixed>|null
   */
  public ?array $data = [];

  public bool $canEdit = false;

  /**
   * @return string|Htmlable
   */
  public function getTitle(): string|Htmlable
  {
    return 'Corriger la tentative #'.$this->getRecord()->id;
  }

  /**
   * Charge la tentative et acquiert le verrou de correction.
   */
  public function mount(int|string $record): void
  {
    $this->record = $this->resolveRecord($record);

    $gradingService = app(AssessmentAttemptGradingService::class);
    $admin = auth('admin')->user();

    abort_unless($admin !== null && $gradingService->canUserGrade($admin, $this->getRecord()), 403);

    $lockResult = $gradingService->acquireLock($admin, $this->getRecord());
    $lockInfo = $gradingService->lockInfo($admin, $this->getRecord()->fresh());
    $this->canEdit = $lockInfo['can_edit'] ?? false;

    if (! $lockResult['acquired']) {
      Notification::make()
        ->title('Correction en cours')
        ->body('Cette tentative est corrigée par '.($lockResult['locked_by']['name'] ?? 'un autre correcteur').'.')
        ->warning()
        ->send();
    }

    $this->fillForm();
  }

  /**
   * Schéma principal de la page.
   */
  public function content(Schema $schema): Schema
  {
    return $schema->components([
      $this->getFormContentComponent(),
    ]);
  }

  /**
   * Conteneur du formulaire de correction.
   */
  public function getFormContentComponent(): Form
  {
    return Form::make([EmbeddedSchema::make('form')])
      ->id('grade-form')
      ->livewireSubmitHandler('save')
      ->footer([
        Actions::make([
          Action::make('save')
            ->label('Enregistrer la correction')
            ->submit('save')
            ->visible(fn (): bool => $this->canEdit),
        ]),
      ]);
  }

  /**
   * Champs du formulaire (réponses rédigées).
   */
  public function defaultForm(Schema $schema): Schema
  {
    return $schema
      ->statePath('data')
      ->components([
        Section::make('Informations')
          ->schema([
            TextInput::make('student_name')
              ->label('Fidèle')
              ->disabled(),
            TextInput::make('assessment_title')
              ->label('Quiz')
              ->disabled(),
          ])
          ->columns(2),
        Repeater::make('grades')
          ->label('Réponses rédigées')
          ->schema([
            Hidden::make('answer_id'),
            TextInput::make('stem')
              ->label('Question')
              ->disabled()
              ->columnSpanFull(),
            Textarea::make('answer_text')
              ->label('Réponse du fidèle')
              ->disabled()
              ->columnSpanFull(),
            TextInput::make('max_points')
              ->label('Points max')
              ->disabled(),
            TextInput::make('points_awarded')
              ->label('Points attribués')
              ->numeric()
              ->minValue(0)
              ->required()
              ->disabled(fn (): bool => ! $this->canEdit),
            Textarea::make('grader_feedback')
              ->label('Feedback')
              ->disabled(fn (): bool => ! $this->canEdit)
              ->columnSpanFull(),
          ])
          ->columns(2)
          ->addable(false)
          ->deletable(false)
          ->reorderable(false),
      ]);
  }

  /**
   * Préremplit le formulaire avec les réponses rédigées.
   */
  protected function fillForm(): void
  {
    $attempt = $this->getRecord()->load(['user', 'assessment', 'answers.question']);
    $writtenAnswers = $attempt->answers
      ->filter(fn ($answer) => $answer->question?->type === 'written')
      ->values();

    $this->form->fill([
      'student_name' => $attempt->user?->name,
      'assessment_title' => $attempt->assessment?->title,
      'grades' => $writtenAnswers->map(fn ($answer) => [
        'answer_id' => $answer->id,
        'stem' => $answer->question?->stem,
        'answer_text' => $answer->answer_text,
        'max_points' => (float) ($answer->question?->points ?? 0),
        'points_awarded' => $answer->points_awarded,
        'grader_feedback' => $answer->grader_feedback,
      ])->all(),
    ]);
  }

  /**
   * Enregistre les notes et notifie le fidèle.
   */
  public function save(): void
  {
    $admin = auth('admin')->user();
    $gradingService = app(AssessmentAttemptGradingService::class);
    $notifier = app(EcapQuizGradingNotifier::class);

    $grades = collect($this->form->getState()['grades'] ?? [])
      ->map(fn (array $row) => [
        'answer_id' => (int) $row['answer_id'],
        'points_awarded' => (float) $row['points_awarded'],
        'grader_feedback' => $row['grader_feedback'] ?? null,
      ])
      ->all();

    try {
      $graded = $gradingService->gradeWrittenAnswers($admin, $this->getRecord(), $grades);
      $notifier->notifyStudentGraded($graded);
    } catch (ValidationException $exception) {
      Notification::make()
        ->title('Correction impossible')
        ->body(collect($exception->errors())->flatten()->first())
        ->danger()
        ->send();

      return;
    }

    Notification::make()
      ->title('Correction enregistrée')
      ->body('Le fidèle a été notifié.')
      ->success()
      ->send();

    $this->redirect(AssessmentAttemptResource::getUrl('index'));
  }
}
