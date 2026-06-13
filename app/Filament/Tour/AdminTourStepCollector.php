<?php

namespace App\Filament\Tour;

use App\Filament\Support\FilamentConfig;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Filament\Resources\Resource;
use UnitEnum;

/**
 * Collecte les étapes de visite guidée selon l'ordre hiérarchique du menu Filament.
 */
class AdminTourStepCollector
{
  /**
   * Ordre des groupes de navigation (identique au panneau admin).
   *
   * @var list<string>
   */
  private const NAVIGATION_GROUPS = [
    'Administration',
    'ECAP',
    'Gestion des cursus',
    'Contenu pédagogique',
    'Apprenants',
    'Évaluations',
    'Progression',
    'Mentorat',
    'Certifications',
    'Prière',
    'Système',
  ];

  /**
   * Libellés français des boutons Shepherd.
   *
   * @return list<array{text: string, action: string, secondary: bool}>
   */
  public static function defaultButtons(): array
  {
    return [
      [
        'text' => __('admin-tour.buttons.previous'),
        'action' => 'back',
        'secondary' => true,
      ],
      [
        'text' => __('admin-tour.buttons.next'),
        'action' => 'next',
        'secondary' => false,
      ],
    ];
  }

  /**
   * Boutons de l'écran d'accueil.
   *
   * @return list<array{text: string, action: string, secondary: bool}>
   */
  public static function welcomeButtons(): array
  {
    return [
      [
        'text' => __('admin-tour.welcome.buttons.skip'),
        'action' => 'cancel',
        'secondary' => true,
      ],
      [
        'text' => __('admin-tour.welcome.buttons.start'),
        'action' => 'next',
        'secondary' => false,
      ],
    ];
  }

  /**
   * Boutons de l'écran de fin.
   *
   * @return list<array{text: string, action: string, secondary: bool}>
   */
  public static function finishButtons(): array
  {
    return [
      [
        'text' => __('admin-tour.finish.buttons.back'),
        'action' => 'back',
        'secondary' => true,
      ],
      [
        'text' => __('admin-tour.finish.buttons.finish'),
        'action' => 'complete',
        'secondary' => false,
      ],
    ];
  }

  /**
   * Étape d'accueil localisée.
   *
   * @return array<string, mixed>
   */
  public static function welcomeStep(): array
  {
    return [
      'id' => 'welcome',
      'title' => __('admin-tour.welcome.title'),
      'text' => __('admin-tour.welcome.text'),
      'position' => 'center',
      'buttons' => self::welcomeButtons(),
    ];
  }

  /**
   * Étape de clôture localisée.
   *
   * @return array<string, mixed>
   */
  public static function finishStep(): array
  {
    return [
      'id' => 'finish',
      'title' => __('admin-tour.finish.title'),
      'text' => __('admin-tour.finish.text'),
      'position' => 'center',
      'buttons' => self::finishButtons(),
    ];
  }

  /**
   * Étapes dynamiques triées selon le menu latéral.
   *
   * @return list<array<string, mixed>>
   */
  public static function collectSteps(): array
  {
    $panel = Filament::getCurrentPanel();

    if ($panel === null) {
      return [];
    }

    $steps = [];
    $sort = 0;

    if (Dashboard::shouldRegisterNavigation()) {
      $dashboardHelp = config('filament_admin_help.dashboard');

      $steps[] = self::buildStep(
        id: 'dashboard',
        title: Dashboard::getNavigationLabel(),
        text: filled($dashboardHelp)
          ? strip_tags((string) $dashboardHelp)
          : 'Tableau de bord et accès rapide aux indicateurs clés.',
        sort: $sort++,
      );
    }

    $resourcesByGroup = self::groupResources($panel->getResources());

    foreach (self::NAVIGATION_GROUPS as $group) {
      foreach ($resourcesByGroup[$group] ?? [] as $resource) {
        $steps[] = self::buildStep(
          id: self::stepIdFor($resource),
          title: (string) $resource::getNavigationLabel(),
          text: self::descriptionFor($resource),
          sort: $sort++,
        );
      }
    }

    foreach ($resourcesByGroup['_other'] ?? [] as $resource) {
      $steps[] = self::buildStep(
        id: self::stepIdFor($resource),
        title: (string) $resource::getNavigationLabel(),
        text: self::descriptionFor($resource),
        sort: $sort++,
      );
    }

    return array_map(
      static fn (array $step): array => array_merge($step, [
        'buttons' => self::defaultButtons(),
      ]),
      $steps,
    );
  }

