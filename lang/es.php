<?php
return [
    'language_name' => 'Español',
    // Genel
    'site_name' => 'Rastreador de Presupuesto',
    'site_description' => 'Solución moderna que simplifica la gestión de finanzas personales',
    'welcome' => 'Bienvenido',
    'logout' => 'Cerrar sesión',
    'save' => 'Guardar',
    'cancel' => 'Cancelar',
    'delete' => 'Eliminar',
    'edit' => 'Editar',
    'update' => 'Actualizar',
    'yes' => 'Sí',
    'no' => 'No',
    'confirm' => 'Confirmar',
    'go_to_app' => 'Ir a la aplicación',

    // Giriş/Kayıt
    'username' => 'Nombre de usuario',
    'password' => 'Contraseña',
    'remember_me' => 'Recuérdame',
    'login' => [
        'title' => 'Iniciar sesión',
        'error_message' => 'Nombre de usuario o contraseña no válidos.',
        'no_account' => '¿No tienes una cuenta? Crea una cuenta gratuita',
        'success' => '¡Inicio de sesión exitoso! Redirigiendo...',
        'error' => 'Ocurrió un error al iniciar sesión.',
        'required' => 'Por favor, introduce tu nombre de usuario y contraseña.',
        'invalid' => 'Nombre de usuario o contraseña no válidos.',
        'locked' => 'Tu cuenta ha sido bloqueada. Por favor, inténtalo de nuevo más tarde.',
        'inactive' => 'Tu cuenta aún no está activa. Por favor, revisa tu correo electrónico.',
        'have_account' => '¿Tienes una cuenta? Iniciar sesión'
    ],

    // Footer
    'footer' => [
        'links' => 'Enlaces',
        'contact' => 'Contacto',
        'copyright' => 'Todos los derechos reservados.'
    ],

    // Ana Sayfa
    'hero' => [
        'title' => 'Administra tu libertad financiera',
        'description' => 'Realiza un seguimiento fácil de tus ingresos, gastos y ahorros. Alcanzar tus metas financieras nunca ha sido tan fácil.',
        'cta' => 'Comienza ahora'
    ],

    'features' => [
        'title' => 'Características',
        'income_tracking' => [
            'title' => 'Seguimiento de ingresos',
            'description' => 'Categoriza todos tus ingresos y realiza un seguimiento automático de tus ingresos regulares.'
        ],
        'expense_management' => [
            'title' => 'Gestión de gastos',
            'description' => 'Mantén tus gastos bajo control y administra fácilmente tus planes de pago.'
        ],
        'savings_goals' => [
            'title' => 'Metas de ahorro',
            'description' => 'Establece tus metas financieras y realiza un seguimiento visual de tu progreso.'
        ]
    ],

    'testimonials' => [
        'title' => 'Testimonios',
        '1' => [
            'text' => '"Gracias a esta aplicación, puedo controlar mucho mejor mi situación financiera. Ahora sé a dónde va cada centavo."',
            'name' => 'Ahmet Y.',
            'title' => 'Desarrollador de software'
        ],
        '2' => [
            'text' => '"Realizar un seguimiento de mis metas de ahorro ahora es muy fácil. Los gráficos visuales aumentan mi motivación."',
            'name' => 'Ayşe K.',
            'title' => 'Profesor'
        ],
        '3' => [
            'text' => '"Ya no me pierdo mis pagos regulares. El sistema de recordatorio realmente me ayuda mucho."',
            'name' => 'Mehmet S.',
            'title' => 'Comerciante'
        ]
    ],

    'cta' => [
        'title' => 'Da forma a tu futuro financiero',
        'description' => 'Crea una cuenta gratuita ahora y toma el control financiero.',
        'button' => 'Comienza gratis'
    ],

    // Doğrulama
    'required' => 'Este campo es obligatorio',
    'min_length' => 'Debe tener al menos :min caracteres',
    'max_length' => 'Debe tener como máximo :max caracteres',
    'email' => 'Por favor, introduce una dirección de correo electrónico válida',
    'match' => 'Las contraseñas no coinciden',
    'unique' => 'Este valor ya está en uso',

    // Kimlik Doğrulama
    'password_confirm' => 'Confirmar contraseña',
    'forgot_password' => 'Olvidé mi contraseña',
    'login_success' => '¡Inicio de sesión exitoso!',
    'logout_confirm' => '¿Estás seguro de que quieres cerrar sesión?',
    'logout_success' => 'Cierre de sesión exitoso',
    'auth' => [
        'invalid_request' => 'Solicitud no válida',
        'username_min_length' => 'El nombre de usuario debe tener al menos 3 caracteres',
        'password_min_length' => 'La contraseña debe tener al menos 6 caracteres',
        'password_mismatch' => 'Las contraseñas no coinciden',
        'username_taken' => 'Este nombre de usuario ya está en uso',
        'register_success' => '¡Registro exitoso!',
        'register_error' => 'Ocurrió un error durante el registro',
        'database_error' => 'Ocurrió un error de base de datos',
        'credentials_required' => 'Se requiere nombre de usuario y contraseña',
        'login_success' => 'Inicio de sesión exitoso',
        'invalid_credentials' => 'Nombre de usuario o contraseña no válidos',
        'logout_success' => 'Cierre de sesión exitoso',
        'session_expired' => 'Tu sesión ha expirado, por favor, inicia sesión de nuevo',
        'account_locked' => 'Tu cuenta ha sido bloqueada, por favor, inténtalo de nuevo más tarde',
        'account_inactive' => 'Tu cuenta aún no está activa',
        'remember_me' => 'Recuérdame',
        'forgot_password' => 'Olvidé mi contraseña',
        'reset_password' => 'Restablecer contraseña',
        'reset_password_success' => 'Se ha enviado un enlace para restablecer la contraseña a tu dirección de correo electrónico',
        'reset_password_error' => 'Ocurrió un error al restablecer la contraseña'
    ],

    // Gelirler
    'incomes' => 'Ingresos',
    'add_income' => 'Añadir nuevo ingreso',
    'edit_income' => 'Editar ingreso',
    'income_name' => 'Nombre del ingreso',
    'income_amount' => 'Cantidad',
    'income_date' => 'Fecha del primer ingreso',
    'income_category' => 'Categoría',
    'income_note' => 'Nota',
    'income_recurring' => 'Ingreso recurrente',
    'income_frequency' => 'Frecuencia de repetición',
    'income_end_date' => 'Fecha de finalización',
    'income' => [
        'title' => 'Ingreso',
        'add_success' => 'Ingreso añadido con éxito',
        'add_error' => 'Ocurrió un error al añadir el ingreso',
        'edit_success' => 'Ingreso actualizado con éxito',
        'edit_error' => 'Ocurrió un error al actualizar el ingreso',
        'delete_success' => 'Ingreso eliminado con éxito',
        'delete_error' => 'Ocurrió un error al eliminar el ingreso',
        'delete_confirm' => '¿Estás seguro de que quieres eliminar este ingreso?',
        'mark_received' => [
            'success' => 'Ingreso marcado como recibido con éxito',
            'error' => 'No se pudo marcar el ingreso como recibido'
        ],
        'mark_not_received' => 'Marcar como no recibido',
        'not_found' => 'Aún no se ha añadido ningún ingreso',
        'load_error' => 'Ocurrió un error al cargar los ingresos',
        'update_error' => 'Ocurrió un error al actualizar el ingreso',
        'rate_error' => 'No se pudo recuperar la tasa de cambio',
        'id' => 'ID del ingreso',
        'name' => 'Nombre del ingreso',
        'amount' => 'Cantidad',
        'currency' => 'Moneda',
        'date' => 'Fecha',
        'frequency' => 'Frecuencia de repetición',
        'end_date' => 'Fecha de finalización',
        'status' => 'Estado',
        'next_date' => 'Próxima fecha',
        'total_amount' => 'Cantidad total',
        'remaining_amount' => 'Cantidad restante',
        'received_amount' => 'Cantidad recibida',
        'pending_amount' => 'Cantidad pendiente',
        'recurring_info' => 'Información de repetición',
        'recurring_count' => 'Conteo de repeticiones',
        'recurring_total' => 'Total de repeticiones',
        'recurring_remaining' => 'Repeticiones restantes',
        'recurring_completed' => 'Repeticiones completadas',
        'recurring_next' => 'Próxima repetición',
        'recurring_last' => 'Última repetición'
    ],

    // Ödemeler
    'payments' => 'Pagos',
    'add_payment' => 'Añadir nuevo pago',
    'edit_payment' => 'Editar pago',
    'payment_name' => 'Nombre del pago',
    'payment_amount' => 'Cantidad',
    'payment_date' => 'Fecha del pago',
    'payment_category' => 'Categoría',
    'payment_note' => 'Nota',
    'payment_recurring' => 'Pago recurrente',
    'payment_frequency' => 'Frecuencia de repetición',
    'payment_end_date' => 'Fecha de finalización',
    'payment' => [
        'title' => 'Pago',
        'add_success' => 'Pago añadido con éxito',
        'add_error' => 'Ocurrió un error al añadir el pago',
        'add_recurring_error' => 'Ocurrió un error al añadir el pago recurrente',
        'edit_success' => 'Pago actualizado con éxito',
        'edit_error' => 'Ocurrió un error al actualizar el pago',
        'delete_success' => 'Pago eliminado con éxito',
        'delete_error' => 'Ocurrió un error al eliminar el pago',
        'delete_confirm' => '¿Estás seguro de que quieres eliminar este pago?',
        'mark_paid' => [
            'success' => 'Pago marcado como pagado con éxito',
            'error' => 'No se pudo marcar el pago como pagado'
        ],
        'mark_not_paid' => 'Marcar como no pagado',
        'not_found' => 'Aún no se ha añadido ningún pago',
        'load_error' => 'Ocurrió un error al cargar los pagos',
        'update_error' => 'Ocurrió un error al actualizar el pago',
        'rate_error' => 'No se pudo recuperar la tasa de cambio',
        'id' => 'ID del pago',
        'name' => 'Nombre del pago',
        'amount' => 'Cantidad',
        'currency' => 'Moneda',
        'date' => 'Fecha',
        'frequency' => 'Frecuencia de repetición',
        'end_date' => 'Fecha de finalización',
        'status' => 'Estado',
        'next_date' => 'Próxima fecha',
        'total_amount' => 'Cantidad total',
        'remaining_amount' => 'Cantidad restante',
        'paid_amount' => 'Cantidad pagada',
        'pending_amount' => 'Cantidad pendiente',
        'recurring_info' => 'Información de repetición',
        'recurring_count' => 'Conteo de repeticiones',
        'recurring_total' => 'Total de repeticiones',
        'recurring_remaining' => 'Repeticiones restantes',
        'recurring_completed' => 'Repeticiones completadas',
        'recurring_next' => 'Próxima repetición',
        'recurring_last' => 'Última repetición',
        'transfer' => 'Transferir al próximo mes',
        'recurring' => [
            'total_payment' => 'Pago total',
            'pending_payment' => 'Pago pendiente'
        ],
        'buttons' => [
            'delete' => 'Eliminar',
            'edit' => 'Editar',
            'mark_paid' => 'Marcar como pagado',
            'mark_not_paid' => 'Marcar como no pagado'
        ]
    ],

    // Birikimler
    'savings' => 'Ahorros',
    'add_saving' => 'Añadir nuevo ahorro',
    'edit_saving' => 'Editar ahorro',
    'saving_name' => 'Nombre del ahorro',
    'target_amount' => 'Cantidad objetivo',
    'current_amount' => 'Cantidad actual',
    'start_date' => 'Fecha de inicio',
    'target_date' => 'Fecha objetivo',
    'saving' => [
        'title' => 'Ahorro',
        'add_success' => 'Ahorro añadido con éxito',
        'add_error' => 'Ocurrió un error al añadir el ahorro',
        'edit_success' => 'Ahorro actualizado con éxito',
        'edit_error' => 'Ocurrió un error al actualizar el ahorro',
        'delete_success' => 'Ahorro eliminado con éxito',
        'delete_error' => 'Ocurrió un error al eliminar el ahorro',
        'delete_confirm' => '¿Estás seguro de que quieres eliminar este ahorro?',
        'progress' => 'Progreso',
        'remaining' => 'Cantidad restante',
        'remaining_days' => 'Días restantes',
        'monthly_needed' => 'Cantidad mensual necesaria',
        'completed' => 'Completado',
        'on_track' => 'En camino',
        'behind' => 'Atrasado',
        'ahead' => 'Adelantado',
        'load_error' => 'Ocurrió un error al cargar los ahorros',
        'not_found' => 'Aún no se ha añadido ningún ahorro',
        'update_error' => 'Ocurrió un error al actualizar el ahorro',
        'name' => 'Nombre del ahorro',
        'target_amount' => 'Cantidad objetivo',
        'current_amount' => 'Cantidad actual',
        'currency' => 'Moneda',
        'start_date' => 'Fecha de inicio',
        'target_date' => 'Fecha objetivo',
        'status' => 'Estado',
        'progress_info' => 'Información de progreso',
        'daily_needed' => 'Cantidad diaria necesaria',
        'weekly_needed' => 'Cantidad semanal necesaria',
        'yearly_needed' => 'Cantidad anual necesaria',
        'completion_date' => 'Fecha de finalización estimada',
        'completion_rate' => 'Tasa de finalización',
        'days_left' => 'Días restantes',
        'days_total' => 'Días totales',
        'days_passed' => 'Días transcurridos',
        'expected_progress' => 'Progreso esperado',
        'actual_progress' => 'Progreso real',
        'progress_difference' => 'Diferencia de progreso',
        'update_amount' => 'Actualizar cantidad',
        'update_details' => 'Actualizar detalles'
    ],

    // Para Birimleri
    'currency' => 'Moneda',
    'base_currency' => 'Moneda base',
    'exchange_rate' => 'Tasa de cambio',
    'update_rate' => 'Actualizar con la tasa de cambio actual',

    // Sıklık
    'frequency' => [
        'none' => 'Una vez',
        'daily' => 'Diario',
        'weekly' => 'Semanal',
        'monthly' => 'Mensual',
        'bimonthly' => 'Bimensual',
        'quarterly' => 'Trimestral',
        'fourmonthly' => 'Cuatrimestral',
        'fivemonthly' => 'Quincenal',
        'sixmonthly' => 'Semestral',
        'yearly' => 'Anual'
    ],

    // Aylar
    'months' => [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ],

    // Ayarlar
    'settings_title' => 'Configuración del usuario',
    'theme' => 'Tema',
    'theme_light' => 'Tema claro',
    'theme_dark' => 'Tema oscuro',
    'language' => 'Idioma',
    'current_password' => 'Contraseña actual',
    'new_password' => 'Nueva contraseña',
    'new_password_confirm' => 'Confirmar nueva contraseña',

    // Hatalar
    'error' => '¡Error!',
    'success' => '¡Éxito!',
    'warning' => '¡Advertencia!',
    'info' => 'Información',
    'error_occurred' => 'Ocurrió un error',
    'try_again' => 'Por favor, inténtalo de nuevo',
    'session_expired' => 'Tu sesión ha expirado. Por favor, inicia sesión de nuevo.',
    'not_found' => 'Página no encontrada',
    'unauthorized' => 'Acceso no autorizado',
    'forbidden' => 'Acceso prohibido',

    // Yeni eklenen kısımlar
    'register' => [
        'title' => 'Crear cuenta',
        'error_message' => 'Ocurrió un error al registrarse.',
        'success' => '¡Registro exitoso! Puedes iniciar sesión.',
        'username_taken' => 'Este nombre de usuario ya está en uso.',
        'password_mismatch' => 'Las contraseñas no coinciden.',
        'invalid_currency' => 'Selección de moneda no válida.',
        'required' => 'Por favor, rellena todos los campos.',
    ],

    // Currencies
    'currencies' => [
        'base_info' => 'Todos los cálculos se realizarán utilizando esta moneda. No te preocupes, puedes cambiarla más tarde.',
        'try' => 'Lira turca',
        'usd' => 'Dólar estadounidense',
        'eur' => 'Euro',
        'gbp' => 'Libra esterlina'
    ],

    // Ayarlar
    'settings' => [
        'title' => 'Configuración del usuario',
        'base_currency' => 'Moneda base',
        'base_currency_info' => 'Todos los cálculos se realizarán utilizando esta moneda.',
        'theme' => 'Tema',
        'theme_light' => 'Tema claro',
        'theme_dark' => 'Tema oscuro',
        'theme_info' => 'Selección del tema de color de la interfaz.',
        'language' => 'Idioma',
        'language_info' => 'Selección del idioma de la interfaz.',
        'save_success' => 'Configuración guardada con éxito',
        'save_error' => 'Ocurrió un error al guardar la configuración',
        'current_password' => 'Contraseña actual',
        'new_password' => 'Nueva contraseña',
        'new_password_confirm' => 'Confirmar nueva contraseña',
        'password_success' => 'Contraseña cambiada con éxito',
        'password_error' => 'Ocurrió un error al cambiar la contraseña',
        'password_mismatch' => 'La contraseña actual es incorrecta',
        'password_same' => 'La nueva contraseña no puede ser la misma que la anterior',
        'password_requirements' => 'La contraseña debe tener al menos 6 caracteres'
    ],

    // Uygulama
    'app' => [
        'previous_month' => 'Mes anterior',
        'next_month' => 'Mes siguiente',
        'monthly_income' => 'Ingreso mensual',
        'monthly_expense' => 'Gasto mensual',
        'net_balance' => 'Saldo neto',
        'period' => 'Período',
        'next_income' => 'Próximo ingreso',
        'next_payment' => 'Próximo pago',
        'payment_power' => 'Poder de pago',
        'installment_info' => 'Información de la cuota',
        'total' => 'Total',
        'total_payment' => 'Pago total',
        'loading' => 'Cargando...',
        'no_data' => 'No se encontraron datos',
        'confirm_delete' => '¿Estás seguro de que quieres eliminar?',
        'yes_delete' => 'Sí, eliminar',
        'no_cancel' => 'No, cancelar',
        'operation_success' => 'Operación exitosa',
        'operation_error' => 'Ocurrió un error durante la operación',
        'save_success' => 'Guardado con éxito',
        'save_error' => 'Ocurrió un error al guardar',
        'update_success' => 'Actualizado con éxito',
        'update_error' => 'Ocurrió un error al actualizar',
        'delete_success' => 'Eliminado con éxito',
        'delete_error' => 'Ocurrió un error al eliminar'
    ],

    // Para birimi işlemleri
    'currency' => [
        'invalid_request' => 'Solicitud no válida',
        'invalid_currency' => 'Moneda no válida',
        'update_success' => 'Moneda actualizada con éxito',
        'update_error' => 'Ocurrió un error al actualizar la moneda',
        'database_error' => 'Ocurrió un error de base de datos',
        'currency_required' => 'Se requiere selección de moneda',
        'rate_fetched' => 'Tasa de cambio obtenida con éxito',
        'rate_fetch_error' => 'No se pudo recuperar la tasa de cambio',
        'rate_not_found' => 'No se encontró la tasa de cambio',
        'select_currency' => 'Seleccionar moneda',
        'current_rate' => 'Tasa actual',
        'conversion_rate' => 'Tasa de conversión',
        'last_update' => 'Última actualización',
        'auto_update' => 'Actualización automática',
        'manual_update' => 'Actualización manual',
        'update_daily' => 'Actualizar diariamente',
        'update_weekly' => 'Actualizar semanalmente',
        'update_monthly' => 'Actualizar mensualmente',
        'update_never' => 'No actualizar nunca'
    ],

    // Özet
    'summary' => [
        'title' => 'Resumen',
        'load_error' => 'Ocurrió un error al cargar la información del resumen',
        'user_not_found' => 'Usuario no encontrado',
        'total_income' => 'Ingreso total',
        'total_expense' => 'Gasto total',
        'net_balance' => 'Saldo neto',
        'positive_balance' => 'Positivo',
        'negative_balance' => 'Negativo',
        'monthly_summary' => 'Resumen mensual',
        'yearly_summary' => 'Resumen anual',
        'income_vs_expense' => 'Relación ingreso/gasto',
        'savings_progress' => 'Progreso de ahorro',
        'payment_schedule' => 'Programa de pago',
        'upcoming_payments' => 'Próximos pagos',
        'upcoming_incomes' => 'Próximos ingresos',
        'expense_percentage' => 'Porcentaje de gasto',
        'savings_percentage' => 'Porcentaje de ahorro',
        'monthly_trend' => 'Tendencia mensual',
        'yearly_trend' => 'Tendencia anual',
        'balance_trend' => 'Tendencia del saldo',
        'expense_trend' => 'Tendencia del gasto',
        'income_trend' => 'Tendencia del ingreso',
        'savings_trend' => 'Tendencia del ahorro',
        'budget_status' => 'Estado del presupuesto',
        'on_budget' => 'En presupuesto',
        'over_budget' => 'Por encima del presupuesto',
        'under_budget' => 'Por debajo del presupuesto',
        'budget_warning' => 'Advertencia de presupuesto',
        'budget_alert' => 'Alerta de presupuesto',
        'expense_categories' => 'Categorías de gasto',
        'income_sources' => 'Fuentes de ingreso',
        'savings_goals' => 'Metas de ahorro',
        'payment_methods' => 'Métodos de pago',
        'recurring_transactions' => 'Transacciones recurrentes',
        'financial_goals' => 'Metas financieras',
        'goal_progress' => 'Progreso del objetivo',
        'goal_completion' => 'Finalización del objetivo',
        'goal_status' => 'Estado del objetivo',
        'completed_goals' => 'Objetivos completados',
        'active_goals' => 'Objetivos activos',
        'missed_goals' => 'Objetivos perdidos',
        'goal_history' => 'Historial del objetivo'
    ],

    'transfer' => [
        'title' => 'Transferencia de pago',
        'confirm' => '¿Estás seguro de que quieres transferir los pagos no confirmados al próximo mes?',
        'transfer_button' => 'Sí, transferir',
        'cancel_button' => 'Cancelar',
        'error' => 'Ocurrió un error al transferir los pagos',
        'success' => 'Pagos transferidos con éxito',
        'no_unpaid_payments' => 'No se encontraron pagos no confirmados para transferir',
        'payment_transferred_from' => '%s (transferido desde %s mes)',
        'update_error' => 'No se pudo actualizar el pago'
    ],

    'validation' => [
        'field_required' => 'El campo %s es obligatorio',
        'field_numeric' => 'El campo %s debe ser numérico',
        'field_date' => 'El campo %s debe ser una fecha válida (YYYY-MM-DD)',
        'field_currency' => 'El campo %s debe ser una moneda válida',
        'field_frequency' => 'El campo %s debe ser una frecuencia de repetición válida',
        'field_min_value' => 'El campo %s debe ser al menos %s',
        'field_max_value' => 'El campo %s debe ser como máximo %s',
        'date_range_error' => 'La fecha de inicio no puede ser mayor que la fecha de finalización',
        'invalid_format' => 'Formato no válido',
        'invalid_value' => 'Valor no válido',
        'required_field' => 'Este campo es obligatorio',
        'min_length' => 'Debe tener al menos %s caracteres',
        'max_length' => 'Debe tener como máximo %s caracteres',
        'exact_length' => 'Debe tener exactamente %s caracteres',
        'greater_than' => 'Debe ser mayor que %s',
        'less_than' => 'Debe ser menor que %s',
        'between' => 'Debe estar entre %s y %s',
        'matches' => 'Debe coincidir con %s',
        'different' => 'Debe ser diferente de %s',
        'unique' => 'Este valor ya está en uso',
        'valid_email' => 'Por favor, introduce una dirección de correo electrónico válida',
        'valid_url' => 'Por favor, introduce una URL válida',
        'valid_ip' => 'Por favor, introduce una dirección IP válida',
        'valid_date' => 'Por favor, introduce una fecha válida',
        'valid_time' => 'Por favor, introduce una hora válida',
        'valid_datetime' => 'Por favor, introduce una fecha y hora válidas',
        'alpha' => 'Debe contener solo letras',
        'alpha_numeric' => 'Debe contener solo letras y números',
        'alpha_dash' => 'Debe contener solo letras, números, guiones y guiones bajos',
        'numeric' => 'Debe contener solo números',
        'integer' => 'Debe ser un entero',
        'decimal' => 'Debe ser un número decimal',
        'natural' => 'Debe ser un entero positivo',
        'natural_no_zero' => 'Debe ser un entero positivo mayor que cero',
        'valid_base64' => 'Por favor, introduce un valor Base64 válido',
        'valid_json' => 'Por favor, introduce un valor JSON válido',
        'valid_file' => 'Por favor, selecciona un archivo válido',
        'valid_image' => 'Por favor, selecciona un archivo de imagen válido',
        'valid_phone' => 'Por favor, introduce un número de teléfono válido',
        'valid_credit_card' => 'Por favor, introduce un número de tarjeta de crédito válido',
        'valid_color' => 'Por favor, introduce un código de color válido'
    ],

    // Utils
    'utils' => [
        'validation' => [
            'required' => 'El campo :field es obligatorio',
            'numeric' => 'El campo :field debe ser numérico',
            'date' => 'El campo :field debe ser una fecha válida',
            'currency' => 'El campo :field debe ser una moneda válida',
            'frequency' => 'El campo :field debe ser una frecuencia de repetición válida',
            'min_value' => 'El campo :field debe ser al menos :min',
            'max_value' => 'El campo :field debe ser como máximo :max',
            'error_title' => 'Error de validación',
            'confirm_button' => 'Aceptar'
        ],
        'session' => [
            'error_title' => 'Error de sesión',
            'invalid_token' => 'Token de seguridad no válido'
        ],
        'frequency' => [
            'none' => 'Sin repetición',
            'monthly' => 'Mensual',
            'bimonthly' => 'Bimensual',
            'quarterly' => 'Trimestral',
            'fourmonthly' => 'Cuatrimestral',
            'fivemonthly' => 'Quincenal',
            'sixmonthly' => 'Semestral',
            'yearly' => 'Anual'
        ],
        'form' => [
            'income_name' => 'Nombre del ingreso',
            'payment_name' => 'Nombre del pago',
            'amount' => 'Cantidad',
            'currency' => 'Moneda',
            'date' => 'Fecha',
            'frequency' => 'Frecuencia de repetición',
            'saving_name' => 'Nombre del ahorro',
            'target_amount' => 'Cantidad objetivo',
            'current_amount' => 'Cantidad actual',
            'start_date' => 'Fecha de inicio',
            'target_date' => 'Fecha objetivo'
        ]
    ],

    'user' => [
        'not_found' => 'Información del usuario no encontrada',
        'update_success' => 'Información del usuario actualizada con éxito',
        'update_error' => 'No se pudo actualizar la información del usuario'
    ],
];
