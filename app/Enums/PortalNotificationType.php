<?php

namespace App\Enums;

/**
 * Types de notifications du portail fidèle / mentor.
 */
enum PortalNotificationType: string
{
  case MentorMessage = 'mentor_message';
  case MenteeMessage = 'mentee_message';
  case AdminMessage = 'admin_message';
  case MeetingReminder = 'meeting_reminder';
  case LevelUnlocked = 'level_unlocked';
  case MentorApproval = 'mentor_approval';
  case MentorRejection = 'mentor_rejection';
  case TpPending = 'tp_pending';
  case ReportUnlocked = 'report_unlocked';
  case EcapQuestionReply = 'ecap_question_reply';
  case EcapQuestionEscalation = 'ecap_question_escalation';
  case QuizPendingGrading = 'quiz_pending_grading';
  case QuizGraded = 'quiz_graded';
}