  /**
   * Correspondance id d'étape → libellé menu pour data-tour.
   *
   * @return array<string, string>
   */
  public static function navigationMap(): array
  {
    $map = [];

    if (Dashboard::shouldRegisterNavigation()) {
      $map['dashboard'] = Dashboard::getNavigationLabel();
    }

    foreach (self::collectSteps() as $step) {
      if ($step['id'] === 'welcome' || $step['id'] === 'finish') {
        continue;
      }

      $map[$step['id']] = $step['title'];
    }

    return $map;
  }

  /**
   * Regroupe les ressources par groupe de navigation.
   *
   * @param  list<class-string<Resource>>  $resources
   * @return array<string, list<class-string<Resource>>>
   */
  private static function groupResources(array $resources): array
  {
    $grouped = [];

    foreach ($resources as $resource) {
      if (! self::shouldIncludeResource($resource)) {
        continue;
      }

      $group = self::normalizeGroup($resource::getNavigationGroup());

      $grouped[$group][] = $resource;
    }

    foreach ($grouped as $group => $items) {
      usort($items, static function (string $a, string $b): int {
        $sortA = $a::getNavigationSort() ?? 999;
        $sortB = $b::getNavigationSort() ?? 999;

        if ($sortA === $sortB) {
          return strcmp((string) $a::getNavigationLabel(), (string) $b::getNavigationLabel());
        }

        return $sortA <=> $sortB;
      });

      $grouped[$group] = $items;
    }

    return $grouped;
  }

  /**
   * Indique si la ressource apparaît dans le menu.
   *
   * @param  class-string<Resource>  $resource
   */
  private static function shouldIncludeResource(string $resource): bool
  {
    if (! is_subclass_of($resource, Resource::class)) {
      return false;
    }

    if (! $resource::shouldRegisterNavigation()) {
      return false;
    }

    $label = $resource::getNavigationLabel();

    return filled($label);
  }

  /**
   * Normalise le nom du groupe de navigation.
   */
  private static function normalizeGroup(string|UnitEnum|null $group): string
  {
    if ($group instanceof UnitEnum) {
      $group = $group->name;
    }

    $group = (string) $group;

    if ($group === '') {
      return '_other';
    }

    if (! in_array($group, self::NAVIGATION_GROUPS, true)) {
      return '_other';
    }

    return $group;
  }

  /**
   * Identifiant stable pour data-tour.
   *
   * @param  class-string<Resource>  $resource
   */
  private static function stepIdFor(string $resource): string
  {
    $slug = $resource::getSlug();

    if (filled($slug)) {
      return (string) $slug;
    }

    return str(class_basename($resource))
      ->before('Resource')
      ->kebab()
      ->toString();
  }

  /**
   * Description courte d'une ressource admin.
   *
   * @param  class-string<Resource>  $resource
   */
  private static function descriptionFor(string $resource): string
  {
    $stepId = self::stepIdFor($resource);
    $customStep = __('admin-tour.steps.'.$stepId);

    if ($customStep !== 'admin-tour.steps.'.$stepId) {
      return $customStep;
    }

    $help = FilamentConfig::resourceAdminHelp($resource);
    $tooltip = $help['navigation_tooltip'] ?? null;

    if (filled($tooltip)) {
      return strip_tags((string) $tooltip);
    }

    $plural = $resource::getPluralModelLabel();
    $singular = $resource::getModelLabel();

    if ($plural !== $singular) {
      return "Gérez les entrées « {$plural} » ({$singular}).";
    }

    return "Accédez à la section « {$plural} ».";
  }

  /**
   * Construit une étape attachée au menu latéral (à droite, sans navigation).
   *
   * @return array<string, mixed>
   */
  private static function buildStep(string $id, string $title, string $text, int $sort): array
  {
    return [
      'id' => $id,
      'title' => $title,
      'text' => $text,
      'attachTo' => '[data-tour="'.$id.'"]',
      'position' => 'right',
      'sort' => $sort,
    ];
  }
}
