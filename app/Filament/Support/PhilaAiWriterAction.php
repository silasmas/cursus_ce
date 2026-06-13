<?php

namespace App\Filament\Support;

use App\Exceptions\AiWriterException;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Colors\Color;
use PdroLucas\FilamentAiWriter\Actions\AiWriterAction;
use PdroLucas\FilamentAiWriter\Contracts\AiProvider;

/**
 * Action IA PHILA-CE — interface en français et erreurs lisibles.
 */
class PhilaAiWriterAction extends AiWriterAction
{
  /**
   * Crée l'action avec libellés français.
   */
  public static function make(?string $name = null): static
  {
    return parent::make($name)
      ->tooltip(__('ai-writer.tooltip'));
  }

  /**
   * Configure la modale, le formulaire et la gestion d'erreurs.
   */
  public function setUp(): void
  {
    parent::setUp();

    $this
      ->color(Color::Fuchsia)
      ->modalHeading(fn (): ?string => $this->silent ? null : __('ai-writer.modal.heading'))
      ->modalDescription(fn (): ?string => $this->silent ? null : __('ai-writer.modal.description'))
      ->modalSubmitAction(
        fn ($action) => $action
          ->color('primary')
          ->label($this->silent ? '' : __('ai-writer.modal.submit')),
      )
      ->form(fn (): array => $this->frenchFormSchema())
      ->action(function (array $data, Get $get, Set $set): void {
        try {
          $this->executeGeneration($data, $get, $set);
        } catch (AiWriterException $exception) {
          $this->notifyFailure($exception);
        }
      });
  }

  /**
   * Schéma du formulaire de la modale en français.
   *
   * @return list<\Filament\Forms\Components\Component>
   */
  protected function frenchFormSchema(): array
  {
    if ($this->silent) {
      return [];
    }

    $fields = [
      Textarea::make('ai_input')
        ->label(__('ai-writer.fields.input_label'))
        ->placeholder(__('ai-writer.fields.input_placeholder'))
        ->helperText(__('ai-writer.fields.input_helper'))
        ->required()
        ->rules(['required', 'string', 'min:3'])
        ->rows(4)
        ->autofocus()
        ->columnSpanFull()
        ->validationMessages([
          'required' => __('ai-writer.validation.input_required'),
          'min' => __('ai-writer.validation.input_min'),
        ]),
    ];

    if ($this->showToneControl) {
      $fields[] = Select::make('ai_tone')
        ->label(__('ai-writer.fields.tone_label'))
        ->options([
          'professional' => __('ai-writer.tones.professional'),
          'casual' => __('ai-writer.tones.casual'),
          'formal' => __('ai-writer.tones.formal'),
          'friendly' => __('ai-writer.tones.friendly'),
          'persuasive' => __('ai-writer.tones.persuasive'),
        ])
        ->placeholder(__('ai-writer.fields.tone_placeholder'))
        ->native(false);
    }

    if ($this->showLengthControl) {
      $fields[] = Select::make('ai_length')
        ->label(__('ai-writer.fields.length_label'))
        ->options([
          'short' => __('ai-writer.lengths.short'),
          'medium' => __('ai-writer.lengths.medium'),
          'long' => __('ai-writer.lengths.long'),
        ])
        ->placeholder(__('ai-writer.fields.length_placeholder'))
        ->native(false);
    }

    if ($this->showEmojiControl) {
      $fields[] = Toggle::make('ai_emojis')
        ->label(__('ai-writer.fields.emojis_label'))
        ->default(false);
    }

    return $fields;
  }

  /**
   * Lance la génération (mode interactif ou silencieux).
   *
   * @param  array<string, mixed>  $data
   */
  protected function executeGeneration(array $data, Get $get, Set $set): void
  {
    if (! $this->runBeforeGenerateHooks()) {
      return;
    }

    if ($this->silent) {
      $this->runSilentSafely($get, $set);

      return;
    }

    $provider = app(AiProvider::class);
    $prompt = $this->buildPromptWithControls($this->aiPrompt, $data);
    $result = $provider->generate($prompt, $data['ai_input'] ?? '');

    if ($this->expectArray) {
      $parsed = $this->parseArrayResult($result, $this->normalizeArrayCase);
      $set($this->targetField, $parsed);
      parent::dispatchEvent($this->targetField, $result);
      $this->notifyGenerationSuccess();

      return;
    }

    $set($this->targetField, trim($result));
    parent::dispatchEvent($this->targetField, $result);
    $this->notifyGenerationSuccess();
  }

  /**
   * Mode silencieux avec messages français et propagation des erreurs IA.
   */
  protected function runSilentSafely(Get $get, Set $set): void
  {
    $missingFields = array_filter($this->contextFields, fn (string $field): bool => blank($get($field)));

    if ($missingFields !== []) {
      Notification::make()
        ->warning()
        ->title(__('ai-writer.missing_context.title'))
        ->body(__('ai-writer.missing_context.body', [
          'fields' => implode(', ', $missingFields),
        ]))
        ->send();

      return;
    }

    parent::runSilent($get, $set);
  }

  /**
   * Notification de succès en français.
   */
  protected function notifyGenerationSuccess(): void
  {
    Notification::make()
      ->success()
      ->title(__('ai-writer.success.title'))
      ->body(__('ai-writer.success.body'))
      ->send();
  }

  /**
   * Notification d'échec lisible pour l'administrateur.
   */
  protected function notifyFailure(AiWriterException $exception): void
  {
    Notification::make()
      ->danger()
      ->title($exception->title)
      ->body($exception->getMessage())
      ->persistent()
      ->send();
  }
}
