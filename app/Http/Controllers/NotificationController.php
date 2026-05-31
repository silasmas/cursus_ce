<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PortalNotification;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des notifications in-app du portail.
 */
class NotificationController extends Controller
{
  /**
   * @param  PortalNotificationService  $notificationService  Service notifications
   */
  public function __construct(
    private readonly PortalNotificationService $notificationService,
  ) {}

  /**
   * Liste JSON des notifications récentes.
   */
  public function index(Request $request): JsonResponse
  {
    $user = $request->user('member');

    return response()->json([
      'unread_count' => $this->notificationService->unreadCount($user),
      'notifications' => $this->notificationService->recentForUser($user),
    ]);
  }

  /**
   * Marque une notification comme lue.
   */
  public function markRead(Request $request, PortalNotification $notification): JsonResponse
  {
    $this->notificationService->markRead($request->user('member'), $notification);

    return response()->json(['ok' => true]);
  }

  /**
   * Marque toutes les notifications comme lues.
   */
  public function markAllRead(Request $request): JsonResponse
  {
    $this->notificationService->markAllRead($request->user('member'));

    return response()->json(['ok' => true]);
  }
}
