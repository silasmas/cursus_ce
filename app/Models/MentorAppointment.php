<?php

namespace App\Models;

use App\Enums\AppointmentChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rendez-vous en ligne programmé entre un mentor et un mentoré.
 */
class MentorAppointment extends Model
{
  protected $fillable = [
    'mentor_id',
    'mentor_assignment_id',
    'scheduled_at',
    'channel',
    'meeting_url',
    'notes',
    'status',
    'mentee_response',
    'proposed_reschedule_at',
    'response_note',
    'responded_at',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'scheduled_at' => 'datetime',
      'proposed_reschedule_at' => 'datetime',
      'responded_at' => 'datetime',
      'channel' => AppointmentChannel::class,
      'mentee_response' => \App\Enums\AppointmentMenteeResponse::class,
    ];
  }

  /**
   * Mentor ayant programmé le rendez-vous.
   */
  public function mentor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'mentor_id');
  }

  /**
   * Assignation mentorat concernée.
   */
  public function mentorAssignment(): BelongsTo
  {
    return $this->belongsTo(MentorAssignment::class);
  }
}
