<?php
return [
    'language_name' => 'Deutsch',
    // General
    'site_name' => 'Budgetplaner',
    'site_description' => 'Moderne Lösung für die persönliche Finanzverwaltung',
    'welcome' => 'Willkommen',
    'logout' => 'Abmelden',
    'save' => 'Speichern',
    'cancel' => 'Abbrechen',
    'delete' => 'Löschen',
    'edit' => 'Bearbeiten',
    'update' => 'Aktualisieren',
    'yes' => 'Ja',
    'no' => 'Nein',
    'confirm' => 'Bestätigen',
    'go_to_app' => 'Zur App',

    // Login/Register
    'username' => 'Benutzername',
    'password' => 'Passwort',
    'remember_me' => 'Angemeldet bleiben',
    'login' => [
        'title' => 'Anmelden',
        'error_message' => 'Ungültiger Benutzername oder Passwort.',
        'no_account' => 'Noch kein Konto? Kostenlos erstellen',
        'success' => 'Anmeldung erfolgreich! Weiterleitung...',
        'error' => 'Ein Fehler ist beim Anmelden aufgetreten.',
        'required' => 'Bitte geben Sie Ihren Benutzernamen und Ihr Passwort ein.',
        'invalid' => 'Ungültiger Benutzername oder Passwort.',
        'locked' => 'Ihr Konto wurde gesperrt. Bitte versuchen Sie es später erneut.',
        'inactive' => 'Ihr Konto ist noch nicht aktiviert. Bitte überprüfen Sie Ihre E-Mail.',
        'have_account' => 'Haben Sie ein Konto? Anmelden'
    ],

    // Footer
    'footer' => [
        'links' => 'Links',
        'contact' => 'Kontakt',
        'copyright' => 'Alle Rechte vorbehalten.'
    ],

    // Home Page
    'hero' => [
        'title' => 'Verwalten Sie Ihre finanzielle Freiheit',
        'description' => 'Verfolgen Sie einfach Ihre Einnahmen, Ausgaben und Ersparnisse. Das Erreichen Ihrer finanziellen Ziele war noch nie so einfach.',
        'cta' => 'Jetzt loslegen'
    ],

    'features' => [
        'title' => 'Funktionen',
        'income_tracking' => [
            'title' => 'Einnahmenverfolgung',
            'description' => 'Kategorisieren Sie alle Ihre Einnahmen und verfolgen Sie automatisch Ihre regelmäßigen Einkünfte.'
        ],
        'expense_management' => [
            'title' => 'Ausgabenverwaltung',
            'description' => 'Behalten Sie Ihre Ausgaben unter Kontrolle und verwalten Sie einfach Ihre Zahlungspläne.'
        ],
        'savings_goals' => [
            'title' => 'Sparziele',
            'description' => 'Setzen Sie sich Ihre finanziellen Ziele und verfolgen Sie visuell Ihren Fortschritt.'
        ]
    ],

    'testimonials' => [
        'title' => 'Erfahrungsberichte',
        '1' => [
            'text' => '"Dank dieser App kann ich meine finanzielle Situation viel besser kontrollieren. Jetzt weiß ich, wohin jeder Cent geht."',
            'name' => 'John D.',
            'title' => 'Softwareentwickler'
        ],
        '2' => [
            'text' => '"Das Verfolgen meiner Sparziele ist jetzt so einfach. Die visuellen Grafiken steigern meine Motivation."',
            'name' => 'Sarah M.',
            'title' => 'Lehrerin'
        ],
        '3' => [
            'text' => '"Ich verpasse nie wieder meine regelmäßigen Zahlungen. Das Erinnerungssystem funktioniert wirklich für mich."',
            'name' => 'Mike R.',
            'title' => 'Unternehmer'
        ]
    ],

    'cta' => [
        'title' => 'Gestalten Sie Ihre finanzielle Zukunft',
        'description' => 'Erstellen Sie jetzt ein kostenloses Konto und übernehmen Sie die Kontrolle über Ihre Finanzen.',
        'button' => 'Kostenlos starten'
    ],

    // Validation
    'required' => 'Dieses Feld ist erforderlich',
    'min_length' => 'Muss mindestens :min Zeichen lang sein',
    'max_length' => 'Darf höchstens :max Zeichen lang sein',
    'email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein',
    'match' => 'Passwörter stimmen nicht überein',
    'unique' => 'Dieser Wert wird bereits verwendet',

    // Authentication
    'password_confirm' => 'Passwort bestätigen',
    'forgot_password' => 'Passwort vergessen',
    'register_success' => 'Registrierung erfolgreich! Sie können sich jetzt anmelden.',
    'logout_confirm' => 'Möchten Sie sich wirklich abmelden?',
    'logout_success' => 'Erfolgreich abgemeldet',
    'auth' => [
        'invalid_request' => 'Ungültige Anfrage',
        'username_min_length' => 'Der Benutzername muss mindestens 3 Zeichen lang sein',
        'password_min_length' => 'Das Passwort muss mindestens 6 Zeichen lang sein',
        'password_mismatch' => 'Passwörter stimmen nicht überein',
        'username_taken' => 'Dieser Benutzername ist bereits vergeben',
        'register_success' => 'Registrierung erfolgreich!',
        'register_error' => 'Ein Fehler ist während der Registrierung aufgetreten',
        'database_error' => 'Ein Datenbankfehler ist aufgetreten',
        'credentials_required' => 'Benutzername und Passwort sind erforderlich',
        'login_success' => 'Anmeldung erfolgreich',
        'invalid_credentials' => 'Ungültiger Benutzername oder Passwort',
        'logout_success' => 'Abmeldung erfolgreich',
        'session_expired' => 'Ihre Sitzung ist abgelaufen, bitte melden Sie sich erneut an',
        'account_locked' => 'Ihr Konto wurde gesperrt, bitte versuchen Sie es später erneut',
        'account_inactive' => 'Ihr Konto ist noch nicht aktiviert',
        'remember_me' => 'Angemeldet bleiben',
        'forgot_password' => 'Passwort vergessen',
        'reset_password' => 'Passwort zurücksetzen',
        'reset_password_success' => 'Ein Link zum Zurücksetzen des Passworts wurde an Ihre E-Mail-Adresse gesendet',
        'reset_password_error' => 'Ein Fehler ist während des Zurücksetzens des Passworts aufgetreten'
    ],

    // Income
    'incomes' => 'Einnahmen',
    'add_income' => 'Neue Einnahme hinzufügen',
    'edit_income' => 'Einnahme bearbeiten',
    'income_name' => 'Name der Einnahme',
    'income_amount' => 'Betrag',
    'income_date' => 'Datum der ersten Einnahme',
    'income_category' => 'Kategorie',
    'income_note' => 'Hinweis',
    'income_recurring' => 'Wiederkehrende Einnahme',
    'income_frequency' => 'Wiederholungsfrequenz',
    'income_end_date' => 'Enddatum',
    'income' => [
        'title' => 'Einnahme',
        'add_success' => 'Einnahme erfolgreich hinzugefügt',
        'add_error' => 'Ein Fehler ist beim Hinzufügen der Einnahme aufgetreten',
        'add_recurring_error' => 'Ein Fehler ist beim Hinzufügen der wiederkehrenden Einnahme aufgetreten',
        'edit_success' => 'Einnahme erfolgreich aktualisiert',
        'edit_error' => 'Ein Fehler ist beim Aktualisieren der Einnahme aufgetreten',
        'delete_success' => 'Einnahme erfolgreich gelöscht',
        'delete_error' => 'Ein Fehler ist beim Löschen der Einnahme aufgetreten',
        'delete_confirm' => 'Möchten Sie diese Einnahme wirklich löschen?',
        'mark_received' => [
            'success' => 'Einnahme erfolgreich als erhalten markiert',
            'error' => 'Fehler beim Markieren der Einnahme als erhalten'
        ],
        'mark_not_received' => 'Als nicht erhalten markieren',
        'not_found' => 'Noch keine Einnahmen hinzugefügt',
        'load_error' => 'Ein Fehler ist beim Laden der Einnahmen aufgetreten',
        'update_error' => 'Ein Fehler ist beim Aktualisieren der Einnahme aufgetreten',
        'rate_error' => 'Konnte den Wechselkurs nicht abrufen',
        'id' => 'Einnahme-ID',
        'name' => 'Name der Einnahme',
        'amount' => 'Betrag',
        'currency' => 'Währung',
        'date' => 'Datum',
        'frequency' => 'Wiederholungsfrequenz',
        'end_date' => 'Enddatum',
        'status' => 'Status',
        'next_date' => 'Nächstes Datum',
        'total_amount' => 'Gesamtbetrag',
        'remaining_amount' => 'Restbetrag',
        'received_amount' => 'Erhaltene Betrag',
        'pending_amount' => 'Ausstehender Betrag',
        'recurring_info' => 'Wiederkehrende Informationen',
        'recurring_count' => 'Wiederholungsanzahl',
        'recurring_total' => 'Gesamte Wiederholungen',
        'recurring_remaining' => 'Verbleibende Wiederholungen',
        'recurring_completed' => 'Abgeschlossene Wiederholungen',
        'recurring_next' => 'Nächste Wiederholung',
        'recurring_last' => 'Letzte Wiederholung'
    ],

    // Payments
    'payments' => 'Zahlungen',
    'add_payment' => 'Neue Zahlung hinzufügen',
    'edit_payment' => 'Zahlung bearbeiten',
    'payment_name' => 'Name der Zahlung',
    'payment_amount' => 'Betrag',
    'payment_date' => 'Zahlungsdatum',
    'payment_category' => 'Kategorie',
    'payment_note' => 'Hinweis',
    'payment_recurring' => 'Wiederkehrende Zahlung',
    'payment_frequency' => 'Wiederholungsfrequenz',
    'payment_end_date' => 'Enddatum',
    'payment' => [
        'title' => 'Zahlung',
        'add_success' => 'Zahlung erfolgreich hinzugefügt',
        'add_error' => 'Ein Fehler ist beim Hinzufügen der Zahlung aufgetreten',
        'add_recurring_error' => 'Ein Fehler ist beim Hinzufügen der wiederkehrenden Zahlung aufgetreten',
        'edit_success' => 'Zahlung erfolgreich aktualisiert',
        'edit_error' => 'Ein Fehler ist beim Aktualisieren der Zahlung aufgetreten',
        'delete_success' => 'Zahlung erfolgreich gelöscht',
        'delete_error' => 'Ein Fehler ist beim Löschen der Zahlung aufgetreten',
        'delete_confirm' => 'Möchten Sie diese Zahlung wirklich löschen?',
        'mark_paid' => [
            'success' => 'Zahlung erfolgreich als bezahlt markiert',
            'error' => 'Fehler beim Markieren der Zahlung als bezahlt'
        ],
        'mark_not_paid' => 'Als nicht bezahlt markieren',
        'not_found' => 'Noch keine Zahlungen hinzugefügt',
        'load_error' => 'Ein Fehler ist beim Laden der Zahlungen aufgetreten',
        'update_error' => 'Ein Fehler ist beim Aktualisieren der Zahlung aufgetreten',
        'rate_error' => 'Konnte den Wechselkurs nicht abrufen',
        'id' => 'Zahlungs-ID',
        'name' => 'Name der Zahlung',
        'amount' => 'Betrag',
        'currency' => 'Währung',
        'date' => 'Datum',
        'frequency' => 'Wiederholungsfrequenz',
        'end_date' => 'Enddatum',
        'status' => 'Status',
        'next_date' => 'Nächstes Datum',
        'total_amount' => 'Gesamtbetrag',
        'remaining_amount' => 'Restbetrag',
        'paid_amount' => 'Bezahlter Betrag',
        'pending_amount' => 'Ausstehender Betrag',
        'recurring_info' => 'Wiederkehrende Informationen',
        'recurring_count' => 'Wiederholungsanzahl',
        'recurring_total' => 'Gesamte Wiederholungen',
        'recurring_remaining' => 'Verbleibende Wiederholungen',
        'recurring_completed' => 'Abgeschlossene Wiederholungen',
        'recurring_next' => 'Nächste Wiederholung',
        'recurring_last' => 'Letzte Wiederholung',
        'transfer' => 'Überweisung auf den nächsten Monat',
        'recurring' => [
            'total_payment' => 'Gesamtzahlung',
            'pending_payment' => 'Ausstehende Zahlung'
        ],
        'buttons' => [
            'delete' => 'Löschen',
            'edit' => 'Bearbeiten',
            'mark_paid' => 'Als bezahlt markieren',
            'mark_not_paid' => 'Als nicht bezahlt markieren'
        ]
    ],

    // Savings
    'savings' => 'Ersparnisse',
    'add_saving' => 'Neue Ersparnis hinzufügen',
    'edit_saving' => 'Ersparnis bearbeiten',
    'saving_name' => 'Name der Ersparnis',
    'target_amount' => 'Zielbetrag',
    'current_amount' => 'Aktueller Betrag',
    'start_date' => 'Startdatum',
    'target_date' => 'Zieldatum',
    'saving' => [
        'title' => 'Ersparnis',
        'add_success' => 'Ersparnis erfolgreich hinzugefügt',
        'add_error' => 'Ein Fehler ist beim Hinzufügen der Ersparnis aufgetreten',
        'edit_success' => 'Ersparnis erfolgreich aktualisiert',
        'edit_error' => 'Ein Fehler ist beim Aktualisieren der Ersparnis aufgetreten',
        'delete_success' => 'Ersparnis erfolgreich gelöscht',
        'delete_error' => 'Ein Fehler ist beim Löschen der Ersparnis aufgetreten',
        'delete_confirm' => 'Möchten Sie diese Ersparnis wirklich löschen?',
        'progress' => 'Fortschritt',
        'remaining' => 'Restbetrag',
        'remaining_days' => 'Verbleibende Tage',
        'monthly_needed' => 'Monatlich benötigter Betrag',
        'completed' => 'Abgeschlossen',
        'on_track' => 'Auf Kurs',
        'behind' => 'Im Rückstand',
        'ahead' => 'Voraus',
        'load_error' => 'Ein Fehler ist beim Laden der Ersparnisse aufgetreten',
        'not_found' => 'Noch keine Ersparnisse hinzugefügt',
        'update_error' => 'Ein Fehler ist beim Aktualisieren der Ersparnis aufgetreten',
        'name' => 'Name der Ersparnis',
        'target_amount' => 'Zielbetrag',
        'current_amount' => 'Aktueller Betrag',
        'currency' => 'Währung',
        'start_date' => 'Startdatum',
        'target_date' => 'Zieldatum',
        'status' => 'Status',
        'progress_info' => 'Fortschrittsinformationen',
        'daily_needed' => 'Täglich benötigter Betrag',
        'weekly_needed' => 'Wöchentlich benötigter Betrag',
        'yearly_needed' => 'Jährlich benötigter Betrag',
        'completion_date' => 'Geschätztes Abschlussdatum',
        'completion_rate' => 'Abschlussrate',
        'days_left' => 'Verbleibende Tage',
        'days_total' => 'Gesamte Tage',
        'days_passed' => 'Vergangene Tage',
        'expected_progress' => 'Erwarteter Fortschritt',
        'actual_progress' => 'Tatsächlicher Fortschritt',
        'progress_difference' => 'Fortschrittsunterschied',
        'update_amount' => 'Betrag aktualisieren',
        'update_details' => 'Details aktualisieren'
    ],

    // Currency
    'currency' => 'Währung',
    'base_currency' => 'Basiswährung',
    'exchange_rate' => 'Wechselkurs',
    'update_rate' => 'Mit aktuellem Kurs aktualisieren',

    // Frequency
    'frequency' => [
        'none' => 'Einmalig',
        'daily' => 'Täglich',
        'weekly' => 'Wöchentlich',
        'monthly' => 'Monatlich',
        'bimonthly' => 'Zweimonatlich',
        'quarterly' => 'Vierteljährlich',
        'fourmonthly' => '4-Monatlich',
        'fivemonthly' => '5-Monatlich',
        'sixmonthly' => '6-Monatlich',
        'yearly' => 'Jährlich'
    ],

    // Months
    'months' => [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'März',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Dezember'
    ],

    // Settings
    'settings' => [
        'title' => 'Benutzereinstellungen',
        'base_currency' => 'Basiswährung',
        'base_currency_info' => 'Alle Berechnungen basieren auf dieser Währung.',
        'theme' => 'Thema',
        'theme_light' => 'Helles Thema',
        'theme_dark' => 'Dunkles Thema',
        'theme_info' => 'Farbschema der Benutzeroberfläche auswählen.',
        'language' => 'Sprache',
        'language_info' => 'Sprache der Benutzeroberfläche auswählen.',
        'save_success' => 'Einstellungen erfolgreich gespeichert',
        'save_error' => 'Ein Fehler ist beim Speichern der Einstellungen aufgetreten',
        'current_password' => 'Aktuelles Passwort',
        'new_password' => 'Neues Passwort',
        'new_password_confirm' => 'Neues Passwort bestätigen',
        'password_success' => 'Passwort erfolgreich geändert',
        'password_error' => 'Ein Fehler ist beim Ändern des Passworts aufgetreten',
        'password_mismatch' => 'Aktuelles Passwort ist falsch',
        'password_same' => 'Neues Passwort darf nicht mit dem alten übereinstimmen',
        'password_requirements' => 'Das Passwort muss mindestens 6 Zeichen lang sein'
    ],

    // Errors
    'error' => 'Fehler!',
    'success' => 'Erfolg!',
    'warning' => 'Warnung!',
    'info' => 'Info',
    'error_occurred' => 'Ein Fehler ist aufgetreten',
    'try_again' => 'Bitte versuchen Sie es erneut',
    'session_expired' => 'Ihre Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.',
    'not_found' => 'Seite nicht gefunden',
    'unauthorized' => 'Unbefugter Zugriff',
    'forbidden' => 'Zugriff verboten',

    // Registration
    'register' => [
        'title' => 'Registrieren',
        'error_message' => 'Ein Fehler ist während der Registrierung aufgetreten.',
        'success' => 'Registrierung erfolgreich! Sie können sich jetzt anmelden.',
        'username_taken' => 'Dieser Benutzername ist bereits vergeben.',
        'password_mismatch' => 'Passwörter stimmen nicht überein.',
        'invalid_currency' => 'Ungültige Währung ausgewählt.',
        'required' => 'Bitte füllen Sie alle Felder aus.',
    ],

    // Currencies
    'currencies' => [
        'base_info' => 'Alle Berechnungen basieren auf dieser Währung. Keine Sorge, Sie können sie später ändern.',
        'try' => 'Türkische Lira',
        'usd' => 'US-Dollar',
        'eur' => 'Euro',
        'gbp' => 'Britisches Pfund'
    ],

    // Application
    'app' => [
        'previous_month' => 'Vorheriger Monat',
        'next_month' => 'Nächster Monat',
        'monthly_income' => 'Monatliches Einkommen',
        'monthly_expense' => 'Monatliche Ausgaben',
        'net_balance' => 'Netto-Saldo',
        'period' => 'Zeitraum',
        'next_income' => 'Nächstes Einkommen',
        'next_payment' => 'Nächste Zahlung',
        'payment_power' => 'Zahlungskraft',
        'installment_info' => 'Informationen zur Rate',
        'total' => 'Gesamt',
        'total_payment' => 'Gesamtzahlung',
        'loading' => 'Laden...',
        'no_data' => 'Keine Daten gefunden',
        'confirm_delete' => 'Möchten Sie wirklich löschen?',
        'yes_delete' => 'Ja, löschen',
        'no_cancel' => 'Nein, abbrechen',
        'operation_success' => 'Operation erfolgreich',
        'operation_error' => 'Ein Fehler ist während der Operation aufgetreten',
        'save_success' => 'Erfolgreich gespeichert',
        'save_error' => 'Ein Fehler ist beim Speichern aufgetreten',
        'update_success' => 'Erfolgreich aktualisiert',
        'update_error' => 'Ein Fehler ist beim Aktualisieren aufgetreten',
        'delete_success' => 'Erfolgreich gelöscht',
        'delete_error' => 'Ein Fehler ist beim Löschen aufgetreten'
    ],

    // Currency operations
    'currency' => [
        'invalid_request' => 'Ungültige Anfrage',
        'invalid_currency' => 'Ungültige Währung',
        'update_success' => 'Währung erfolgreich aktualisiert',
        'update_error' => 'Ein Fehler ist beim Aktualisieren der Währung aufgetreten',
        'database_error' => 'Ein Datenbankfehler ist aufgetreten',
        'currency_required' => 'Währungsauswahl ist erforderlich',
        'rate_fetched' => 'Wechselkurs erfolgreich abgerufen',
        'rate_fetch_error' => 'Konnte den Wechselkurs nicht abrufen',
        'rate_not_found' => 'Wechselkurs nicht gefunden',
        'select_currency' => 'Währung auswählen',
        'current_rate' => 'Aktueller Kurs',
        'conversion_rate' => 'Umrechnungskurs',
        'last_update' => 'Letzte Aktualisierung',
        'auto_update' => 'Automatische Aktualisierung',
        'manual_update' => 'Manuelle Aktualisierung',
        'update_daily' => 'Täglich aktualisieren',
        'update_weekly' => 'Wöchentlich aktualisieren',
        'update_monthly' => 'Monatlich aktualisieren',
        'update_never' => 'Nie aktualisieren'
    ],

    // Summary
    'summary' => [
        'title' => 'Zusammenfassung',
        'load_error' => 'Ein Fehler ist beim Laden der Zusammenfassungsinformationen aufgetreten',
        'user_not_found' => 'Benutzer nicht gefunden',
        'total_income' => 'Gesamteinnahmen',
        'total_expense' => 'Gesamtausgaben',
        'net_balance' => 'Netto-Saldo',
        'positive_balance' => 'Positiv',
        'negative_balance' => 'Negativ',
        'monthly_summary' => 'Monatliche Zusammenfassung',
        'yearly_summary' => 'Jährliche Zusammenfassung',
        'income_vs_expense' => 'Einnahmen/Ausgaben-Verhältnis',
        'savings_progress' => 'Sparfortschritt',
        'payment_schedule' => 'Zahlungsplan',
        'upcoming_payments' => 'Bevorstehende Zahlungen',
        'upcoming_incomes' => 'Bevorstehende Einnahmen',
        'expense_percentage' => 'Ausgabenanteil',
        'savings_percentage' => 'Sparanteil',
        'monthly_trend' => 'Monatlicher Trend',
        'yearly_trend' => 'Jährlicher Trend',
        'balance_trend' => 'Saldo-Trend',
        'expense_trend' => 'Ausgaben-Trend',
        'income_trend' => 'Einnahmen-Trend',
        'savings_trend' => 'Spar-Trend',
        'budget_status' => 'Budgetstatus',
        'on_budget' => 'Im Budget',
        'over_budget' => 'Über Budget',
        'under_budget' => 'Unter Budget',
        'budget_warning' => 'Budgetwarnung',
        'budget_alert' => 'Budgetalarm',
        'expense_categories' => 'Ausgabenkategorien',
        'income_sources' => 'Einnahmequellen',
        'savings_goals' => 'Sparziele',
        'payment_methods' => 'Zahlungsmethoden',
        'recurring_transactions' => 'Wiederkehrende Transaktionen',
        'financial_goals' => 'Finanzielle Ziele',
        'goal_progress' => 'Zielfortschritt',
        'goal_completion' => 'Zielerreichung',
        'goal_status' => 'Zielstatus',
        'completed_goals' => 'Abgeschlossene Ziele',
        'active_goals' => 'Aktive Ziele',
        'missed_goals' => 'Verpasste Ziele',
        'goal_history' => 'Zielverlauf'
    ],

    // Transfer
    'transfer' => [
        'title' => 'Zahlungsüberweisung',
        'confirm' => 'Möchten Sie wirklich unbezahlte Zahlungen auf den nächsten Monat übertragen?',
        'transfer_button' => 'Ja, übertragen',
        'cancel_button' => 'Abbrechen',
        'error' => 'Ein Fehler ist beim Übertragen der Zahlungen aufgetreten',
        'success' => 'Zahlungen erfolgreich übertragen',
        'no_unpaid_payments' => 'Keine unbezahlten Zahlungen zum Übertragen gefunden',
        'payment_transferred_from' => '%s (übertragen von %s)',
        'update_error' => 'Fehler beim Aktualisieren der Zahlung'
    ],

    'validation' => [
        'field_required' => 'Das Feld %s ist erforderlich',
        'field_numeric' => 'Das Feld %s muss numerisch sein',
        'field_date' => 'Das Feld %s muss ein gültiges Datum sein (YYYY-MM-DD)',
        'field_currency' => 'Das Feld %s muss eine gültige Währung sein',
        'field_frequency' => 'Das Feld %s muss eine gültige Frequenz sein',
        'field_min_value' => 'Das Feld %s muss mindestens %s sein',
        'field_max_value' => 'Das Feld %s darf höchstens %s sein',
        'date_range_error' => 'Das Startdatum darf nicht größer als das Enddatum sein',
        'invalid_format' => 'Ungültiges Format',
        'invalid_value' => 'Ungültiger Wert',
        'required_field' => 'Dieses Feld ist erforderlich',
        'min_length' => 'Muss mindestens %s Zeichen lang sein',
        'max_length' => 'Darf höchstens %s Zeichen lang sein',
        'exact_length' => 'Muss genau %s Zeichen lang sein',
        'greater_than' => 'Muss größer als %s sein',
        'less_than' => 'Muss kleiner als %s sein',
        'between' => 'Muss zwischen %s und %s liegen',
        'matches' => 'Muss mit %s übereinstimmen',
        'different' => 'Muss sich von %s unterscheiden',
        'unique' => 'Dieser Wert wird bereits verwendet',
        'valid_email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein',
        'valid_url' => 'Bitte geben Sie eine gültige URL ein',
        'valid_ip' => 'Bitte geben Sie eine gültige IP-Adresse ein',
        'valid_date' => 'Bitte geben Sie ein gültiges Datum ein',
        'valid_time' => 'Bitte geben Sie eine gültige Uhrzeit ein',
        'valid_datetime' => 'Bitte geben Sie ein gültiges Datum und eine gültige Uhrzeit ein',
        'alpha' => 'Darf nur Buchstaben enthalten',
        'alpha_numeric' => 'Darf nur Buchstaben und Zahlen enthalten',
        'alpha_dash' => 'Darf nur Buchstaben, Zahlen, Bindestriche und Unterstriche enthalten',
        'numeric' => 'Darf nur Zahlen enthalten',
        'integer' => 'Muss eine ganze Zahl sein',
        'decimal' => 'Muss eine Dezimalzahl sein',
        'natural' => 'Muss eine positive ganze Zahl sein',
        'natural_no_zero' => 'Muss eine ganze Zahl größer als Null sein',
        'valid_base64' => 'Bitte geben Sie einen gültigen Base64-Wert ein',
        'valid_json' => 'Bitte geben Sie einen gültigen JSON-Wert ein',
        'valid_file' => 'Bitte wählen Sie eine gültige Datei aus',
        'valid_image' => 'Bitte wählen Sie eine gültige Bilddatei aus',
        'valid_phone' => 'Bitte geben Sie eine gültige Telefonnummer ein',
        'valid_credit_card' => 'Bitte geben Sie eine gültige Kreditkartennummer ein',
        'valid_color' => 'Bitte geben Sie einen gültigen Farbcode ein'
    ],

    // Utils
    'utils' => [
        'validation' => [
            'required' => ':field ist erforderlich',
            'numeric' => ':field muss numerisch sein',
            'date' => ':field muss ein gültiges Datum sein',
            'currency' => ':field muss eine gültige Währung sein',
            'frequency' => ':field muss eine gültige Frequenz sein',
            'min_value' => ':field muss mindestens :min sein',
            'max_value' => ':field darf höchstens :max sein',
            'error_title' => 'Validierungsfehler',
            'confirm_button' => 'OK'
        ],
        'session' => [
            'error_title' => 'Sitzungsfehler',
            'invalid_token' => 'Ungültiges Sicherheitstoken'
        ],
        'frequency' => [
            'none' => 'Keine',
            'monthly' => 'Monatlich',
            'bimonthly' => 'Zweimonatlich',
            'quarterly' => 'Vierteljährlich',
            'fourmonthly' => '4-Monatlich',
            'fivemonthly' => '5-Monatlich',
            'sixmonthly' => '6-Monatlich',
            'yearly' => 'Jährlich'
        ],
        'form' => [
            'income_name' => 'Name der Einnahme',
            'payment_name' => 'Name der Zahlung',
            'amount' => 'Betrag',
            'currency' => 'Währung',
            'date' => 'Datum',
            'frequency' => 'Wiederholungsfrequenz',
            'saving_name' => 'Name der Ersparnis',
            'target_amount' => 'Zielbetrag',
            'current_amount' => 'Aktueller Betrag',
            'start_date' => 'Startdatum',
            'target_date' => 'Zieldatum'
        ]
    ],

    'user' => [
        'not_found' => 'Benutzerinformationen nicht gefunden',
        'update_success' => 'Benutzerinformationen erfolgreich aktualisiert',
        'update_error' => 'Fehler beim Aktualisieren der Benutzerinformationen'
    ],
];