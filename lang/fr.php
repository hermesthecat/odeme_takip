<?php
return [
    'language_name' => 'Français',
    // Genel
    'site_name' => 'Suivi de budget',
    'site_description' => 'Solution moderne simplifiant la gestion des finances personnelles',
    'welcome' => 'Bienvenue',
    'logout' => 'Déconnexion',
    'save' => 'Enregistrer',
    'cancel' => 'Annuler',
    'delete' => 'Supprimer',
    'edit' => 'Modifier',
    'update' => 'Mettre à jour',
    'yes' => 'Oui',
    'no' => 'Non',
    'confirm' => 'Confirmer',
    'go_to_app' => 'Aller à l\'application',

    // Giriş/Kayıt
    'username' => 'Nom d\'utilisateur',
    'password' => 'Mot de passe',
    'remember_me' => 'Se souvenir de moi',
    'login' => [
        'title' => 'Connexion',
        'error_message' => 'Nom d\'utilisateur ou mot de passe incorrect.',
        'no_account' => 'Pas de compte ? Créez un compte gratuit',
        'success' => 'Connexion réussie ! Redirection...',
        'error' => 'Une erreur s\'est produite lors de la connexion.',
        'required' => 'Veuillez entrer votre nom d\'utilisateur et votre mot de passe.',
        'invalid' => 'Nom d\'utilisateur ou mot de passe incorrect.',
        'locked' => 'Votre compte a été bloqué. Veuillez réessayer plus tard.',
        'inactive' => 'Votre compte n\'est pas encore actif. Veuillez vérifier votre e-mail.',
        'have_account' => 'Vous avez un compte ? Connectez-vous'
    ],

    // Footer
    'footer' => [
        'links' => 'Liens',
        'contact' => 'Contact',
        'copyright' => 'Tous droits réservés.'
    ],

    // Ana Sayfa
    'hero' => [
        'title' => 'Gérez votre liberté financière',
        'description' => 'Suivez facilement vos revenus, dépenses et économies. Atteindre vos objectifs financiers n\'a jamais été aussi simple.',
        'cta' => 'Commencez maintenant'
    ],

    'features' => [
        'title' => 'Fonctionnalités',
        'income_tracking' => [
            'title' => 'Suivi des revenus',
            'description' => 'Catégorisez tous vos revenus et suivez automatiquement vos revenus réguliers.'
        ],
        'expense_management' => [
            'title' => 'Gestion des dépenses',
            'description' => 'Gardez vos dépenses sous contrôle et gérez facilement vos plans de paiement.'
        ],
        'savings_goals' => [
            'title' => 'Objectifs d\'épargne',
            'description' => 'Fixez vos objectifs financiers et suivez visuellement vos progrès.'
        ]
    ],

    'testimonials' => [
        'title' => 'Témoignages',
        '1' => [
            'text' => '"Grâce à cette application, je peux beaucoup mieux contrôler ma situation financière. Maintenant, je sais où va chaque centime."',
            'name' => 'Ahmet Y.',
            'title' => 'Développeur de logiciels'
        ],
        '2' => [
            'text' => '"Suivre mes objectifs d\'épargne est maintenant très facile. Les graphiques visuels augmentent ma motivation."',
            'name' => 'Ayşe K.',
            'title' => 'Professeur'
        ],
        '3' => [
            'text' => '"Je ne manque plus jamais mes paiements réguliers. Le système de rappel m\'aide vraiment beaucoup."',
            'name' => 'Mehmet S.',
            'title' => 'Commerçant'
        ]
    ],

    'cta' => [
        'title' => 'Façonnez votre avenir financier',
        'description' => 'Créez un compte gratuit maintenant et prenez le contrôle financier.',
        'button' => 'Commencer gratuitement'
    ],

    // Doğrulama
    'required' => 'Ce champ est obligatoire',
    'min_length' => 'Doit contenir au moins :min caractères',
    'max_length' => 'Doit contenir au plus :max caractères',
    'email' => 'Veuillez entrer une adresse e-mail valide',
    'match' => 'Les mots de passe ne correspondent pas',
    'unique' => 'Cette valeur est déjà utilisée',

    // Kimlik Doğrulama
    'password_confirm' => 'Confirmer le mot de passe',
    'forgot_password' => 'Mot de passe oublié',
    'login_success' => 'Connexion réussie !',
    'logout_confirm' => 'Êtes-vous sûr de vouloir vous déconnecter ?',
    'logout_success' => 'Déconnexion réussie',
    'auth' => [
        'invalid_request' => 'Requête invalide',
        'username_min_length' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères',
        'password_min_length' => 'Le mot de passe doit contenir au moins 6 caractères',
        'password_mismatch' => 'Les mots de passe ne correspondent pas',
        'username_taken' => 'Ce nom d\'utilisateur est déjà utilisé',
        'register_success' => 'Inscription réussie !',
        'register_error' => 'Une erreur s\'est produite lors de l\'inscription',
        'database_error' => 'Une erreur de base de données s\'est produite',
        'credentials_required' => 'Nom d\'utilisateur et mot de passe requis',
        'login_success' => 'Connexion réussie',
        'invalid_credentials' => 'Nom d\'utilisateur ou mot de passe incorrect',
        'logout_success' => 'Déconnexion réussie',
        'session_expired' => 'Votre session a expiré, veuillez vous reconnecter',
        'account_locked' => 'Votre compte a été bloqué, veuillez réessayer plus tard',
        'account_inactive' => 'Votre compte n\'est pas encore actif',
        'remember_me' => 'Se souvenir de moi',
        'forgot_password' => 'Mot de passe oublié',
        'reset_password' => 'Réinitialiser le mot de passe',
        'reset_password_success' => 'Un lien pour réinitialiser le mot de passe a été envoyé à votre adresse e-mail',
        'reset_password_error' => 'Une erreur s\'est produite lors de la réinitialisation du mot de passe'
    ],

    // Gelirler
    'incomes' => 'Revenus',
    'add_income' => 'Ajouter un nouveau revenu',
    'edit_income' => 'Modifier le revenu',
    'income_name' => 'Nom du revenu',
    'income_amount' => 'Montant',
    'income_date' => 'Date du premier revenu',
    'income_category' => 'Catégorie',
    'income_note' => 'Remarque',
    'income_recurring' => 'Revenu récurrent',
    'income_frequency' => 'Fréquence de répétition',
    'income_end_date' => 'Date de fin',
    'income' => [
        'title' => 'Revenu',
        'add_success' => 'Revenu ajouté avec succès',
        'add_error' => 'Une erreur s\'est produite lors de l\'ajout du revenu',
        'edit_success' => 'Revenu mis à jour avec succès',
        'edit_error' => 'Une erreur s\'est produite lors de la mise à jour du revenu',
        'delete_success' => 'Revenu supprimé avec succès',
        'delete_error' => 'Une erreur s\'est produite lors de la suppression du revenu',
        'delete_confirm' => 'Êtes-vous sûr de vouloir supprimer ce revenu ?',
        'mark_received' => [
            'success' => 'Revenu marqué comme reçu avec succès',
            'error' => 'Impossible de marquer le revenu comme reçu'
        ],
        'mark_not_received' => 'Marquer comme non reçu',
        'not_found' => 'Aucun revenu n\'a encore été ajouté',
        'load_error' => 'Une erreur s\'est produite lors du chargement des revenus',
        'update_error' => 'Une erreur s\'est produite lors de la mise à jour du revenu',
        'rate_error' => 'Impossible de récupérer le taux de change',
        'id' => 'ID du revenu',
        'name' => 'Nom du revenu',
        'amount' => 'Montant',
        'currency' => 'Devise',
        'date' => 'Date',
        'frequency' => 'Fréquence de répétition',
        'end_date' => 'Date de fin',
        'status' => 'Statut',
        'next_date' => 'Date suivante',
        'total_amount' => 'Montant total',
        'remaining_amount' => 'Montant restant',
        'received_amount' => 'Montant reçu',
        'pending_amount' => 'Montant en attente',
        'recurring_info' => 'Informations sur la répétition',
        'recurring_count' => 'Nombre de répétitions',
        'recurring_total' => 'Total des répétitions',
        'recurring_remaining' => 'Répétitions restantes',
        'recurring_completed' => 'Répétitions terminées',
        'recurring_next' => 'Prochaine répétition',
        'recurring_last' => 'Dernière répétition'
    ],

    // Ödemeler
    'payments' => 'Paiements',
    'add_payment' => 'Ajouter un nouveau paiement',
    'edit_payment' => 'Modifier le paiement',
    'payment_name' => 'Nom du paiement',
    'payment_amount' => 'Montant',
    'payment_date' => 'Date du paiement',
    'payment_category' => 'Catégorie',
    'payment_note' => 'Remarque',
    'payment_recurring' => 'Paiement récurrent',
    'payment_frequency' => 'Fréquence de répétition',
    'payment_end_date' => 'Date de fin',
    'payment' => [
        'title' => 'Paiement',
        'add_success' => 'Paiement ajouté avec succès',
        'add_error' => 'Une erreur s\'est produite lors de l\'ajout du paiement',
        'add_recurring_error' => 'Une erreur s\'est produite lors de l\'ajout du paiement récurrent',
        'edit_success' => 'Paiement mis à jour avec succès',
        'edit_error' => 'Une erreur s\'est produite lors de la mise à jour du paiement',
        'delete_success' => 'Paiement supprimé avec succès',
        'delete_error' => 'Une erreur s\'est produite lors de la suppression du paiement',
        'delete_confirm' => 'Êtes-vous sûr de vouloir supprimer ce paiement ?',
        'mark_paid' => [
            'success' => 'Paiement marqué comme payé avec succès',
            'error' => 'Impossible de marquer le paiement comme payé'
        ],
        'mark_not_paid' => 'Marquer comme non payé',
        'not_found' => 'Aucun paiement n\'a encore été ajouté',
        'load_error' => 'Une erreur s\'est produite lors du chargement des paiements',
        'update_error' => 'Une erreur s\'est produite lors de la mise à jour du paiement',
        'rate_error' => 'Impossible de récupérer le taux de change',
        'id' => 'ID du paiement',
        'name' => 'Nom du paiement',
        'amount' => 'Montant',
        'currency' => 'Devise',
        'date' => 'Date',
        'frequency' => 'Fréquence de répétition',
        'end_date' => 'Date de fin',
        'status' => 'Statut',
        'next_date' => 'Date suivante',
        'total_amount' => 'Montant total',
        'remaining_amount' => 'Montant restant',
        'paid_amount' => 'Montant payé',
        'pending_amount' => 'Montant en attente',
        'recurring_info' => 'Informations sur la répétition',
        'recurring_count' => 'Nombre de répétitions',
        'recurring_total' => 'Total des répétitions',
        'recurring_remaining' => 'Répétitions restantes',
        'recurring_completed' => 'Répétitions terminées',
        'recurring_next' => 'Prochaine répétition',
        'recurring_last' => 'Dernière répétition',
        'transfer' => 'Transférer au mois prochain',
        'recurring' => [
            'total_payment' => 'Montant total du paiement',
            'pending_payment' => 'Paiement en attente'
        ],
        'buttons' => [
            'delete' => 'Supprimer',
            'edit' => 'Modifier',
            'mark_paid' => 'Marquer comme payé',
            'mark_not_paid' => 'Marquer comme non payé'
        ]
    ],

    // Birikimler
    'savings' => 'Économies',
    'add_saving' => 'Ajouter une nouvelle économie',
    'edit_saving' => 'Modifier l\'économie',
    'saving_name' => 'Nom de l\'économie',
    'target_amount' => 'Montant cible',
    'current_amount' => 'Montant actuel',
    'start_date' => 'Date de début',
    'target_date' => 'Date cible',
    'saving' => [
        'title' => 'Économie',
        'add_success' => 'Économie ajoutée avec succès',
        'add_error' => 'Une erreur s\'est produite lors de l\'ajout de l\'économie',
        'edit_success' => 'Économie mise à jour avec succès',
        'edit_error' => 'Une erreur s\'est produite lors de la mise à jour de l\'économie',
        'delete_success' => 'Économie supprimée avec succès',
        'delete_error' => 'Une erreur s\'est produite lors de la suppression de l\'économie',
        'delete_confirm' => 'Êtes-vous sûr de vouloir supprimer cette économie ?',
        'progress' => 'Progrès',
        'remaining' => 'Montant restant',
        'remaining_days' => 'Jours restants',
        'monthly_needed' => 'Montant mensuel nécessaire',
        'completed' => 'Terminé',
        'on_track' => 'Sur la bonne voie',
        'behind' => 'En retard',
        'ahead' => 'En avance',
        'load_error' => 'Une erreur s\'est produite lors du chargement des économies',
        'not_found' => 'Aucune économie n\'a encore été ajoutée',
        'update_error' => 'Une erreur s\'est produite lors de la mise à jour de l\'économie',
        'name' => 'Nom de l\'économie',
        'target_amount' => 'Montant cible',
        'current_amount' => 'Montant actuel',
        'currency' => 'Devise',
        'start_date' => 'Date de début',
        'target_date' => 'Date cible',
        'status' => 'Statut',
        'progress_info' => 'Informations sur le progrès',
        'daily_needed' => 'Montant quotidien nécessaire',
        'weekly_needed' => 'Montant hebdomadaire nécessaire',
        'yearly_needed' => 'Montant annuel nécessaire',
        'completion_date' => 'Date d\'achèvement estimée',
        'completion_rate' => 'Taux d\'achèvement',
        'days_left' => 'Jours restants',
        'days_total' => 'Total des jours',
        'days_passed' => 'Jours passés',
        'expected_progress' => 'Progrès attendu',
        'actual_progress' => 'Progrès réel',
        'progress_difference' => 'Différence de progrès',
        'update_amount' => 'Mettre à jour le montant',
        'update_details' => 'Mettre à jour les détails'
    ],

    // Para Birimleri
    'currency' => 'Devise',
    'base_currency' => 'Devise de base',
    'exchange_rate' => 'Taux de change',
    'update_rate' => 'Mettre à jour avec le taux de change actuel',

    // Sıklık
    'frequency' => [
        'none' => 'Une fois',
        'daily' => 'Quotidiennement',
        'weekly' => 'Hebdomadaire',
        'monthly' => 'Mensuellement',
        'bimonthly' => 'Bimestriellement',
        'quarterly' => 'Trimestriellement',
        'fourmonthly' => 'Tous les quatre mois',
        'fivemonthly' => 'Tous les cinq mois',
        'sixmonthly' => 'Semestriellement',
        'yearly' => 'Annuellement'
    ],

    // Aylar
    'months' => [
        1 => 'Janvier',
        2 => 'Février',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Août',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Décembre'
    ],

    // Ayarlar
    'settings_title' => 'Paramètres utilisateur',
    'theme' => 'Thème',
    'theme_light' => 'Thème clair',
    'theme_dark' => 'Thème sombre',
    'language' => 'Langue',
    'current_password' => 'Mot de passe actuel',
    'new_password' => 'Nouveau mot de passe',
    'new_password_confirm' => 'Confirmer le nouveau mot de passe',

    // Hatalar
    'error' => 'Erreur !',
    'success' => 'Succès !',
    'warning' => 'Avertissement !',
    'info' => 'Info',
    'error_occurred' => 'Une erreur s\'est produite',
    'try_again' => 'Veuillez réessayer',
    'session_expired' => 'Votre session a expiré. Veuillez vous reconnecter.',
    'not_found' => 'Page non trouvée',
    'unauthorized' => 'Accès non autorisé',
    'forbidden' => 'Accès interdit',

    // Yeni eklenen kısımlar
    'register' => [
        'title' => 'Créer un compte',
        'error_message' => 'Une erreur s\'est produite lors de l\'inscription.',
        'success' => 'Inscription réussie ! Vous pouvez vous connecter.',
        'username_taken' => 'Ce nom d\'utilisateur est déjà utilisé.',
        'password_mismatch' => 'Les mots de passe ne correspondent pas.',
        'invalid_currency' => 'Sélection de devise non valide.',
        'required' => 'Veuillez remplir tous les champs.',
    ],

    // Currencies
    'currencies' => [
        'base_info' => 'Tous les calculs seront effectués en utilisant cette devise. Ne vous inquiétez pas, vous pouvez la modifier ultérieurement.',
        'try' => 'Lire turque',
        'usd' => 'Dollar américain',
        'eur' => 'Euro',
        'gbp' => 'Livre sterling'
    ],

    // Ayarlar
    'settings' => [
        'title' => 'Paramètres utilisateur',
        'base_currency' => 'Devise de base',
        'base_currency_info' => 'Tous les calculs seront effectués en utilisant cette devise.',
        'theme' => 'Thème',
        'theme_light' => 'Thème clair',
        'theme_dark' => 'Thème sombre',
        'theme_info' => 'Sélection du thème de couleur de l\'interface.',
        'language' => 'Langue',
        'language_info' => 'Sélection de la langue de l\'interface.',
        'save_success' => 'Paramètres enregistrés avec succès',
        'save_error' => 'Une erreur s\'est produite lors de l\'enregistrement des paramètres',
        'current_password' => 'Mot de passe actuel',
        'new_password' => 'Nouveau mot de passe',
        'new_password_confirm' => 'Confirmer le nouveau mot de passe',
        'password_success' => 'Mot de passe modifié avec succès',
        'password_error' => 'Une erreur s\'est produite lors de la modification du mot de passe',
        'password_mismatch' => 'Le mot de passe actuel est incorrect',
        'password_same' => 'Le nouveau mot de passe ne peut pas être le même que l\'ancien',
        'password_requirements' => 'Le mot de passe doit contenir au moins 6 caractères'
    ],

    // Uygulama
    'app' => [
        'previous_month' => 'Mois précédent',
        'next_month' => 'Mois suivant',
        'monthly_income' => 'Revenu mensuel',
        'monthly_expense' => 'Dépense mensuelle',
        'net_balance' => 'Solde net',
        'period' => 'Période',
        'next_income' => 'Prochain revenu',
        'next_payment' => 'Prochain paiement',
        'payment_power' => 'Pouvoir de paiement',
        'installment_info' => 'Informations sur les versements',
        'total' => 'Total',
        'total_payment' => 'Paiement total',
        'loading' => 'Chargement...',
        'no_data' => 'Aucune donnée trouvée',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer ?',
        'yes_delete' => 'Oui, supprimer',
        'no_cancel' => 'Non, annuler',
        'operation_success' => 'Opération réussie',
        'operation_error' => 'Une erreur s\'est produite lors de l\'opération',
        'save_success' => 'Enregistré avec succès',
        'save_error' => 'Une erreur s\'est produite lors de l\'enregistrement',
        'update_success' => 'Mis à jour avec succès',
        'update_error' => 'Une erreur s\'est produite lors de la mise à jour',
        'delete_success' => 'Supprimé avec succès',
        'delete_error' => 'Une erreur s\'est produite lors de la suppression'
    ],

    // Para birimi işlemleri
    'currency' => [
        'invalid_request' => 'Requête invalide',
        'invalid_currency' => 'Devise invalide',
        'update_success' => 'Devise mise à jour avec succès',
        'update_error' => 'Une erreur s\'est produite lors de la mise à jour de la devise',
        'database_error' => 'Une erreur de base de données s\'est produite',
        'currency_required' => 'La sélection de la devise est requise',
        'rate_fetched' => 'Taux de change récupéré avec succès',
        'rate_fetch_error' => 'Impossible de récupérer le taux de change',
        'rate_not_found' => 'Taux de change introuvable',
        'select_currency' => 'Sélectionner la devise',
        'current_rate' => 'Taux actuel',
        'conversion_rate' => 'Taux de conversion',
        'last_update' => 'Dernière mise à jour',
        'auto_update' => 'Mise à jour automatique',
        'manual_update' => 'Mise à jour manuelle',
        'update_daily' => 'Mettre à jour quotidiennement',
        'update_weekly' => 'Mettre à jour hebdomadairement',
        'update_monthly' => 'Mettre à jour mensuellement',
        'update_never' => 'Ne jamais mettre à jour'
    ],

    // Özet
    'summary' => [
        'title' => 'Résumé',
        'load_error' => 'Une erreur s\'est produite lors du chargement des informations du résumé',
        'user_not_found' => 'Utilisateur non trouvé',
        'total_income' => 'Revenu total',
        'total_expense' => 'Dépense totale',
        'net_balance' => 'Solde net',
        'positive_balance' => 'Positif',
        'negative_balance' => 'Négatif',
        'monthly_summary' => 'Résumé mensuel',
        'yearly_summary' => 'Résumé annuel',
        'income_vs_expense' => 'Ratio revenus/dépenses',
        'savings_progress' => 'Progrès de l\'épargne',
        'payment_schedule' => 'Calendrier des paiements',
        'upcoming_payments' => 'Paiements à venir',
        'upcoming_incomes' => 'Revenus à venir',
        'expense_percentage' => 'Pourcentage des dépenses',
        'savings_percentage' => 'Pourcentage de l\'épargne',
        'monthly_trend' => 'Tendance mensuelle',
        'yearly_trend' => 'Tendance annuelle',
        'balance_trend' => 'Tendance du solde',
        'expense_trend' => 'Tendance des dépenses',
        'income_trend' => 'Tendance des revenus',
        'savings_trend' => 'Tendance de l\'épargne',
        'budget_status' => 'État du budget',
        'on_budget' => 'Dans le budget',
        'over_budget' => 'Au-dessus du budget',
        'under_budget' => 'En dessous du budget',
        'budget_warning' => 'Avertissement de budget',
        'budget_alert' => 'Alerte de budget',
        'expense_categories' => 'Catégories de dépenses',
        'income_sources' => 'Sources de revenus',
        'savings_goals' => 'Objectifs d\'épargne',
        'payment_methods' => 'Méthodes de paiement',
        'recurring_transactions' => 'Transactions récurrentes',
        'financial_goals' => 'Objectifs financiers',
        'goal_progress' => 'Progrès de l\'objectif',
        'goal_completion' => 'Achèvement de l\'objectif',
        'goal_status' => 'État de l\'objectif',
        'completed_goals' => 'Objectifs atteints',
        'active_goals' => 'Objectifs actifs',
        'missed_goals' => 'Objectifs manqués',
        'goal_history' => 'Historique des objectifs'
    ],

    'transfer' => [
        'title' => 'Transfert de paiement',
        'confirm' => 'Êtes-vous sûr de vouloir transférer les paiements non confirmés au mois prochain ?',
        'transfer_button' => 'Oui, transférer',
        'cancel_button' => 'Annuler',
        'error' => 'Une erreur s\'est produite lors du transfert des paiements',
        'success' => 'Paiements transférés avec succès',
        'no_unpaid_payments' => 'Aucun paiement impayé trouvé à transférer',
        'payment_transferred_from' => '%s (transféré du mois de %s)',
        'update_error' => 'Impossible de mettre à jour le paiement'
    ],

    'validation' => [
        'field_required' => 'Le champ %s est obligatoire',
        'field_numeric' => 'Le champ %s doit être numérique',
        'field_date' => 'Le champ %s doit être une date valide (AAAA-MM-JJ)',
        'field_currency' => 'Le champ %s doit être une devise valide',
        'field_frequency' => 'Le champ %s doit être une fréquence de répétition valide',
        'field_min_value' => 'Le champ %s doit être au moins %s',
        'field_max_value' => 'Le champ %s doit être au plus %s',
        'date_range_error' => 'La date de début ne peut pas être postérieure à la date de fin',
        'invalid_format' => 'Format invalide',
        'invalid_value' => 'Valeur invalide',
        'required_field' => 'Ce champ est obligatoire',
        'min_length' => 'Doit contenir au moins %s caractères',
        'max_length' => 'Doit contenir au plus %s caractères',
        'exact_length' => 'Doit contenir exactement %s caractères',
        'greater_than' => 'Doit être supérieur à %s',
        'less_than' => 'Doit être inférieur à %s',
        'between' => 'Doit être compris entre %s et %s',
        'matches' => 'Doit correspondre à %s',
        'different' => 'Doit être différent de %s',
        'unique' => 'Cette valeur est déjà utilisée',
        'valid_email' => 'Veuillez entrer une adresse e-mail valide',
        'valid_url' => 'Veuillez entrer une URL valide',
        'valid_ip' => 'Veuillez entrer une adresse IP valide',
        'valid_date' => 'Veuillez entrer une date valide',
        'valid_time' => 'Veuillez entrer une heure valide',
        'valid_datetime' => 'Veuillez entrer une date et une heure valides',
        'alpha' => 'Doit contenir uniquement des lettres',
        'alpha_numeric' => 'Doit contenir uniquement des lettres et des chiffres',
        'alpha_dash' => 'Doit contenir uniquement des lettres, des chiffres, des tirets et des underscores',
        'numeric' => 'Doit contenir uniquement des chiffres',
        'integer' => 'Doit être un entier',
        'decimal' => 'Doit être un nombre décimal',
        'natural' => 'Doit être un entier positif',
        'natural_no_zero' => 'Doit être un entier positif supérieur à zéro',
        'valid_base64' => 'Veuillez entrer une valeur Base64 valide',
        'valid_json' => 'Veuillez entrer une valeur JSON valide',
        'valid_file' => 'Veuillez sélectionner un fichier valide',
        'valid_image' => 'Veuillez sélectionner un fichier image valide',
        'valid_phone' => 'Veuillez entrer un numéro de téléphone valide',
        'valid_credit_card' => 'Veuillez entrer un numéro de carte de crédit valide',
        'valid_color' => 'Veuillez entrer un code couleur valide'
    ],

    // Utils
    'utils' => [
        'validation' => [
            'required' => 'Le champ :field est obligatoire',
            'numeric' => 'Le champ :field doit être numérique',
            'date' => 'Le champ :field doit être une date valide',
            'currency' => 'Le champ :field doit être une devise valide',
            'frequency' => 'Le champ :field doit être une fréquence de répétition valide',
            'min_value' => 'Le champ :field doit être au moins :min',
            'max_value' => 'Le champ :field doit être au plus :max',
            'error_title' => 'Erreur de validation',
            'confirm_button' => 'OK'
        ],
        'session' => [
            'error_title' => 'Erreur de session',
            'invalid_token' => 'Jeton de sécurité invalide'
        ],
        'frequency' => [
            'none' => 'Aucune répétition',
            'monthly' => 'Mensuel',
            'bimonthly' => 'Bimestriel',
            'quarterly' => '3 mois',
            'fourmonthly' => '4 mois',
            'fivemonthly' => '5 mois',
            'sixmonthly' => '6 mois',
            'yearly' => 'Annuel'
        ],
        'form' => [
            'income_name' => 'Nom du revenu',
            'payment_name' => 'Nom du paiement',
            'amount' => 'Montant',
            'currency' => 'Devise',
            'date' => 'Date',
            'frequency' => 'Fréquence de répétition',
            'saving_name' => 'Nom de l\'économie',
            'target_amount' => 'Montant cible',
            'current_amount' => 'Montant actuel',
            'start_date' => 'Date de début',
            'target_date' => 'Date cible'
        ]
    ],

    'user' => [
        'not_found' => 'Informations sur l\'utilisateur introuvables',
        'update_success' => 'Informations sur l\'utilisateur mises à jour avec succès',
        'update_error' => 'Impossible de mettre à jour les informations sur l\'utilisateur'
    ],
];
