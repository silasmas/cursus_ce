<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Programs\ProgramResource;
use App\Models\Program;
use App\Services\Analytics\AdminDashboardStatisticsService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

/**
 * Tableau synthétique des indicateurs par cursus PHILA-CE.
 */
class CursusSummaryTableWidget extends TableWidget
{
  protected static ?int $sort = 2;

  protected int | string | array $columnSpan = 'full';

  /**
   * Cache local des métriques par cursus pour la durée de la requête.
   *
   * @var Collection<int, array<string, mixed>>|null
   */
  private ?Collection $summariesCache = null;

  /**
   * Configure le tableau Filament des cursus.
   */
  public function table(Table $table): Table
  {
    $summaries = $this->summaries()->keyBy('id');

    return $table
      ->heading('Synthèse par cursus')
      ->description('Inscriptions, progression, évaluations et certificats pour chaque parcours actif.')
      ->query(
        Program::query()
          ->where('is_active', true)
          ->orderBy('sort_order'),
      )
      ->paginated(false)
      ->recordUrl(fn (Program $record): string => ProgramResource::getUrl('edit', ['record' => $record]))
      ->columns([
        TextColumn::make('name')
          ->label('Cursus')
          ->searchable()
          ->weight('bold'),
        TextColumn::make('status')
          ->label('Statut')
          ->badge()
          ->state(function (Program $record) use ($summaries): string {
            $row = $summaries->get($record->id);

            if ($row === null) {
              return '—';
            }

            if ($row['is_open']) {
              return 'Ouvert';
            }

            return 'Fermé';
          })
          ->color(fn (string $state): string => $state === 'Ouvert' ? 'success' : 'gray'),
        TextColumn::make('enrollments_active')
          ->label('Inscrits actifs')
          ->alignCenter()
          ->state(fn (Program $record) => $summaries->get($record->id)['enrollments_active'] ?? 0)
          ->numeric(),
        TextColumn::make('access_open')
          ->label('Accès ouverts')
          ->alignCenter()
          ->state(fn (Program $record) => $summaries->get($record->id)['access_open'] ?? 0)
          ->numeric(),
        TextColumn::make('published_chapters')
          ->label('Chapitres publiés')
          ->alignCenter()
          ->state(fn (Program $record) => $summaries->get($record->id)['published_chapters'] ?? 0)
          ->numeric(),
        TextColumn::make('avg_progress_percent')
          ->label('Progression moy.')
          ->alignCenter()
          ->state(fn (Program $record) => ($summaries->get($record->id)['avg_progress_percent'] ?? 0).'%')
          ->color(function (Program $record) use ($summaries): string {
            $value = (float) ($summaries->get($record->id)['avg_progress_percent'] ?? 0);

            if ($value >= 50) {
              return 'success';
            }

            if ($value >= 20) {
              return 'warning';
            }

            return 'gray';
          }),
        TextColumn::make('certificates')
          ->label('Certificats')
          ->alignCenter()
          ->state(fn (Program $record) => $summaries->get($record->id)['certificates'] ?? 0)
          ->numeric(),
        TextColumn::make('assessments_month')
          ->label('Quiz ce mois')
          ->alignCenter()
          ->state(fn (Program $record) => $summaries->get($record->id)['assessments_month'] ?? 0)
          ->numeric(),
        TextColumn::make('tp_submissions_month')
          ->label('TP ce mois')
          ->alignCenter()
          ->state(fn (Program $record) => $summaries->get($record->id)['tp_submissions_month'] ?? 0)
          ->numeric(),
        TextColumn::make('ecap_mode')
          ->label('ECAP en ligne / présentiel')
          ->state(function (Program $record) use ($summaries): string {
            $row = $summaries->get($record->id);

            if ($row === null || $record->slug !== 'ecap') {
              return '—';
            }

            return ($row['ecap_online'] ?? 0).' / '.($row['ecap_presentiel'] ?? 0);
          })
          ->toggleable(),
        TextColumn::make('access_pending_validation')
          ->label('À valider')
          ->alignCenter()
          ->state(fn (Program $record) => $summaries->get($record->id)['access_pending_validation'] ?? 0)
          ->color(fn (Program $record) => ($summaries->get($record->id)['access_pending_validation'] ?? 0) > 0 ? 'warning' : 'gray')
          ->numeric(),
      ]);
  }

  /**
   * Retourne les métriques agrégées par cursus (mise en cache locale).
   *
   * @return Collection<int, array<string, mixed>>
   */
  private function summaries(): Collection
  {
    return $this->summariesCache ??= app(AdminDashboardStatisticsService::class)->programSummaries();
  }
}
