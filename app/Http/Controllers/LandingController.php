<?php

namespace App\Http\Controllers;

use App\Services\Ecap\EcapSessionPublicService;
use App\Services\Public\RegistrationAvailabilityService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Page publique de présentation de la plateforme PHILA-CE.
 */
class LandingController extends Controller
{
  /**
   * @param  EcapSessionPublicService  $ecapSessionPublicService  Session ECAP mise en avant
   */
  public function __construct(
    private readonly EcapSessionPublicService $ecapSessionPublicService,
    private readonly RegistrationAvailabilityService $registrationAvailability,
  ) {}

  /**
   * Affiche la page d'accueil publique unique.
   */
  public function index(): Response
  {
    return Inertia::render('Landing', [
      'features' => [
        [
          'title' => 'Formation biblique structurée',
          'description' => 'Parcourez un cursus complet pour exposer votre vie à la Parole de Dieu et marcher dans son appel.',
        ],
        [
          'title' => 'Accompagnement & mentorat',
          'description' => 'Bénéficiez d\'un suivi personnalisé par des mentors pour grandir dans votre foi et votre service.',
        ],
        [
          'title' => 'Progression & certifications',
          'description' => 'Suivez votre avancement, validez vos modules et obtenez vos certificats de formation.',
        ],
        [
          'title' => 'Communauté de prière',
          'description' => 'Participez aux temps de prière et restez connecté à la famille PHILA.',
        ],
      ],
      'ecapSession' => $this->ecapSessionPublicService->featuredSessionPayload(),
      'registration' => $this->registrationAvailability->publicPayload(),
    ]);
  }
}
