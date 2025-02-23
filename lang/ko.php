<?php
return [
    'language_name' => '한국어',
    // Genel
    'site_name' => '예산 추적기',
    'site_description' => '개인 재정 관리를 간소화하는 최신 솔루션',
    'welcome' => '환영합니다',
    'logout' => '로그아웃',
    'save' => '저장',
    'cancel' => '취소',
    'delete' => '삭제',
    'edit' => '편집',
    'update' => '업데이트',
    'yes' => '예',
    'no' => '아니요',
    'confirm' => '확인',
    'go_to_app' => '앱으로 이동',

    // Giriş/Kayıt
    'username' => '사용자 이름',
    'password' => '비밀번호',
    'remember_me' => '로그인 유지',
    'login' => [
        'title' => '로그인',
        'error_message' => '잘못된 사용자 이름 또는 비밀번호입니다.',
        'no_account' => '계정이 없으신가요? 무료 계정 만들기',
        'success' => '로그인 성공! 리디렉션 중...',
        'error' => '로그인하는 동안 오류가 발생했습니다.',
        'required' => '사용자 이름과 비밀번호를 입력하세요.',
        'invalid' => '잘못된 사용자 이름 또는 비밀번호입니다.',
        'locked' => '귀하의 계정이 잠겼습니다. 나중에 다시 시도하십시오.',
        'inactive' => '귀하의 계정이 아직 활성화되지 않았습니다. 이메일을 확인하세요.',
        'have_account' => '계정이 있으신가요? 로그인'
    ],

    // Footer
    'footer' => [
        'links' => '링크',
        'contact' => '연락처',
        'copyright' => '모든 권리 보유.'
    ],

    // Ana Sayfa
    'hero' => [
        'title' => '재정적 자유를 관리하세요',
        'description' => '수입, 지출 및 저축을 쉽게 추적하세요. 재정 목표를 달성하는 것이 그 어느 때보다 쉬워졌습니다.',
        'cta' => '지금 시작하기'
    ],

    'features' => [
        'title' => '기능',
        'income_tracking' => [
            'title' => '수입 추적',
            'description' => '모든 수입을 분류하고 정기적인 수입을 자동으로 추적합니다.'
        ],
        'expense_management' => [
            'title' => '지출 관리',
            'description' => '지출을 통제하고 지불 계획을 쉽게 관리하세요.'
        ],
        'savings_goals' => [
            'title' => '저축 목표',
            'description' => '재정 목표를 설정하고 진행 상황을 시각적으로 추적하세요.'
        ]
    ],

    'testimonials' => [
        'title' => '추천사',
        '1' => [
            'text' => '"이 앱 덕분에 재정 상황을 훨씬 더 잘 관리할 수 있습니다. 이제 내 돈이 어디로 가는지 알 수 있습니다."',
            'name' => 'Ahmet Y.',
            'title' => '소프트웨어 개발자'
        ],
        '2' => [
            'text' => '"저축 목표를 추적하는 것이 이제 매우 쉽습니다. 시각적 그래프는 동기 부여를 높여줍니다."',
            'name' => 'Ayşe K.',
            'title' => '선생님'
        ],
        '3' => [
            'text' => '"더 이상 정기적인 지불을 놓치지 않습니다. 알림 시스템이 정말 많은 도움이 됩니다."',
            'name' => 'Mehmet S.',
            'title' => '상인'
        ]
    ],

    'cta' => [
        'title' => '재정적 미래를 만들어보세요',
        'description' => '지금 무료 계정을 만들고 재정적 통제권을 확보하세요.',
        'button' => '무료로 시작하기'
    ],

    // Doğrulama
    'required' => '이 필드는 필수 입력 사항입니다',
    'min_length' => '최소 :min자 이상이어야 합니다',
    'max_length' => '최대 :max자 이하여야 합니다',
    'email' => '유효한 이메일 주소를 입력하세요',
    'match' => '비밀번호가 일치하지 않습니다',
    'unique' => '이 값은 이미 사용 중입니다',

    // Kimlik Doğrulama
    'password_confirm' => '비밀번호 확인',
    'forgot_password' => '비밀번호를 잊으셨나요',
    'login_success' => '로그인 성공!',
    'logout_confirm' => '로그아웃하시겠습니까?',
    'logout_success' => '로그아웃 성공',
    'auth' => [
        'invalid_request' => '잘못된 요청',
        'username_min_length' => '사용자 이름은 3자 이상이어야 합니다',
        'password_min_length' => '비밀번호는 6자 이상이어야 합니다',
        'password_mismatch' => '비밀번호가 일치하지 않습니다',
        'username_taken' => '이 사용자 이름은 이미 사용 중입니다',
        'register_success' => '등록 성공!',
        'register_error' => '등록하는 동안 오류가 발생했습니다',
        'database_error' => '데이터베이스 오류가 발생했습니다',
        'credentials_required' => '사용자 이름과 비밀번호가 필요합니다',
        'login_success' => '로그인 성공',
        'invalid_credentials' => '잘못된 사용자 이름 또는 비밀번호',
        'logout_success' => '로그아웃 성공',
        'session_expired' => '세션이 만료되었습니다. 다시 로그인하세요',
        'account_locked' => '귀하의 계정이 잠겼습니다. 나중에 다시 시도하십시오',
        'account_inactive' => '귀하의 계정이 아직 활성화되지 않았습니다',
        'remember_me' => '로그인 유지',
        'forgot_password' => '비밀번호를 잊으셨나요',
        'reset_password' => '비밀번호 재설정',
        'reset_password_success' => '비밀번호 재설정 링크가 이메일 주소로 전송되었습니다',
        'reset_password_error' => '비밀번호를 재설정하는 동안 오류가 발생했습니다'
    ],

    // Gelirler
    'incomes' => '수입',
    'add_income' => '새 수입 추가',
    'edit_income' => '수입 편집',
    'income_name' => '수입 이름',
    'income_amount' => '금액',
    'income_date' => '첫 수입 날짜',
    'income_category' => '범주',
    'income_note' => '메모',
    'income_recurring' => '반복 수입',
    'income_frequency' => '반복 빈도',
    'income_end_date' => '종료 날짜',
    'income' => [
        'title' => '수입',
        'add_success' => '수입이 성공적으로 추가되었습니다',
        'add_error' => '수입을 추가하는 동안 오류가 발생했습니다',
        'edit_success' => '수입이 성공적으로 업데이트되었습니다',
        'edit_error' => '수입을 업데이트하는 동안 오류가 발생했습니다',
        'delete_success' => '수입이 성공적으로 삭제되었습니다',
        'delete_error' => '수입을 삭제하는 동안 오류가 발생했습니다',
        'delete_confirm' => '이 수입을 삭제하시겠습니까?',
        'mark_received' => [
            'success' => '수입이 수신됨으로 성공적으로 표시되었습니다',
            'error' => '수입을 수신됨으로 표시할 수 없습니다'
        ],
        'mark_not_received' => '수신되지 않음으로 표시',
        'not_found' => '아직 수입이 추가되지 않았습니다',
        'load_error' => '수입을 로드하는 동안 오류가 발생했습니다',
        'update_error' => '수입을 업데이트하는 동안 오류가 발생했습니다',
        'rate_error' => '환율을 검색할 수 없습니다',
        'id' => '수입 ID',
        'name' => '수입 이름',
        'amount' => '금액',
        'currency' => '통화',
        'date' => '날짜',
        'frequency' => '반복 빈도',
        'end_date' => '종료 날짜',
        'status' => '상태',
        'next_date' => '다음 날짜',
        'total_amount' => '총 금액',
        'remaining_amount' => '남은 금액',
        'received_amount' => '수신 금액',
        'pending_amount' => '보류 중인 금액',
        'recurring_info' => '반복 정보',
        'recurring_count' => '반복 횟수',
        'recurring_total' => '총 반복 횟수',
        'recurring_remaining' => '남은 반복 횟수',
        'recurring_completed' => '완료된 반복 횟수',
        'recurring_next' => '다음 반복',
        'recurring_last' => '마지막 반복'
    ],

    // Ödemeler
    'payments' => '지불',
    'add_payment' => '새 지불 추가',
    'edit_payment' => '지불 편집',
    'payment_name' => '지불 이름',
    'payment_amount' => '금액',
    'payment_date' => '지불 날짜',
    'payment_category' => '범주',
    'payment_note' => '메모',
    'payment_recurring' => '반복 지불',
    'payment_frequency' => '반복 빈도',
    'payment_end_date' => '종료 날짜',
    'payment' => [
        'title' => '지불',
        'add_success' => '지불이 성공적으로 추가되었습니다',
        'add_error' => '지불을 추가하는 동안 오류가 발생했습니다',
        'add_recurring_error' => '반복 지불을 추가하는 동안 오류가 발생했습니다',
        'edit_success' => '지불이 성공적으로 업데이트되었습니다',
        'edit_error' => '지불을 업데이트하는 동안 오류가 발생했습니다',
        'delete_success' => '지불이 성공적으로 삭제되었습니다',
        'delete_error' => '지불을 삭제하는 동안 오류가 발생했습니다',
        'delete_confirm' => '이 지불을 삭제하시겠습니까?',
        'mark_paid' => [
            'success' => '지불이 지불됨으로 성공적으로 표시되었습니다',
            'error' => '지불을 지불됨으로 표시할 수 없습니다'
        ],
        'mark_not_paid' => '미지불로 표시',
        'not_found' => '아직 지불이 추가되지 않았습니다',
        'load_error' => '지불을 로드하는 동안 오류가 발생했습니다',
        'update_error' => '지불을 업데이트하는 동안 오류가 발생했습니다',
        'rate_error' => '환율을 검색할 수 없습니다',
        'id' => '지불 ID',
        'name' => '지불 이름',
        'amount' => '금액',
        'currency' => '통화',
        'date' => '날짜',
        'frequency' => '반복 빈도',
        'end_date' => '종료 날짜',
        'status' => '상태',
        'next_date' => '다음 날짜',
        'total_amount' => '총 금액',
        'remaining_amount' => '남은 금액',
        'paid_amount' => '지불된 금액',
        'pending_amount' => '보류 중인 금액',
        'recurring_info' => '반복 정보',
        'recurring_count' => '반복 횟수',
        'recurring_total' => '총 반복 횟수',
        'recurring_remaining' => '남은 반복 횟수',
        'recurring_completed' => '완료된 반복 횟수',
        'recurring_next' => '다음 반복',
        'recurring_last' => '마지막 반복',
        'transfer' => '다음 달로 이월',
        'recurring' => [
            'total_payment' => '총 지불액',
            'pending_payment' => '보류 중인 지불'
        ],
        'buttons' => [
            'delete' => '삭제',
            'edit' => '편집',
            'mark_paid' => '지불됨으로 표시',
            'mark_not_paid' => '미지불로 표시'
        ]
    ],

    // Birikimler
    'savings' => '저축',
    'add_saving' => '새 저축 추가',
    'edit_saving' => '저축 편집',
    'saving_name' => '저축 이름',
    'target_amount' => '목표 금액',
    'current_amount' => '현재 금액',
    'start_date' => '시작 날짜',
    'target_date' => '목표 날짜',
    'saving' => [
        'title' => '저축',
        'add_success' => '저축이 성공적으로 추가되었습니다',
        'add_error' => '저축을 추가하는 동안 오류가 발생했습니다',
        'edit_success' => '저축이 성공적으로 업데이트되었습니다',
        'edit_error' => '저축을 업데이트하는 동안 오류가 발생했습니다',
        'delete_success' => '저축이 성공적으로 삭제되었습니다',
        'delete_error' => '저축을 삭제하는 동안 오류가 발생했습니다',
        'delete_confirm' => '이 저축을 삭제하시겠습니까?',
        'progress' => '진행',
        'remaining' => '남은 금액',
        'remaining_days' => '남은 일수',
        'monthly_needed' => '매월 필요한 금액',
        'completed' => '완료됨',
        'on_track' => '순조롭게 진행 중',
        'behind' => '뒤쳐짐',
        'ahead' => '앞서감',
        'load_error' => '저축을 로드하는 동안 오류가 발생했습니다',
        'not_found' => '아직 저축이 추가되지 않았습니다',
        'update_error' => '저축을 업데이트하는 동안 오류가 발생했습니다',
        'name' => '저축 이름',
        'target_amount' => '목표 금액',
        'current_amount' => '현재 금액',
        'currency' => '통화',
        'start_date' => '시작 날짜',
        'target_date' => '목표 날짜',
        'status' => '상태',
        'progress_info' => '진행 정보',
        'daily_needed' => '매일 필요한 금액',
        'weekly_needed' => '매주 필요한 금액',
        'yearly_needed' => '매년 필요한 금액',
        'completion_date' => '예상 완료일',
        'completion_rate' => '완료율',
        'days_left' => '남은 일수',
        'days_total' => '총 일수',
        'days_passed' => '경과된 일수',
        'expected_progress' => '예상 진행률',
        'actual_progress' => '실제 진행률',
        'progress_difference' => '진행률 차이',
        'update_amount' => '금액 업데이트',
        'update_details' => '세부 정보 업데이트'
    ],

    // Para Birimleri
    'currency' => '통화',
    'base_currency' => '기준 통화',
    'exchange_rate' => '환율',
    'update_rate' => '현재 환율로 업데이트',

    // Sıklık
    'frequency' => [
        'none' => '한 번',
        'daily' => '매일',
        'weekly' => '매주',
        'monthly' => '매월',
        'bimonthly' => '격월',
        'quarterly' => '분기별',
        'fourmonthly' => '4개월마다',
        'fivemonthly' => '5개월마다',
        'sixmonthly' => '6개월마다',
        'yearly' => '매년'
    ],

    // Aylar
    'months' => [
        1 => '1월',
        2 => '2월',
        3 => '3월',
        4 => '4월',
        5 => '5월',
        6 => '6월',
        7 => '7월',
        8 => '8월',
        9 => '9월',
        10 => '10월',
        11 => '11월',
        12 => '12월'
    ],

    // Ayarlar
    'settings_title' => '사용자 설정',
    'theme' => '테마',
    'theme_light' => '밝은 테마',
    'theme_dark' => '어두운 테마',
    'language' => '언어',
    'current_password' => '현재 비밀번호',
    'new_password' => '새 비밀번호',
    'new_password_confirm' => '새 비밀번호 확인',

    // Hatalar
    'error' => '오류!',
    'success' => '성공!',
    'warning' => '경고!',
    'info' => '정보',
    'error_occurred' => '오류가 발생했습니다',
    'try_again' => '다시 시도하십시오',
    'session_expired' => '세션이 만료되었습니다. 다시 로그인하세요.',
    'not_found' => '페이지를 찾을 수 없습니다',
    'unauthorized' => '권한 없는 액세스',
    'forbidden' => '액세스가 금지되었습니다',

    // Yeni eklenen kısımlar
    'register' => [
        'title' => '계정 만들기',
        'error_message' => '등록하는 동안 오류가 발생했습니다.',
        'success' => '등록 성공! 로그인할 수 있습니다.',
        'username_taken' => '이 사용자 이름은 이미 사용 중입니다.',
        'password_mismatch' => '비밀번호가 일치하지 않습니다.',
        'invalid_currency' => '잘못된 통화 선택.',
        'required' => '모든 필드를 채워주세요.',
    ],

    // Currencies
    'currencies' => [
        'base_info' => '모든 계산은 이 통화를 사용하여 수행됩니다. 걱정하지 마세요. 나중에 변경할 수 있습니다.',
        'try' => '터키 리라',
        'usd' => '미국 달러',
        'eur' => '유로',
        'gbp' => '영국 파운드'
    ],

    // Ayarlar
    'settings' => [
        'title' => '사용자 설정',
        'base_currency' => '기준 통화',
        'base_currency_info' => '모든 계산은 이 통화를 사용하여 수행됩니다.',
        'theme' => '테마',
        'theme_light' => '밝은 테마',
        'theme_dark' => '어두운 테마',
        'theme_info' => '인터페이스 색상 테마 선택.',
        'language' => '언어',
        'language_info' => '인터페이스 언어 선택.',
        'save_success' => '설정이 성공적으로 저장되었습니다',
        'save_error' => '설정을 저장하는 동안 오류가 발생했습니다',
        'current_password' => '현재 비밀번호',
        'new_password' => '새 비밀번호',
        'new_password_confirm' => '새 비밀번호 확인',
        'password_success' => '비밀번호가 성공적으로 변경되었습니다',
        'password_error' => '비밀번호를 변경하는 동안 오류가 발생했습니다',
        'password_mismatch' => '현재 비밀번호가 잘못되었습니다',
        'password_same' => '새 비밀번호는 이전 비밀번호와 동일할 수 없습니다',
        'password_requirements' => '비밀번호는 6자 이상이어야 합니다'
    ],

    // Uygulama
    'app' => [
        'previous_month' => '이전 달',
        'next_month' => '다음 달',
        'monthly_income' => '월간 수입',
        'monthly_expense' => '월간 지출',
        'net_balance' => '순 잔액',
        'period' => '기간',
        'next_income' => '다음 수입',
        'next_payment' => '다음 지불',
        'payment_power' => '지불 능력',
        'installment_info' => '할부 정보',
        'total' => '총액',
        'total_payment' => '총 지불액',
        'loading' => '로드 중...',
        'no_data' => '데이터가 없습니다',
        'confirm_delete' => '삭제하시겠습니까?',
        'yes_delete' => '예, 삭제',
        'no_cancel' => '아니요, 취소',
        'operation_success' => '작업 성공',
        'operation_error' => '작업 중 오류가 발생했습니다',
        'save_success' => '성공적으로 저장되었습니다',
        'save_error' => '저장하는 동안 오류가 발생했습니다',
        'update_success' => '성공적으로 업데이트되었습니다',
        'update_error' => '업데이트하는 동안 오류가 발생했습니다',
        'delete_success' => '성공적으로 삭제되었습니다',
        'delete_error' => '삭제하는 동안 오류가 발생했습니다'
    ],

    // Para birimi işlemleri
    'currency' => [
        'invalid_request' => '잘못된 요청',
        'invalid_currency' => '잘못된 통화',
        'update_success' => '통화가 성공적으로 업데이트되었습니다',
        'update_error' => '통화를 업데이트하는 동안 오류가 발생했습니다',
        'database_error' => '데이터베이스 오류가 발생했습니다',
        'currency_required' => '통화 선택이 필요합니다',
        'rate_fetched' => '환율을 성공적으로 가져왔습니다',
        'rate_fetch_error' => '환율을 검색할 수 없습니다',
        'rate_not_found' => '환율을 찾을 수 없습니다',
        'select_currency' => '통화 선택',
        'current_rate' => '현재 환율',
        'conversion_rate' => '변환율',
        'last_update' => '최종 업데이트',
        'auto_update' => '자동 업데이트',
        'manual_update' => '수동 업데이트',
        'update_daily' => '매일 업데이트',
        'update_weekly' => '매주 업데이트',
        'update_monthly' => '매월 업데이트',
        'update_never' => '업데이트 안 함'
    ],

    // Özet
    'summary' => [
        'title' => '요약',
        'load_error' => '요약 정보를 로드하는 동안 오류가 발생했습니다',
        'user_not_found' => '사용자를 찾을 수 없습니다',
        'total_income' => '총 수입',
        'total_expense' => '총 지출',
        'net_balance' => '순 잔액',
        'positive_balance' => '양수',
        'negative_balance' => '음수',
        'monthly_summary' => '월간 요약',
        'yearly_summary' => '연간 요약',
        'income_vs_expense' => '수입/지출 비율',
        'savings_progress' => '저축 진행률',
        'payment_schedule' => '지불 일정',
        'upcoming_payments' => '예정된 지불',
        'upcoming_incomes' => '예정된 수입',
        'expense_percentage' => '지출 비율',
        'savings_percentage' => '저축 비율',
        'monthly_trend' => '월별 추세',
        'yearly_trend' => '연간 추세',
        'balance_trend' => '잔액 추세',
        'expense_trend' => '지출 추세',
        'income_trend' => '수입 추세',
        'savings_trend' => '저축 추세',
        'budget_status' => '예산 상태',
        'on_budget' => '예산 내',
        'over_budget' => '예산 초과',
        'under_budget' => '예산 미만',
        'budget_warning' => '예산 경고',
        'budget_alert' => '예산 알림',
        'expense_categories' => '지출 카테고리',
        'income_sources' => '수입원',
        'savings_goals' => '저축 목표',
        'payment_methods' => '지불 방법',
        'recurring_transactions' => '반복 거래',
        'financial_goals' => '재정 목표',
        'goal_progress' => '목표 진행률',
        'goal_completion' => '목표 완료',
        'goal_status' => '목표 상태',
        'completed_goals' => '완료된 목표',
        'active_goals' => '활성 목표',
        'missed_goals' => '놓친 목표',
        'goal_history' => '목표 기록'
    ],

    'transfer' => [
        'title' => '지불 이체',
        'confirm' => '미지불 지불금을 다음 달로 이월하시겠습니까?',
        'transfer_button' => '예, 이체',
        'cancel_button' => '취소',
        'error' => '지불금을 이체하는 동안 오류가 발생했습니다',
        'success' => '지불금이 성공적으로 이체되었습니다',
        'no_unpaid_payments' => '이체할 미지불 지불금이 없습니다',
        'payment_transferred_from' => '%s (에서 이체됨 %s 월)',
        'update_error' => '지불금을 업데이트할 수 없습니다'
    ],

    'validation' => [
        'field_required' => '%s 필드는 필수 입력 사항입니다',
        'field_numeric' => '%s 필드는 숫자여야 합니다',
        'field_date' => '%s 필드는 유효한 날짜여야 합니다 (YYYY-MM-DD)',
        'field_currency' => '%s 필드는 유효한 통화여야 합니다',
        'field_frequency' => '%s 필드는 유효한 반복 빈도여야 합니다',
        'field_min_value' => '%s 필드는 최소 %s이어야 합니다',
        'field_max_value' => '%s 필드는 최대 %s이어야 합니다',
        'date_range_error' => '시작 날짜는 종료 날짜보다 클 수 없습니다',
        'invalid_format' => '잘못된 형식',
        'invalid_value' => '잘못된 값',
        'required_field' => '이 필드는 필수 입력 사항입니다',
        'min_length' => '최소 %s자 이상이어야 합니다',
        'max_length' => '최대 %s자 이하여야 합니다',
        'exact_length' => '정확히 %s자여야 합니다',
        'greater_than' => '%s보다 커야 합니다',
        'less_than' => '%s보다 작아야 합니다',
        'between' => '%s와 %s 사이에 있어야 합니다',
        'matches' => '%s와 일치해야 합니다',
        'different' => '%s와 달라야 합니다',
        'unique' => '이 값은 이미 사용 중입니다',
        'valid_email' => '유효한 이메일 주소를 입력하세요',
        'valid_url' => '유효한 URL을 입력하세요',
        'valid_ip' => '유효한 IP 주소를 입력하세요',
        'valid_date' => '유효한 날짜를 입력하세요',
        'valid_time' => '유효한 시간을 입력하세요',
        'valid_datetime' => '유효한 날짜와 시간을 입력하세요',
        'alpha' => '문자만 포함해야 합니다',
        'alpha_numeric' => '문자와 숫자만 포함해야 합니다',
        'alpha_dash' => '문자, 숫자, 대시 및 밑줄만 포함해야 합니다',
        'numeric' => '숫자만 포함해야 합니다',
        'integer' => '정수여야 합니다',
        'decimal' => '소수여야 합니다',
        'natural' => '양의 정수여야 합니다',
        'natural_no_zero' => '0보다 큰 양의 정수여야 합니다',
        'valid_base64' => '유효한 Base64 값을 입력하세요',
        'valid_json' => '유효한 JSON 값을 입력하세요',
        'valid_file' => '유효한 파일을 선택하세요',
        'valid_image' => '유효한 이미지 파일을 선택하세요',
        'valid_phone' => '유효한 전화번호를 입력하세요',
        'valid_credit_card' => '유효한 신용 카드 번호를 입력하세요',
        'valid_color' => '유효한 색상 코드를 입력하세요'
    ],

    // Utils
    'utils' => [
        'validation' => [
            'required' => ':field 필드는 필수 입력 사항입니다',
            'numeric' => ':field 필드는 숫자여야 합니다',
            'date' => ':field 필드는 유효한 날짜여야 합니다',
            'currency' => ':field 필드는 유효한 통화여야 합니다',
            'frequency' => ':field 필드는 유효한 반복 빈도여야 합니다',
            'min_value' => ':field 필드는 최소 :min이어야 합니다',
            'max_value' => ':field 필드는 최대 :max이어야 합니다',
            'error_title' => '유효성 검사 오류',
            'confirm_button' => '확인'
        ],
        'session' => [
            'error_title' => '세션 오류',
            'invalid_token' => '잘못된 보안 토큰'
        ],
        'frequency' => [
            'none' => '반복 없음',
            'monthly' => '매월',
            'bimonthly' => '격월',
            'quarterly' => '분기별',
            'fourmonthly' => '4개월마다',
            'fivemonthly' => '5개월마다',
            'sixmonthly' => '6개월마다',
            'yearly' => '매년'
        ],
        'form' => [
            'income_name' => '수입 이름',
            'payment_name' => '지불 이름',
            'amount' => '금액',
            'currency' => '통화',
            'date' => '날짜',
            'frequency' => '반복 빈도',
            'saving_name' => '저축 이름',
            'target_amount' => '목표 금액',
            'current_amount' => '현재 금액',
            'start_date' => '시작 날짜',
            'target_date' => '목표 날짜'
        ]
    ],

    'user' => [
        'not_found' => '사용자 정보를 찾을 수 없습니다',
        'update_success' => '사용자 정보가 성공적으로 업데이트되었습니다',
        'update_error' => '사용자 정보를 업데이트할 수 없습니다'
    ],
];
