<?php
return [
    'language_name' => '日本語',
    // Genel
    'site_name' => '予算トラッカー',
    'site_description' => '個人の財務管理を簡素化する最新のソリューション',
    'welcome' => 'ようこそ',
    'logout' => 'ログアウト',
    'save' => '保存',
    'cancel' => 'キャンセル',
    'delete' => '削除',
    'edit' => '編集',
    'update' => '更新',
    'yes' => 'はい',
    'no' => 'いいえ',
    'confirm' => '確認',
    'go_to_app' => 'アプリへ移動',

    // Giriş/Kayıt
    'username' => 'ユーザー名',
    'password' => 'パスワード',
    'remember_me' => 'ログイン状態を保存する',
    'login' => [
        'title' => 'ログイン',
        'error_message' => '無効なユーザー名またはパスワードです。',
        'no_account' => 'アカウントをお持ちではありませんか？ 無料アカウントを作成する',
        'success' => 'ログインに成功しました！ リダイレクトしています...',
        'error' => 'ログイン中にエラーが発生しました。',
        'required' => 'ユーザー名とパスワードを入力してください。',
        'invalid' => '無効なユーザー名またはパスワードです。',
        'locked' => 'あなたのアカウントはロックされています。 後でもう一度お試しください。',
        'inactive' => 'あなたのアカウントはまだアクティブではありません。 メールを確認してください。',
        'have_account' => 'アカウントをお持ちですか？ ログイン'
    ],

    // Footer
    'footer' => [
        'links' => 'リンク',
        'contact' => 'お問い合わせ',
        'copyright' => '全著作権所有。'
    ],

    // Ana Sayfa
    'hero' => [
        'title' => 'あなたの経済的自由を管理する',
        'description' => '収入、支出、貯蓄を簡単に追跡できます。 経済的な目標を達成することがこれまでになく簡単になりました。',
        'cta' => '今すぐ始める'
    ],

    'features' => [
        'title' => '特徴',
        'income_tracking' => [
            'title' => '収入追跡',
            'description' => 'すべての収入を分類し、定期的な収入を自動的に追跡します。'
        ],
        'expense_management' => [
            'title' => '経費管理',
            'description' => '支出を管理し、支払いプランを簡単に管理します。'
        ],
        'savings_goals' => [
            'title' => '貯蓄目標',
            'description' => '経済的な目標を設定し、進捗状況を視覚的に追跡します。'
        ]
    ],

    'testimonials' => [
        'title' => 'お客様の声',
        '1' => [
            'text' => '"このアプリのおかげで、私は自分の財政状況をより良く管理することができます。 今、私のお金がどこに行くのかを知っています。"',
            'name' => 'Ahmet Y.',
            'title' => 'ソフトウェア開発者'
        ],
        '2' => [
            'text' => '"貯蓄目標を追跡することが非常に簡単になりました。 視覚的なグラフは私のモチベーションを高めます。"',
            'name' => 'Ayşe K.',
            'title' => '先生'
        ],
        '3' => [
            'text' => '"私はもはや定期的な支払いを逃しません。 リマインダーシステムは本当に私を助けてくれます。"',
            'name' => 'Mehmet S.',
            'title' => 'トレーダー'
        ]
    ],

    'cta' => [
        'title' => 'あなたの経済的な未来を形作る',
        'description' => '今すぐ無料アカウントを作成して、経済的なコントロールを手に入れましょう。',
        'button' => '無料ではじめる'
    ],

    // Doğrulama
    'required' => 'このフィールドは必須です',
    'min_length' => ':min 文字以上で入力してください',
    'max_length' => ':max 文字以下で入力してください',
    'email' => '有効なメールアドレスを入力してください',
    'match' => 'パスワードが一致しません',
    'unique' => 'この値はすでに使用されています',

    // Kimlik Doğrulama
    'password_confirm' => 'パスワードの確認',
    'forgot_password' => 'パスワードを忘れました',
    'login_success' => 'ログインに成功しました！',
    'logout_confirm' => 'ログアウトしてもよろしいですか？',
    'logout_success' => 'ログアウトに成功しました',
    'auth' => [
        'invalid_request' => '無効なリクエスト',
        'username_min_length' => 'ユーザー名は3文字以上で入力してください',
        'password_min_length' => 'パスワードは6文字以上で入力してください',
        'password_mismatch' => 'パスワードが一致しません',
        'username_taken' => 'このユーザー名はすでに使用されています',
        'register_success' => '登録に成功しました！',
        'register_error' => '登録中にエラーが発生しました',
        'database_error' => 'データベースエラーが発生しました',
        'credentials_required' => 'ユーザー名とパスワードが必要です',
        'login_success' => 'ログインに成功しました',
        'invalid_credentials' => '無効なユーザー名またはパスワード',
        'logout_success' => 'ログアウトに成功しました',
        'session_expired' => 'セッションの有効期限が切れました。もう一度ログインしてください',
        'account_locked' => 'あなたのアカウントはロックされています。 後でもう一度お試しください',
        'account_inactive' => 'あなたのアカウントはまだアクティブではありません',
        'remember_me' => 'ログイン状態を保存する',
        'forgot_password' => 'パスワードを忘れました',
        'reset_password' => 'パスワードをリセット',
        'reset_password_success' => 'パスワードリセットリンクがあなたのメールアドレスに送信されました',
        'reset_password_error' => 'パスワードのリセット中にエラーが発生しました'
    ],

    // Gelirler
    'incomes' => '収入',
    'add_income' => '新しい収入を追加',
    'edit_income' => '収入を編集',
    'income_name' => '収入名',
    'income_amount' => '金額',
    'income_date' => '最初の収入日',
    'income_category' => 'カテゴリー',
    'income_note' => 'ノート',
    'income_recurring' => '定期的な収入',
    'income_frequency' => '繰り返しの頻度',
    'income_end_date' => '終了日',
    'income' => [
        'title' => '収入',
        'add_success' => '収入が正常に追加されました',
        'add_error' => '収入の追加中にエラーが発生しました',
        'edit_success' => '収入が正常に更新されました',
        'edit_error' => '収入の更新中にエラーが発生しました',
        'delete_success' => '収入が正常に削除されました',
        'delete_error' => '収入の削除中にエラーが発生しました',
        'delete_confirm' => 'この収入を削除してもよろしいですか？',
        'mark_received' => [
            'success' => '収入は正常に受信済みとしてマークされました',
            'error' => '収入を受信済みとしてマークできませんでした'
        ],
        'mark_not_received' => '未受領としてマーク',
        'not_found' => 'まだ収入が追加されていません',
        'load_error' => '収入の読み込み中にエラーが発生しました',
        'update_error' => '収入の更新中にエラーが発生しました',
        'rate_error' => '為替レートを取得できませんでした',
        'id' => '収入ID',
        'name' => '収入名',
        'amount' => '金額',
        'currency' => '通貨',
        'date' => '日付',
        'frequency' => '繰り返しの頻度',
        'end_date' => '終了日',
        'status' => '状態',
        'next_date' => '次の日付',
        'total_amount' => '合計金額',
        'remaining_amount' => '残りの金額',
        'received_amount' => '受領金額',
        'pending_amount' => '保留中の金額',
        'recurring_info' => '繰り返しの情報',
        'recurring_count' => '繰り返しの回数',
        'recurring_total' => '繰り返しの合計',
        'recurring_remaining' => '残りの繰り返し',
        'recurring_completed' => '完了した繰り返し',
        'recurring_next' => '次の繰り返し',
        'recurring_last' => '最後の繰り返し'
    ],

    // Ödemeler
    'payments' => '支払い',
    'add_payment' => '新しい支払いを追加',
    'edit_payment' => '支払いを編集',
    'payment_name' => '支払い名',
    'payment_amount' => '金額',
    'payment_date' => '支払い日',
    'payment_category' => 'カテゴリー',
    'payment_note' => 'ノート',
    'payment_recurring' => '定期的な支払い',
    'payment_frequency' => '繰り返しの頻度',
    'payment_end_date' => '終了日',
    'payment' => [
        'title' => '支払い',
        'add_success' => '支払いが正常に追加されました',
        'add_error' => '支払いの追加中にエラーが発生しました',
        'add_recurring_error' => '定期的な支払いの追加中にエラーが発生しました',
        'edit_success' => '支払いが正常に更新されました',
        'edit_error' => '支払いの更新中にエラーが発生しました',
        'delete_success' => '支払いが正常に削除されました',
        'delete_error' => '支払いの削除中にエラーが発生しました',
        'delete_confirm' => 'この支払いを削除してもよろしいですか？',
        'mark_paid' => [
            'success' => '支払いは正常に支払い済みとしてマークされました',
            'error' => '支払いを支払い済みとしてマークできませんでした'
        ],
        'mark_not_paid' => '未払いとしてマーク',
        'not_found' => 'まだ支払いが追加されていません',
        'load_error' => '支払いの読み込み中にエラーが発生しました',
        'update_error' => '支払いの更新中にエラーが発生しました',
        'rate_error' => '為替レートを取得できませんでした',
        'id' => '支払いID',
        'name' => '支払い名',
        'amount' => '金額',
        'currency' => '通貨',
        'date' => '日付',
        'frequency' => '繰り返しの頻度',
        'end_date' => '終了日',
        'status' => '状態',
        'next_date' => '次の日付',
        'total_amount' => '合計金額',
        'remaining_amount' => '残りの金額',
        'paid_amount' => '支払われた金額',
        'pending_amount' => '保留中の金額',
        'recurring_info' => '繰り返しの情報',
        'recurring_count' => '繰り返しの回数',
        'recurring_total' => '繰り返しの合計',
        'recurring_remaining' => '残りの繰り返し',
        'recurring_completed' => '完了した繰り返し',
        'recurring_next' => '次の繰り返し',
        'recurring_last' => '最後の繰り返し',
        'transfer' => '翌月に転送',
        'recurring' => [
            'total_payment' => '合計支払い',
            'pending_payment' => '保留中の支払い'
        ],
        'buttons' => [
            'delete' => '削除',
            'edit' => '編集',
            'mark_paid' => '支払い済みとしてマーク',
            'mark_not_paid' => '未払いとしてマーク'
        ]
    ],

    // Birikimler
    'savings' => '貯蓄',
    'add_saving' => '新しい貯蓄を追加',
    'edit_saving' => '貯蓄を編集',
    'saving_name' => '貯蓄名',
    'target_amount' => '目標金額',
    'current_amount' => '現在の金額',
    'start_date' => '開始日',
    'target_date' => '目標日',
    'saving' => [
        'title' => '貯蓄',
        'add_success' => '貯蓄が正常に追加されました',
        'add_error' => '貯蓄の追加中にエラーが発生しました',
        'edit_success' => '貯蓄が正常に更新されました',
        'edit_error' => '貯蓄の更新中にエラーが発生しました',
        'delete_success' => '貯蓄が正常に削除されました',
        'delete_error' => '貯蓄の削除中にエラーが発生しました',
        'delete_confirm' => 'この貯蓄を削除してもよろしいですか？',
        'progress' => '進捗',
        'remaining' => '残りの金額',
        'remaining_days' => '残りの日数',
        'monthly_needed' => '毎月必要な金額',
        'completed' => '完了',
        'on_track' => '順調',
        'behind' => '遅れている',
        'ahead' => '先を行く',
        'load_error' => '貯蓄の読み込み中にエラーが発生しました',
        'not_found' => 'まだ貯蓄が追加されていません',
        'update_error' => '貯蓄の更新中にエラーが発生しました',
        'name' => '貯蓄名',
        'target_amount' => '目標金額',
        'current_amount' => '現在の金額',
        'currency' => '通貨',
        'start_date' => '開始日',
        'target_date' => '目標日',
        'status' => '状態',
        'progress_info' => '進捗情報',
        'daily_needed' => '毎日必要な金額',
        'weekly_needed' => '毎週必要な金額',
        'yearly_needed' => '毎年必要な金額',
        'completion_date' => '推定完了日',
        'completion_rate' => '完了率',
        'days_left' => '残りの日数',
        'days_total' => '合計日数',
        'days_passed' => '経過日数',
        'expected_progress' => '予想される進捗',
        'actual_progress' => '実際の進捗',
        'progress_difference' => '進捗の差',
        'update_amount' => '金額を更新',
        'update_details' => '詳細を更新'
    ],

    // Para Birimleri
    'currency' => '通貨',
    'base_currency' => '基本通貨',
    'exchange_rate' => '為替レート',
    'update_rate' => '現在の為替レートで更新',

    // Sıklık
    'frequency' => [
        'none' => '一度限り',
        'daily' => '毎日',
        'weekly' => '毎週',
        'monthly' => '毎月',
        'bimonthly' => '隔月',
        'quarterly' => '四半期ごと',
        'fourmonthly' => '4ヶ月ごと',
        'fivemonthly' => '5ヶ月ごと',
        'sixmonthly' => '6ヶ月ごと',
        'yearly' => '毎年'
    ],

    // Aylar
    'months' => [
        1 => '1月',
        2 => '2月',
        3 => '3月',
        4 => '4月',
        5 => '5月',
        6 => '6月',
        7 => '7月',
        8 => '8月',
        9 => '9月',
        10 => '10月',
        11 => '11月',
        12 => '12月'
    ],

    // Ayarlar
    'settings_title' => 'ユーザー設定',
    'theme' => 'テーマ',
    'theme_light' => 'ライトテーマ',
    'theme_dark' => 'ダークテーマ',
    'language' => '言語',
    'current_password' => '現在のパスワード',
    'new_password' => '新しいパスワード',
    'new_password_confirm' => '新しいパスワードの確認',

    // Hatalar
    'error' => 'エラー！',
    'success' => '成功！',
    'warning' => '警告！',
    'info' => '情報',
    'error_occurred' => 'エラーが発生しました',
    'try_again' => 'もう一度お試しください',
    'session_expired' => 'セッションの有効期限が切れました。もう一度ログインしてください。',
    'not_found' => 'ページが見つかりません',
    'unauthorized' => '不正なアクセス',
    'forbidden' => 'アクセスが拒否されました',

    // Yeni eklenen kısımlar
    'register' => [
        'title' => 'アカウントを作成',
        'error_message' => '登録中にエラーが発生しました。',
        'success' => '登録が完了しました！ ログインできます。',
        'username_taken' => 'このユーザー名はすでに使用されています。',
        'password_mismatch' => 'パスワードが一致しません。',
        'invalid_currency' => '無効な通貨の選択。',
        'required' => 'すべてのフィールドに入力してください。',
    ],

    // Currencies
    'currencies' => [
        'base_info' => 'すべての計算はこの通貨を使用して行われます。 心配しないでください、後で変更できます。',
        'try' => 'トルコリラ',
        'usd' => 'アメリカドル',
        'eur' => 'ユーロ',
        'gbp' => '英国ポンド'
    ],

    // Ayarlar
    'settings' => [
        'title' => 'ユーザー設定',
        'base_currency' => '基本通貨',
        'base_currency_info' => 'すべての計算はこの通貨を使用して行われます。',
        'theme' => 'テーマ',
        'theme_light' => 'ライトテーマ',
        'theme_dark' => 'ダークテーマ',
        'theme_info' => 'インターフェースの配色テーマの選択。',
        'language' => '言語',
        'language_info' => 'インターフェース言語の選択。',
        'save_success' => '設定が正常に保存されました',
        'save_error' => '設定の保存中にエラーが発生しました',
        'current_password' => '現在のパスワード',
        'new_password' => '新しいパスワード',
        'new_password_confirm' => '新しいパスワードの確認',
        'password_success' => 'パスワードが正常に変更されました',
        'password_error' => 'パスワードの変更中にエラーが発生しました',
        'password_mismatch' => '現在のパスワードが正しくありません',
        'password_same' => '新しいパスワードは古いパスワードと同じにすることはできません',
        'password_requirements' => 'パスワードは6文字以上である必要があります'
    ],

    // Uygulama
    'app' => [
        'previous_month' => '先月',
        'next_month' => '来月',
        'monthly_income' => '月収',
        'monthly_expense' => '月間支出',
        'net_balance' => '純残高',
        'period' => '期間',
        'next_income' => '次の収入',
        'next_payment' => '次の支払い',
        'payment_power' => '支払い能力',
        'installment_info' => '分割払い情報',
        'total' => '合計',
        'total_payment' => '合計支払い',
        'loading' => '読み込み中...',
        'no_data' => 'データが見つかりません',
        'confirm_delete' => '削除してもよろしいですか？',
        'yes_delete' => 'はい、削除',
        'no_cancel' => 'いいえ、キャンセル',
        'operation_success' => '操作は成功しました',
        'operation_error' => '操作中にエラーが発生しました',
        'save_success' => '正常に保存されました',
        'save_error' => '保存中にエラーが発生しました',
        'update_success' => '正常に更新されました',
        'update_error' => '更新中にエラーが発生しました',
        'delete_success' => '正常に削除されました',
        'delete_error' => '削除中にエラーが発生しました'
    ],

    // Para birimi işlemleri
    'currency' => [
        'invalid_request' => '無効なリクエスト',
        'invalid_currency' => '無効な通貨',
        'update_success' => '通貨が正常に更新されました',
        'update_error' => '通貨の更新中にエラーが発生しました',
        'database_error' => 'データベースエラーが発生しました',
        'currency_required' => '通貨の選択が必要です',
        'rate_fetched' => '為替レートが正常に取得されました',
        'rate_fetch_error' => '為替レートを取得できませんでした',
        'rate_not_found' => '為替レートが見つかりませんでした',
        'select_currency' => '通貨を選択',
        'current_rate' => '現在のレート',
        'conversion_rate' => '換算レート',
        'last_update' => '最終更新日',
        'auto_update' => '自動更新',
        'manual_update' => '手動更新',
        'update_daily' => '毎日更新',
        'update_weekly' => '毎週更新',
        'update_monthly' => '毎月更新',
        'update_never' => '更新しない'
    ],

    // Özet
    'summary' => [
        'title' => '概要',
        'load_error' => '概要情報の読み込み中にエラーが発生しました',
        'user_not_found' => 'ユーザーが見つかりません',
        'total_income' => '総収入',
        'total_expense' => '総支出',
        'net_balance' => '純残高',
        'positive_balance' => '正',
        'negative_balance' => '負',
        'monthly_summary' => '月次概要',
        'yearly_summary' => '年間概要',
        'income_vs_expense' => '収入/支出比率',
        'savings_progress' => '貯蓄の進捗',
        'payment_schedule' => '支払いスケジュール',
        'upcoming_payments' => '今後の支払い',
        'upcoming_incomes' => '今後の収入',
        'expense_percentage' => '支出の割合',
        'savings_percentage' => '貯蓄の割合',
        'monthly_trend' => '月次トレンド',
        'yearly_trend' => '年間トレンド',
        'balance_trend' => '残高トレンド',
        'expense_trend' => '支出トレンド',
        'income_trend' => '収入トレンド',
        'savings_trend' => '貯蓄トレンド',
        'budget_status' => '予算状況',
        'on_budget' => '予算内',
        'over_budget' => '予算超過',
        'under_budget' => '予算を下回る',
        'budget_warning' => '予算警告',
        'budget_alert' => '予算アラート',
        'expense_categories' => '支出カテゴリ',
        'income_sources' => '収入源',
        'savings_goals' => '貯蓄目標',
        'payment_methods' => '支払い方法',
        'recurring_transactions' => '定期的な取引',
        'financial_goals' => '財務目標',
        'goal_progress' => '目標の進捗',
        'goal_completion' => '目標の完了',
        'goal_status' => '目標の状態',
        'completed_goals' => '完了した目標',
        'active_goals' => 'アクティブな目標',
        'missed_goals' => '逃した目標',
        'goal_history' => '目標履歴'
    ],

    'transfer' => [
        'title' => '支払い転送',
        'confirm' => '未払いの支払いを翌月に転送してもよろしいですか？',
        'transfer_button' => 'はい、転送',
        'cancel_button' => 'キャンセル',
        'error' => '支払いの転送中にエラーが発生しました',
        'success' => '支払いが正常に転送されました',
        'no_unpaid_payments' => '転送する未払いの支払いが見つかりませんでした',
        'payment_transferred_from' => '%s (から転送 %s 月)',
        'update_error' => '支払いを更新できませんでした'
    ],

    'validation' => [
        'field_required' => '%s フィールドは必須です',
        'field_numeric' => '%s フィールドは数値である必要があります',
        'field_date' => '%s フィールドは有効な日付である必要があります (YYYY-MM-DD)',
        'field_currency' => '%s フィールドは有効な通貨である必要があります',
        'field_frequency' => '%s フィールドは有効な繰り返し頻度である必要があります',
        'field_min_value' => '%s フィールドは少なくとも %s である必要があります',
        'field_max_value' => '%s フィールドは最大 %s である必要があります',
        'date_range_error' => '開始日は終了日よりも大きくすることはできません',
        'invalid_format' => '無効な形式',
        'invalid_value' => '無効な値',
        'required_field' => 'このフィールドは必須です',
        'min_length' => '%s 文字以上である必要があります',
        'max_length' => '%s 文字以下である必要があります',
        'exact_length' => '正確に %s 文字である必要があります',
        'greater_than' => '%s より大きくなければなりません',
        'less_than' => '%s より小さくなければなりません',
        'between' => '%s と %s の間でなければなりません',
        'matches' => '%s と一致する必要があります',
        'different' => '%s と異なる必要があります',
        'unique' => 'この値はすでに使用されています',
        'valid_email' => '有効なメールアドレスを入力してください',
        'valid_url' => '有効な URL を入力してください',
        'valid_ip' => '有効な IP アドレスを入力してください',
        'valid_date' => '有効な日付を入力してください',
        'valid_time' => '有効な時刻を入力してください',
        'valid_datetime' => '有効な日付と時刻を入力してください',
        'alpha' => '文字のみを含める必要があります',
        'alpha_numeric' => '文字と数字のみを含める必要があります',
        'alpha_dash' => '文字、数字、ダッシュ、およびアンダースコアのみを含める必要があります',
        'numeric' => '数字のみを含める必要があります',
        'integer' => '整数である必要があります',
        'decimal' => '10進数である必要があります',
        'natural' => '正の整数である必要があります',
        'natural_no_zero' => 'ゼロより大きい正の整数である必要があります',
        'valid_base64' => '有効な Base64 値を入力してください',
        'valid_json' => '有効な JSON 値を入力してください',
        'valid_file' => '有効なファイルを選択してください',
        'valid_image' => '有効な画像ファイルを選択してください',
        'valid_phone' => '有効な電話番号を入力してください',
        'valid_credit_card' => '有効なクレジットカード番号を入力してください',
        'valid_color' => '有効なカラーコードを入力してください'
    ],

    // Utils
    'utils' => [
        'validation' => [
            'required' => ':field フィールドは必須です',
            'numeric' => ':field フィールドは数値である必要があります',
            'date' => ':field フィールドは有効な日付である必要があります',
            'currency' => ':field フィールドは有効な通貨である必要があります',
            'frequency' => ':field フィールドは有効な繰り返し頻度である必要があります',
            'min_value' => ':field フィールドは少なくとも :min である必要があります',
            'max_value' => ':field フィールドは最大 :max である必要があります',
            'error_title' => '検証エラー',
            'confirm_button' => 'OK'
        ],
        'session' => [
            'error_title' => 'セッションエラー',
            'invalid_token' => '無効なセキュリティトークン'
        ],
        'frequency' => [
            'none' => '繰り返しなし',
            'monthly' => '毎月',
            'bimonthly' => '2ヶ月ごと',
            'quarterly' => '3ヶ月ごと',
            'fourmonthly' => '4ヶ月ごと',
            'fivemonthly' => '5ヶ月ごと',
            'sixmonthly' => '6ヶ月ごと',
            'yearly' => '毎年'
        ],
        'form' => [
            'income_name' => '収入名',
            'payment_name' => '支払い名',
            'amount' => '金額',
            'currency' => '通貨',
            'date' => '日付',
            'frequency' => '繰り返しの頻度',
            'saving_name' => '貯蓄名',
            'target_amount' => '目標金額',
            'current_amount' => '現在の金額',
            'start_date' => '開始日',
            'target_date' => '目標日'
        ]
    ],

    'user' => [
        'not_found' => 'ユーザー情報が見つかりません',
        'update_success' => 'ユーザー情報が正常に更新されました',
        'update_error' => 'ユーザー情報を更新できませんでした'
    ],
];
