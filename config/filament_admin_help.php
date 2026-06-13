<?php

/**
 * Aide contextuelle admin Filament : info-bulles menu et descriptions de pages.
 */
return [
  'dashboard' => 'Tableau de bord : indicateurs globaux, synthèse par cursus (inscriptions, progression, quiz, TP, certificats) et graphiques de tendance. Cliquez sur une carte ou une ligne pour ouvrir la rubrique concernée.',

  'navigation_groups' => [
    'Administration' => 'Gérez les comptes utilisateurs, les profils détaillés des fidèles et consultez le journal d\'audit pour tracer qui a modifié quoi dans le panneau.',
    'ECAP' => 'Pilotez tout le parcours ECAP : sessions, dates d\'inscription publique, calendrier, vacations, périodes pédagogiques, acteurs (enseignants, superviseurs, modérateurs) et fil de questions par module.',
    'Gestion des cursus' => 'Définissez les cursus PHILA-CE (ECAP, Métamorpho, etc.), leurs paramètres métier et validez les droits d\'accès des fidèles à chaque cursus.',
    'Contenu pédagogique' => 'Construisez les cours, modules, chapitres, blocs multimédias et la bibliothèque de fichiers utilisés dans les parcours en ligne.',
    'Apprenants' => 'Suivez les inscriptions aux sessions, les groupes de vacation et les dossiers académiques ECAP des fidèles.',
    'Évaluations' => 'Administrez tests, banque de questions, passations, réponses et travaux pratiques remis par les fidèles ou leurs mentors.',
    'Progression' => 'Visualisez l\'avancement chapitre par chapitre et bloc par bloc pour débloquer ou diagnostiquer un parcours.',
    'Mentorat' => 'Gérez le cursus Métamorpho : profils mentors, binômes, feedbacks, décisions, rapports et soutenances.',
    'Certifications' => 'Délivrez les certificats aux fidèles et personnalisez les modèles graphiques utilisés pour l\'impression.',
    'Prière' => 'Organisez les sessions de prière communautaires et la liste des participants inscrits.',
    'Système' => 'Exports de données, file d\'e-mails sortants et rapports archivés pour la conformité et le support.',
  ],

  'resources' => [
    'App.Filament.Resources.Users.UserResource' => [
      'navigation_tooltip' => 'Tous les comptes de la plateforme (fidèles, enseignants ECAP, mentors, administrateurs). Créez un compte manuellement ou retrouvez un utilisateur pour réinitialiser son accès.',
      'pages' => [
        'list' => 'Liste des comptes utilisateurs. Recherchez, filtrez et ouvrez un profil pour le modifier.',
        'create' => 'Créez un compte utilisateur manuellement (rare — l\'inscription publique est le flux normal).',
        'edit' => 'Modifiez les informations du compte et les rôles associés.',
      ],
    ],
    'App.Filament.Resources.Profiles.ProfileResource' => [
      'navigation_tooltip' => 'Fiche complète du fidèle : identité, coordonnées, parcours spirituel, session ECAP, vacation et groupe. Indispensable pour corriger une inscription ou changer de vacation.',
      'pages' => [
        'list' => 'Consultez les profils fidèles complétés lors de l\'inscription.',
        'create' => 'Créez un profil fidèle manuellement.',
        'edit' => 'Modifiez le profil d\'un fidèle (coordonnées, parcours, vacation ECAP).',
      ],
    ],
    'App.Filament.Resources.LoginEvents.LoginEventResource' => [
      'navigation_tooltip' => 'Statistiques de connexion par appareil (mobile, tablette, ordinateur) pour orienter la décision app native vs web responsive.',
      'pages' => [
        'list' => 'Historique des connexions au portail fidèle et à l\'admin. Filtrez par appareil pour analyser l\'usage mobile.',
      ],
    ],
    'App.Filament.Resources.AuditLogs.AuditLogResource' => [
      'navigation_tooltip' => 'Traçabilité des actions sensibles dans l’admin : qui a créé, modifié ou supprimé quoi, et à quelle date. Utile en cas d’incident ou de litige.',
      'pages' => [
        'list' => 'Journal d\'audit : qui a fait quoi et quand dans le panneau admin.',
        'create' => 'Les entrées d\'audit sont créées automatiquement.',
        'edit' => 'Les journaux d\'audit ne se modifient pas.',
      ],
    ],
    'App.Filament.Resources.Programs.ProgramResource' => [
      'navigation_tooltip' => 'Liste des cursus PHILA-CE (ECAP, Métamorpho, etc.). Chaque programme a un slug unique utilisé par le portail fidèle pour afficher ou masquer un parcours.',
      'pages' => [
        'list' => 'Gérez les cursus disponibles et leurs règles d\'accès.',
        'create' => 'Ajoutez un nouveau cursus (slug unique).',
        'edit' => 'Modifiez le nom, la description et les règles d\'ouverture du cursus.',
      ],
    ],
    'App.Filament.Resources.ProgramSettings.ProgramSettingResource' => [
      'navigation_tooltip' => 'Options techniques et métier par programme (seuils, flags, textes). À utiliser quand une règle ne se configure pas dans la session ou le cours.',
      'pages' => [
        'list' => 'Paramètres spécifiques liés aux programmes.',
        'create' => 'Définissez un nouveau paramètre programme.',
        'edit' => 'Modifiez la valeur d\'un paramètre programme.',
      ],
    ],
    'App.Filament.Resources.ProgramAccesses.ProgramAccessResource' => [
      'navigation_tooltip' => 'Validez ou refusez l’accès d’un fidèle à un cursus (ex. Métamorpho déjà suivi). Tant que l’accès n’est pas validé, le cursus reste verrouillé dans Mon Espace.',
      'pages' => [
        'list' => 'Consultez les accès fidèles × cursus. Utilisez la légende au-dessus du tableau pour comprendre chaque interrupteur d\'état.',
      ],
    ],
    'App.Filament.Resources.AcademicSessions.AcademicSessionResource' => [
      'navigation_tooltip' => 'Cœur du pilotage ECAP : activez la session, ouvrez/fermez les inscriptions publiques (/inscription), planifiez périodes, vacations et modules au calendrier.',
      'pages' => [
        'list' => 'Liste des sessions ECAP. Cliquez sur une ligne puis « Modifier » pour accéder au calendrier, aux périodes pédagogiques et aux vacations.',
        'create' => 'Créez une nouvelle session : le code ECAP-XXXXXX est généré automatiquement. Configurez ensuite les onglets calendrier, périodes et vacations.',
        'edit' => 'Gérez le déroulé complet de cette session via les onglets ci-dessous.',
      ],
    ],
    'App.Filament.Resources.EcapStaffAssignments.EcapStaffAssignmentResource' => [
      'navigation_tooltip' => 'Déclarez qui enseigne, supervise ou modère chaque session ou vacation ECAP. Sans affectation ici, les profs ne recevront pas les questions @ du portail fidèle.',
      'pages' => [
        'list' => 'Consultez et gérez les acteurs de vacation (enseignant, superviseur, modérateur) par session.',
        'create' => 'Affectez un utilisateur à un rôle ECAP pour une session ou une vacation précise.',
        'edit' => 'Modifiez ou désactivez une affectation de rôle vacation.',
      ],
    ],
    'App.Filament.Resources.VacationQuestions.VacationQuestionResource' => [
      'navigation_tooltip' => 'Fil des questions ECAP par module de cours : visible par tous les acteurs, réponse attendue de @prof ou @tous. Escalade automatique admin + enseignants après 1 h sans réponse.',
      'pages' => [
        'list' => 'Surveillez toutes les publications fidèles, filtrez par statut et ouvrez une fiche pour lire le fil ou intervenir.',
        'create' => 'Créez une question manuellement (rare) : liez un module ECAP, choisissez @tous ou un enseignant précis.',
        'edit' => 'Lisez la question, le module # associé, le destinataire @ et enregistrez une réponse si besoin.',
      ],
    ],
    'App.Filament.Resources.Courses.CourseResource' => [
      'navigation_tooltip' => 'Conteneur pédagogique d’un cursus : un cours regroupe plusieurs modules (ex. « ECAP année 1 »). Créez le cours avant d’ajouter modules et chapitres.',
      'pages' => [
        'list' => 'Liste des cours par programme.',
        'create' => 'Créez un cours rattaché à un programme.',
        'edit' => 'Modifiez le cours et gérez ses modules via les onglets.',
      ],
    ],
    'App.Filament.Resources.CourseModules.CourseModuleResource' => [
      'navigation_tooltip' => 'Découpage du cours en modules (#module dans le fil Q&R fidèle). Pour ECAP : configurez aussi le quiz de fin de module (5 questions, 80 %) dans l’onglet du module.',
      'pages' => [
        'list' => 'Modules de cours regroupés par cursus. Pour ECAP, configurez le quiz de fin de module dans l\'onglet dédié de chaque module.',
        'create' => 'Ajoutez un module à un cours.',
        'edit' => 'Contenu du module : chapitres et quiz de validation obligatoire (5 questions, 80 %).',
      ],
    ],
    'App.Filament.Resources.Chapters.ChapterResource' => [
      'navigation_tooltip' => 'Unités de lecture dans un module. Un chapitre doit être publié pour apparaître dans le parcours fidèle ; il contient les blocs (texte, vidéo, fichier).',
      'pages' => [
        'list' => 'Chapitres regroupés par module de cours. Publiez-les pour les rendre visibles dans le parcours fidèle.',
        'create' => 'Créez un chapitre dans un module.',
        'edit' => 'Modifiez le chapitre et ses blocs de contenu.',
      ],
    ],
    'App.Filament.Resources.ContentBlocks.ContentBlockResource' => [
      'navigation_tooltip' => 'Éléments à l’intérieur d’un chapitre : paragraphe, vidéo, PDF, etc. La liste est regroupée par chapitre ; préférez l’onglet « Contenu » depuis le chapitre pour l’ordre pédagogique.',
      'pages' => [
        'list' => 'Contenu des chapitres, regroupé par chapitre (texte, vidéo, fichier…).',
        'create' => 'Ajoutez un contenu à un chapitre.',
        'edit' => 'Modifiez le contenu pédagogique du chapitre.',
      ],
    ],
    'App.Filament.Resources.MediaAssets.MediaAssetResource' => [
      'navigation_tooltip' => 'Bibliothèque centrale des fichiers (images, audio, vidéo, PDF). Téléversez ici puis réutilisez le média dans plusieurs blocs sans re-upload.',
      'pages' => [
        'list' => 'Médias uploadés, réutilisables dans les blocs de contenu.',
        'create' => 'Téléversez un nouveau fichier média.',
        'edit' => 'Modifiez les métadonnées du média.',
      ],
    ],
    'App.Filament.Resources.Enrollments.EnrollmentResource' => [
      'navigation_tooltip' => 'Inscriptions confirmées des fidèles à une session ECAP (mode en ligne ou présentiel). Vérifiez ici si un fidèle est bien rattaché à la bonne session.',
      'pages' => [
        'list' => 'Liste des inscriptions confirmées par session ECAP.',
        'create' => 'Inscrivez manuellement un fidèle à une session.',
        'edit' => 'Modifiez une inscription existante.',
      ],
    ],
    'App.Filament.Resources.LearningGroups.LearningGroupResource' => [
      'navigation_tooltip' => 'Communautés de vacation sur toute la session ECAP. Sert à répartir les fidèles et à cibler les acteurs (enseignant / superviseur) par groupe.',
      'pages' => [
        'list' => 'Groupes de vacation créés pour répartir les fidèles.',
        'create' => 'Créez un groupe de vacation.',
        'edit' => 'Modifiez le groupe et gérez ses membres.',
      ],
    ],
    'App.Filament.Resources.LearningGroupMembers.LearningGroupMemberResource' => [
      'navigation_tooltip' => 'Liste des fidèles affectés à chaque groupe de vacation. Permet de corriger une mauvaise affectation après inscription.',
      'pages' => [
        'list' => 'Liste des affectations fidèle ↔ groupe de vacation.',
        'create' => 'Ajoutez un fidèle à un groupe.',
        'edit' => 'Modifiez une affectation de membre.',
      ],
    ],
    'App.Filament.Resources.StudentAcademicRecords.StudentAcademicRecordResource' => [
      'navigation_tooltip' => 'Dossier académique ECAP : notes, validations, statut de passage. Consultez ou mettez à jour la situation officielle du fidèle dans la session.',
      'pages' => [
        'list' => 'Dossiers académiques des fidèles inscrits à ECAP.',
        'create' => 'Ouvrez un dossier académique manuellement.',
        'edit' => 'Mettez à jour le dossier académique d\'un fidèle.',
      ],
    ],
    'App.Filament.Resources.Assessments.AssessmentResource' => [
      'navigation_tooltip' => 'Créez et paramétrez tous les tests : quiz de chapitre, quiz obligatoire de fin de module ECAP (5 questions), TP notés et examens. C’est le point d’entrée avant d’ajouter les questions.',
      'pages' => [
        'list' => 'Toutes les évaluations : quiz de chapitre, quiz de fin de module ECAP, TP et examens.',
        'create' => 'Créez une nouvelle évaluation.',
        'edit' => 'Paramètres du test. Pour un quiz de fin de module, ajoutez exactement 5 questions avec un chapitre de révision par question.',
      ],
    ],
    'App.Filament.Resources.Questions.QuestionResource' => [
      'navigation_tooltip' => 'Banque globale des énoncés QCM : réutilisez une question dans plusieurs tests ou créez-en depuis l’onglet Questions d’une évaluation.',
      'pages' => [
        'list' => 'Questions de tous les tests. Préférez l\'onglet « Questions » depuis une évaluation.',
        'create' => 'Créez une question isolée.',
        'edit' => 'Modifiez l\'énoncé, les options et le chapitre de révision.',
      ],
    ],
    'App.Filament.Resources.QuestionOptions.QuestionOptionResource' => [
      'navigation_tooltip' => 'Propositions de réponse (QCM) rattachées à une question. Indiquez la bonne réponse pour que la correction automatique fonctionne.',
      'pages' => [
        'list' => 'Options de réponse de la plateforme.',
        'create' => 'Ajoutez une proposition de réponse.',
        'edit' => 'Modifiez une option (texte, correct/incorrect).',
      ],
    ],
    'App.Filament.Resources.AssessmentAttempts.AssessmentAttemptResource' => [
      'navigation_tooltip' => 'Historique des passages de quiz/tests par les fidèles : score, date, réussite ou échec. Utile pour débloquer manuellement ou analyser les difficultés.',
      'pages' => [
        'list' => 'Historique des tentatives de quiz et tests : suivi des scores et reprises.',
        'create' => 'Enregistrez une tentative manuellement (cas exceptionnel).',
        'edit' => 'Consultez le détail d\'une passation de test.',
      ],
    ],
    'App.Filament.Resources.AttemptAnswers.AttemptAnswerResource' => [
      'navigation_tooltip' => 'Détail question par question d’une passation de test. Permet d’auditer ce que le fidèle a répondu à chaque QCM.',
      'pages' => [
        'list' => 'Réponses individuelles enregistrées pour chaque question d\'un test.',
        'create' => 'Saisie manuelle d\'une réponse (audit ou correction).',
        'edit' => 'Modifiez une réponse enregistrée.',
      ],
    ],
    'App.Filament.Resources.AssignmentSubmissions.AssignmentSubmissionResource' => [
      'navigation_tooltip' => 'Travaux pratiques déposés par les fidèles : suivez la correction mentor, la validation modérateur et le statut de publication du TP modèle.',
      'pages' => [
        'list' => 'Remises de TP : statut, correction mentor et validation modérateur.',
        'create' => 'Enregistrez une remise manuellement.',
        'edit' => 'Consultez ou validez une remise de travail pratique.',
      ],
    ],
    'App.Filament.Resources.ChapterProgress.ChapterProgressResource' => [
      'navigation_tooltip' => 'Où en est chaque fidèle dans chaque chapitre (non commencé, en cours, terminé). Diagnostic si un module suivant reste verrouillé.',
      'pages' => [
        'list' => 'Progression par chapitre pour chaque fidèle.',
        'create' => 'Initialisez une progression manuellement.',
        'edit' => 'Ajustez l\'état d\'avancement d\'un chapitre.',
      ],
    ],
    'App.Filament.Resources.ContentBlockProgress.ContentBlockProgressResource' => [
      'navigation_tooltip' => 'Suivi fin à l’intérieur d’un chapitre (bloc lu, vidéo vue). Plus granulaire que la progression chapitre seule.',
      'pages' => [
        'list' => 'Suivi fin (blocs lus, vidéos vues, etc.).',
        'create' => 'Créez une entrée de progression bloc.',
        'edit' => 'Modifiez la progression sur un bloc.',
      ],
    ],
    'App.Filament.Resources.MentorProfiles.MentorProfileResource' => [
      'navigation_tooltip' => 'Répertoire des mentors Métamorpho (photo, bio, disponibilité). Un mentor sans profil complet n’apparaît pas correctement côté fidèle.',
      'pages' => [
        'list' => 'Mentors enregistrés pour accompagner les fidèles Métamorpho.',
        'create' => 'Créez un profil mentor.',
        'edit' => 'Modifiez les informations du mentor.',
      ],
    ],
    'App.Filament.Resources.MentorAssignments.MentorAssignmentResource' => [
      'navigation_tooltip' => 'Binômes mentor ↔ fidèle pour Métamorpho. Créez ou clôturez une assignation pour ouvrir l’espace mentor et le chat.',
      'pages' => [
        'list' => 'Assignations actives et historiques de mentorat.',
        'create' => 'Assignez un mentor à un fidèle.',
        'edit' => 'Modifiez une assignation mentor.',
      ],
    ],
    'App.Filament.Resources.MentoringFeedback.MentoringFeedbackResource' => [
      'navigation_tooltip' => 'Avis et retours écrits dans le cadre du mentorat Métamorpho. Archive consultable par l’administration.',
      'pages' => [
        'list' => 'Feedbacks de mentorat enregistrés.',
        'create' => 'Ajoutez un avis de mentorat.',
        'edit' => 'Modifiez un feedback.',
      ],
    ],
    'App.Filament.Resources.MentoringDecisions.MentoringDecisionResource' => [
      'navigation_tooltip' => 'Décisions formelles du mentor (validation d’étape, report, etc.). Trace officielle des choix d’accompagnement.',
      'pages' => [
        'list' => 'Décisions prises par les mentors.',
        'create' => 'Enregistrez une décision.',
        'edit' => 'Modifiez une décision mentor.',
      ],
    ],
    'App.Filament.Resources.MentoringReports.MentoringReportResource' => [
      'navigation_tooltip' => 'Rapports périodiques rédigés par les mentors. Permet un suivi structuré sur plusieurs semaines.',
      'pages' => [
        'list' => 'Rapports de suivi mentorat.',
        'create' => 'Créez un rapport mentor.',
        'edit' => 'Modifiez un rapport existant.',
      ],
    ],
    'App.Filament.Resources.Defenses.DefenseResource' => [
      'navigation_tooltip' => 'Planification des soutenances et défenses de fin de cursus ECAP (jury, créneau, statut).',
      'pages' => [
        'list' => 'Planification et suivi des soutenances.',
        'create' => 'Planifiez une soutenance.',
        'edit' => 'Modifiez les détails d\'une soutenance.',
      ],
    ],
    'App.Filament.Resources.MentorTpPublications.MentorTpPublicationResource' => [
      'navigation_tooltip' => 'TP modèles déposés par les mentors en attente de validation admin avant publication aux fidèles.',
      'pages' => [
        'list' => 'TP mentors soumis pour publication.',
        'view' => 'Consultez le détail du TP mentor avant validation.',
      ],
    ],
    'App.Filament.Resources.Certificates.CertificateResource' => [
      'navigation_tooltip' => 'Certificats et brevets déjà délivrés aux fidèles. Vérifiez le numéro, la date et le cursus concerné.',
      'pages' => [
        'list' => 'Certificats émis.',
        'create' => 'Émettez un certificat manuellement.',
        'edit' => 'Modifiez un certificat délivré.',
      ],
    ],
    'App.Filament.Resources.CertificateTemplates.CertificateTemplateResource' => [
      'navigation_tooltip' => 'Modèles graphiques (mise en page, champs dynamiques) utilisés pour générer les certificats PDF.',
      'pages' => [
        'list' => 'Modèles de certificats disponibles.',
        'create' => 'Créez un modèle de certificat.',
        'edit' => 'Modifiez la mise en page du modèle.',
      ],
    ],
    'App.Filament.Resources.PrayerSessions.PrayerSessionResource' => [
      'navigation_tooltip' => 'Sessions de prière communautaires PHILA : dates, lieux, capacité. Les fidèles s’inscrivent depuis le portail public ou l’admin.',
      'pages' => [
        'list' => 'Calendrier des sessions de prière.',
        'create' => 'Planifiez une session de prière.',
        'edit' => 'Modifiez une session de prière.',
      ],
    ],
    'App.Filament.Resources.PrayerSessionAttendees.PrayerSessionAttendeeResource' => [
      'navigation_tooltip' => 'Liste des participants inscrits à chaque session de prière. Export possible pour l’organisation sur place.',
      'pages' => [
        'list' => 'Présences et inscriptions aux sessions de prière.',
        'create' => 'Inscrivez un participant.',
        'edit' => 'Modifiez une participation.',
      ],
    ],
    'App.Filament.Resources.EmailOutboxes.EmailOutboxResource' => [
      'navigation_tooltip' => 'File d’attente des e-mails sortants (notifications, OTP, rappels). Consultez en cas d’e-mail non reçu par un fidèle.',
      'pages' => [
        'list' => 'E-mails en attente d\'envoi ou déjà envoyés.',
        'create' => 'Créez un e-mail sortant manuellement.',
        'edit' => 'Consultez le contenu d\'un e-mail.',
      ],
    ],
    'App.Filament.Resources.ExportJobs.ExportJobResource' => [
      'navigation_tooltip' => 'Exports CSV/Excel lancés depuis l’admin : suivez l’état (en cours, terminé, erreur) et téléchargez le fichier généré.',
      'pages' => [
        'list' => 'Jobs d\'export CSV/Excel et leur statut.',
        'create' => 'Lancez un nouvel export.',
        'edit' => 'Consultez le détail d\'un export.',
      ],
    ],
    'App.Filament.Resources.DeploymentOperations.DeploymentOperationResource' => [
      'navigation_tooltip' => 'Maintenance production : migrations, Shield, storage et seeders sans SSH. Une route HTTP sécurisée (DEPLOYMENT_TOKEN) permet aussi le déploiement depuis un pipeline CI/CD. Réservé aux super administrateurs.',
      'pages' => [
        'list' => 'Diagnostic migrations et stockage, boutons d\'exécution, encart curl pour la route HTTP de déploiement et historique des opérations.',
        'view' => 'Sortie console détaillée d\'une opération de maintenance.',
      ],
    ],
    'App.Filament.Resources.ReportSnapshots.ReportSnapshotResource' => [
      'navigation_tooltip' => 'Instantanés de rapports archivés à une date donnée. Conserve une preuve des chiffres communiqués à un instant T.',
      'pages' => [
        'list' => 'Snapshots de rapports enregistrés.',
        'create' => 'Générez un instantané de rapport.',
        'edit' => 'Consultez un instantané.',
      ],
    ],
    'App.Filament.Resources.Roles.RoleResource' => [
      'navigation_tooltip' => 'Rôles et permissions Shield : définissez qui peut voir, créer, modifier ou supprimer chaque menu admin (ECAP, évaluations, etc.).',
      'pages' => [
        'list' => 'Liste des rôles admin.',
        'create' => 'Créez un rôle avec des permissions.',
        'edit' => 'Modifiez les permissions d\'un rôle.',
      ],
    ],
  ],
];
