<?php
return [
    'language_name' => '简体中文',
    // Genel
    'site_name' => '预算追踪器',
    'site_description' => '简化个人财务管理的现代解决方案',
    'welcome' => '欢迎',
    'logout' => '登出',
    'save' => '保存',
    'cancel' => '取消',
    'delete' => '删除',
    'edit' => '编辑',
    'update' => '更新',
    'yes' => '是',
    'no' => '否',
    'confirm' => '确认',
    'go_to_app' => '前往应用',

    // Giriş/Kayıt
    'username' => '用户名',
    'password' => '密码',
    'remember_me' => '记住我',
    'login' => [
        'title' => '登录',
        'error_message' => '无效的用户名或密码。',
        'no_account' => '没有账户？创建一个免费账户',
        'success' => '登录成功！正在重定向...',
        'error' => '登录时发生错误。',
        'required' => '请输入您的用户名和密码。',
        'invalid' => '无效的用户名或密码。',
        'locked' => '您的账户已被锁定。请稍后再试。',
        'inactive' => '您的账户尚未激活。请检查您的电子邮件。',
        'have_account' => '已有账户？登录'
    ],

    // Footer
    'footer' => [
        'links' => '链接',
        'contact' => '联系方式',
        'copyright' => '版权所有。'
    ],

    // Ana Sayfa
    'hero' => [
        'title' => '管理您的财务自由',
        'description' => '轻松追踪您的收入、支出和储蓄。实现您的财务目标从未如此简单。',
        'cta' => '立即开始'
    ],

    'features' => [
        'title' => '功能',
        'income_tracking' => [
            'title' => '收入追踪',
            'description' => '对您的所有收入进行分类，并自动追踪您的定期收入。'
        ],
        'expense_management' => [
            'title' => '支出管理',
            'description' => '控制您的支出并轻松管理您的付款计划。'
        ],
        'savings_goals' => [
            'title' => '储蓄目标',
            'description' => '设定您的财务目标并以可视化方式追踪您的进度。'
        ]
    ],

    'testimonials' => [
        'title' => '评价',
        '1' => [
            'text' => '“感谢这个应用程序，我可以更好地控制我的财务状况。现在我知道每一分钱都花在哪里了。”',
            'name' => 'Ahmet Y.',
            'title' => '软件开发者'
        ],
        '2' => [
            'text' => '“追踪我的储蓄目标现在非常容易。可视化图表提高了我的积极性。”',
            'name' => 'Ayşe K.',
            'title' => '教师'
        ],
        '3' => [
            'text' => '“我再也不会错过我的定期付款了。提醒系统真的帮了我很多。”',
            'name' => 'Mehmet S.',
            'title' => '商人'
        ]
    ],

    'cta' => [
        'title' => '塑造您的财务未来',
        'description' => '立即创建一个免费账户并掌控财务。',
        'button' => '免费开始'
    ],

    // Doğrulama
    'required' => '此字段为必填项',
    'min_length' => '必须至少 :min 个字符',
    'max_length' => '必须最多 :max 个字符',
    'email' => '请输入有效的电子邮件地址',
    'match' => '密码不匹配',
    'unique' => '此值已被使用',

    // Kimlik Doğrulama
    'password_confirm' => '密码确认',
    'forgot_password' => '忘记密码',
    'login_success' => '登录成功！',
    'logout_confirm' => '您确定要登出吗？',
    'logout_success' => '成功登出',
    'auth' => [
        'invalid_request' => '无效的请求',
        'username_min_length' => '用户名必须至少 3 个字符',
        'password_min_length' => '密码必须至少 6 个字符',
        'password_mismatch' => '密码不匹配',
        'username_taken' => '此用户名已被使用',
        'register_success' => '注册成功！',
        'register_error' => '注册时发生错误',
        'database_error' => '发生数据库错误',
        'credentials_required' => '需要用户名和密码',
        'login_success' => '登录成功',
        'invalid_credentials' => '无效的用户名或密码',
        'logout_success' => '登出成功',
        'session_expired' => '您的会话已过期，请重新登录',
        'account_locked' => '您的账户已被锁定，请稍后再试',
        'account_inactive' => '您的账户尚未激活',
        'remember_me' => '记住我',
        'forgot_password' => '忘记密码',
        'reset_password' => '重置密码',
        'reset_password_success' => '密码重置链接已发送至您的电子邮件地址',
        'reset_password_error' => '重置密码时发生错误'
    ],

    // Gelirler
    'incomes' => '收入',
    'add_income' => '添加新收入',
    'edit_income' => '编辑收入',
    'income_name' => '收入名称',
    'income_amount' => '金额',
    'income_date' => '首次收入日期',
    'income_category' => '类别',
    'income_note' => '备注',
    'income_recurring' => '定期收入',
    'income_frequency' => '重复频率',
    'income_end_date' => '结束日期',
    'income' => [
        'title' => '收入',
        'add_success' => '收入添加成功',
        'add_error' => '添加收入时发生错误',
        'edit_success' => '收入更新成功',
        'edit_error' => '更新收入时发生错误',
        'delete_success' => '收入删除成功',
        'delete_error' => '删除收入时发生错误',
        'delete_confirm' => '您确定要删除此收入吗？',
        'mark_received' => [
            'success' => '收入已成功标记为已收到',
            'error' => '收入无法标记为已收到'
        ],
        'mark_not_received' => '标记为未收到',
        'not_found' => '尚未添加任何收入',
        'load_error' => '加载收入时发生错误',
        'update_error' => '更新收入时发生错误',
        'rate_error' => '无法检索汇率',
        'id' => '收入 ID',
        'name' => '收入名称',
        'amount' => '金额',
        'currency' => '货币',
        'date' => '日期',
        'frequency' => '重复频率',
        'end_date' => '结束日期',
        'status' => '状态',
        'next_date' => '下一次日期',
        'total_amount' => '总金额',
        'remaining_amount' => '剩余金额',
        'received_amount' => '已收到金额',
        'pending_amount' => '待处理金额',
        'recurring_info' => '重复信息',
        'recurring_count' => '重复计数',
        'recurring_total' => '总重复次数',
        'recurring_remaining' => '剩余重复次数',
        'recurring_completed' => '已完成重复次数',
        'recurring_next' => '下一次重复',
        'recurring_last' => '上一次重复'
    ],

    // Ödemeler
    'payments' => '支付',
    'add_payment' => '添加新支付',
    'edit_payment' => '编辑支付',
    'payment_name' => '支付名称',
    'payment_amount' => '金额',
    'payment_date' => '支付日期',
    'payment_category' => '类别',
    'payment_note' => '备注',
    'payment_recurring' => '定期支付',
    'payment_frequency' => '重复频率',
    'payment_end_date' => '结束日期',
    'payment' => [
        'title' => '支付',
        'add_success' => '支付添加成功',
        'add_error' => '添加支付时发生错误',
        'add_recurring_error' => '添加定期支付时发生错误',
        'edit_success' => '支付更新成功',
        'edit_error' => '更新支付时发生错误',
        'delete_success' => '支付删除成功',
        'delete_error' => '删除支付时发生错误',
        'delete_confirm' => '您确定要删除此支付吗？',
        'mark_paid' => [
            'success' => '支付已成功标记为已支付',
            'error' => '支付无法标记为已支付'
        ],
        'mark_not_paid' => '标记为未支付',
        'not_found' => '尚未添加任何支付',
        'load_error' => '加载支付时发生错误',
        'update_error' => '更新支付时发生错误',
        'rate_error' => '无法检索汇率',
        'id' => '支付 ID',
        'name' => '支付名称',
        'amount' => '金额',
        'currency' => '货币',
        'date' => '日期',
        'frequency' => '重复频率',
        'end_date' => '结束日期',
        'status' => '状态',
        'next_date' => '下一次日期',
        'total_amount' => '总金额',
        'remaining_amount' => '剩余金额',
        'paid_amount' => '已支付金额',
        'pending_amount' => '待处理金额',
        'recurring_info' => '重复信息',
        'recurring_count' => '重复计数',
        'recurring_total' => '总重复次数',
        'recurring_remaining' => '剩余重复次数',
        'recurring_completed' => '已完成重复次数',
        'recurring_next' => '下一次重复',
        'recurring_last' => '上一次重复',
        'transfer' => '转移到下个月',
        'recurring' => [
            'total_payment' => '总支付金额',
            'pending_payment' => '待处理支付'
        ],
        'buttons' => [
            'delete' => '删除',
            'edit' => '编辑',
            'mark_paid' => '标记为已支付',
            'mark_not_paid' => '标记为未支付'
        ]
    ],

    // Birikimler
    'savings' => '储蓄',
    'add_saving' => '添加新储蓄',
    'edit_saving' => '编辑储蓄',
    'saving_name' => '储蓄名称',
    'target_amount' => '目标金额',
    'current_amount' => '当前金额',
    'start_date' => '开始日期',
    'target_date' => '目标日期',
    'saving' => [
        'title' => '储蓄',
        'add_success' => '储蓄添加成功',
        'add_error' => '添加储蓄时发生错误',
        'edit_success' => '储蓄更新成功',
        'edit_error' => '更新储蓄时发生错误',
        'delete_success' => '储蓄删除成功',
        'delete_error' => '删除储蓄时发生错误',
        'delete_confirm' => '您确定要删除此储蓄吗？',
        'progress' => '进度',
        'remaining' => '剩余金额',
        'remaining_days' => '剩余天数',
        'monthly_needed' => '每月所需金额',
        'completed' => '已完成',
        'on_track' => '按计划进行',
        'behind' => '落后',
        'ahead' => '超前',
        'load_error' => '加载储蓄时发生错误',
        'not_found' => '尚未添加任何储蓄',
        'update_error' => '更新储蓄时发生错误',
        'name' => '储蓄名称',
        'target_amount' => '目标金额',
        'current_amount' => '当前金额',
        'currency' => '货币',
        'start_date' => '开始日期',
        'target_date' => '目标日期',
        'status' => '状态',
        'progress_info' => '进度信息',
        'daily_needed' => '每日所需金额',
        'weekly_needed' => '每周所需金额',
        'yearly_needed' => '每年所需金额',
        'completion_date' => '预计完成日期',
        'completion_rate' => '完成率',
        'days_left' => '剩余天数',
        'days_total' => '总天数',
        'days_passed' => '已过去天数',
        'expected_progress' => '预期进度',
        'actual_progress' => '实际进度',
        'progress_difference' => '进度差异',
        'update_amount' => '更新金额',
        'update_details' => '更新详细信息'
    ],

    // Para Birimleri
    'currency' => '货币',
    'base_currency' => '基本货币',
    'exchange_rate' => '汇率',
    'update_rate' => '使用当前汇率更新',

    // Sıklık
    'frequency' => [
        'none' => '一次性',
        'daily' => '每日',
        'weekly' => '每周',
        'monthly' => '每月',
        'bimonthly' => '双月',
        'quarterly' => '季度',
        'fourmonthly' => '四个月',
        'fivemonthly' => '五个月',
        'sixmonthly' => '六个月',
        'yearly' => '每年'
    ],

    // Aylar
    'months' => [
        1 => '一月',
        2 => '二月',
        3 => '三月',
        4 => '四月',
        5 => '五月',
        6 => '六月',
        7 => '七月',
        8 => '八月',
        9 => '九月',
        10 => '十月',
        11 => '十一月',
        12 => '十二月'
    ],

    // Ayarlar
    'settings_title' => '用户设置',
    'theme' => '主题',
    'theme_light' => '浅色主题',
    'theme_dark' => '深色主题',
    'language' => '语言',
    'current_password' => '当前密码',
    'new_password' => '新密码',
    'new_password_confirm' => '确认新密码',

    // Hatalar
    'error' => '错误！',
    'success' => '成功！',
    'warning' => '警告！',
    'info' => '信息',
    'error_occurred' => '发生错误',
    'try_again' => '请重试',
    'session_expired' => '您的会话已过期。请重新登录。',
    'not_found' => '页面未找到',
    'unauthorized' => '未经授权的访问',
    'forbidden' => '禁止访问',

    // Yeni eklenen kısımlar
    'register' => [
        'title' => '创建账户',
        'error_message' => '注册时发生错误。',
        'success' => '注册成功！您可以登录了。',
        'username_taken' => '此用户名已被使用。',
        'password_mismatch' => '密码不匹配。',
        'invalid_currency' => '无效的货币选择。',
        'required' => '请填写所有字段。',
    ],

    // Currencies
    'currencies' => [
        'base_info' => '所有计算将使用此货币。不用担心，您可以稍后更改它。',
        'try' => '土耳其里拉',
        'usd' => '美元',
        'eur' => '欧元',
        'gbp' => '英镑'
    ],

    // Ayarlar
    'settings' => [
        'title' => '用户设置',
        'base_currency' => '基本货币',
        'base_currency_info' => '所有计算将使用此货币。',
        'theme' => '主题',
        'theme_light' => '浅色主题',
        'theme_dark' => '深色主题',
        'theme_info' => '界面颜色主题选择。',
        'language' => '语言',
        'language_info' => '界面语言选择。',
        'save_success' => '设置保存成功',
        'save_error' => '保存设置时发生错误',
        'current_password' => '当前密码',
        'new_password' => '新密码',
        'new_password_confirm' => '确认新密码',
        'password_success' => '密码更改成功',
        'password_error' => '更改密码时发生错误',
        'password_mismatch' => '当前密码不正确',
        'password_same' => '新密码不能与旧密码相同',
        'password_requirements' => '密码必须至少 6 个字符'
    ],

    // Uygulama
    'app' => [
        'previous_month' => '上个月',
        'next_month' => '下个月',
        'monthly_income' => '月收入',
        'monthly_expense' => '月支出',
        'net_balance' => '净余额',
        'period' => '期间',
        'next_income' => '下一次收入',
        'next_payment' => '下一次支付',
        'payment_power' => '支付能力',
        'installment_info' => '分期信息',
        'total' => '总计',
        'total_payment' => '总支付金额',
        'loading' => '加载中...',
        'no_data' => '未找到数据',
        'confirm_delete' => '您确定要删除吗？',
        'yes_delete' => '是，删除',
        'no_cancel' => '否，取消',
        'operation_success' => '操作成功',
        'operation_error' => '操作期间发生错误',
        'save_success' => '保存成功',
        'save_error' => '保存时发生错误',
        'update_success' => '更新成功',
        'update_error' => '更新时发生错误',
        'delete_success' => '删除成功',
        'delete_error' => '删除时发生错误'
    ],

    // Para birimi işlemleri
    'currency' => [
        'invalid_request' => '无效的请求',
        'invalid_currency' => '无效的货币',
        'update_success' => '货币更新成功',
        'update_error' => '更新货币时发生错误',
        'database_error' => '发生数据库错误',
        'currency_required' => '需要选择货币',
        'rate_fetched' => '汇率获取成功',
        'rate_fetch_error' => '无法检索汇率',
        'rate_not_found' => '未找到汇率',
        'select_currency' => '选择货币',
        'current_rate' => '当前汇率',
        'conversion_rate' => '转换率',
        'last_update' => '上次更新',
        'auto_update' => '自动更新',
        'manual_update' => '手动更新',
        'update_daily' => '每日更新',
        'update_weekly' => '每周更新',
        'update_monthly' => '每月更新',
        'update_never' => '不更新'
    ],

    // Özet
    'summary' => [
        'title' => '摘要',
        'load_error' => '加载摘要信息时发生错误',
        'user_not_found' => '用户未找到',
        'total_income' => '总收入',
        'total_expense' => '总支出',
        'net_balance' => '净余额',
        'positive_balance' => '正',
        'negative_balance' => '负',
        'monthly_summary' => '月度摘要',
        'yearly_summary' => '年度摘要',
        'income_vs_expense' => '收入/支出比率',
        'savings_progress' => '储蓄进度',
        'payment_schedule' => '支付计划',
        'upcoming_payments' => '即将到来的支付',
        'upcoming_incomes' => '即将到来的收入',
        'expense_percentage' => '支出百分比',
        'savings_percentage' => '储蓄百分比',
        'monthly_trend' => '月度趋势',
        'yearly_trend' => '年度趋势',
        'balance_trend' => '余额趋势',
        'expense_trend' => '支出趋势',
        'income_trend' => '收入趋势',
        'savings_trend' => '储蓄趋势',
        'budget_status' => '预算状态',
        'on_budget' => '在预算内',
        'over_budget' => '超出预算',
        'under_budget' => '低于预算',
        'budget_warning' => '预算警告',
        'budget_alert' => '预算警报',
        'expense_categories' => '支出类别',
        'income_sources' => '收入来源',
        'savings_goals' => '储蓄目标',
        'payment_methods' => '支付方式',
        'recurring_transactions' => '定期交易',
        'financial_goals' => '财务目标',
        'goal_progress' => '目标进度',
        'goal_completion' => '目标完成',
        'goal_status' => '目标状态',
        'completed_goals' => '已完成目标',
        'active_goals' => '活跃目标',
        'missed_goals' => '错过目标',
        'goal_history' => '目标历史'
    ],

    'transfer' => [
        'title' => '支付转移',
        'confirm' => '您确定要将未支付的款项转移到下个月吗？',
        'transfer_button' => '是的，转移',
        'cancel_button' => '取消',
        'error' => '转移支付时发生错误',
        'success' => '支付转移成功',
        'no_unpaid_payments' => '未找到要转移的未支付款项',
        'payment_transferred_from' => '%s（从 %s 月转移）',
        'update_error' => '支付无法更新'
    ],

    'validation' => [
        'field_required' => '%s 字段是必需的',
        'field_numeric' => '%s 字段必须是数字',
        'field_date' => '%s 字段必须是有效的日期 (YYYY-MM-DD)',
        'field_currency' => '%s 字段必须是有效的货币',
        'field_frequency' => '%s 字段必须是有效的重复频率',
        'field_min_value' => '%s 字段必须至少为 %s',
        'field_max_value' => '%s 字段必须最多为 %s',
        'date_range_error' => '开始日期不能大于结束日期',
        'invalid_format' => '无效的格式',
        'invalid_value' => '无效的值',
        'required_field' => '此字段为必填项',
        'min_length' => '必须至少 %s 个字符',
        'max_length' => '必须最多 %s 个字符',
        'exact_length' => '必须正好 %s 个字符',
        'greater_than' => '必须大于 %s',
        'less_than' => '必须小于 %s',
        'between' => '必须在 %s 和 %s 之间',
        'matches' => '必须匹配 %s',
        'different' => '必须与 %s 不同',
        'unique' => '此值已被使用',
        'valid_email' => '请输入有效的电子邮件地址',
        'valid_url' => '请输入有效的 URL',
        'valid_ip' => '请输入有效的 IP 地址',
        'valid_date' => '请输入有效的日期',
        'valid_time' => '请输入有效的时间',
        'valid_datetime' => '请输入有效的日期和时间',
        'alpha' => '必须只包含字母',
        'alpha_numeric' => '必须只包含字母和数字',
        'alpha_dash' => '必须只包含字母、数字、破折号和下划线',
        'numeric' => '必须只包含数字',
        'integer' => '必须是整数',
        'decimal' => '必须是十进制数',
        'natural' => '必须是正整数',
        'natural_no_zero' => '必须是大于零的正整数',
        'valid_base64' => '请输入有效的 Base64 值',
        'valid_json' => '请输入有效的 JSON 值',
        'valid_file' => '请选择有效的文件',
        'valid_image' => '请选择有效的图像文件',
        'valid_phone' => '请输入有效的电话号码',
        'valid_credit_card' => '请输入有效的信用卡号码',
        'valid_color' => '请输入有效的颜色代码'
    ],

    // Utils
    'utils' => [
        'validation' => [
            'required' => ':field 字段是必需的',
            'numeric' => ':field 字段必须是数字',
            'date' => ':field 字段必须是有效的日期',
            'currency' => ':field 字段必须是有效的货币',
            'frequency' => ':field 字段必须是有效的重复频率',
            'min_value' => ':field 字段必须至少为 :min',
            'max_value' => ':field 字段必须最多为 :max',
            'error_title' => '验证错误',
            'confirm_button' => '确定'
        ],
        'session' => [
            'error_title' => '会话错误',
            'invalid_token' => '无效的安全令牌'
        ],
        'frequency' => [
            'none' => '无重复',
            'monthly' => '每月',
            'bimonthly' => '双月',
            'quarterly' => '3 个月',
            'fourmonthly' => '4 个月',
            'fivemonthly' => '5 个月',
            'sixmonthly' => '6 个月',
            'yearly' => '每年'
        ],
        'form' => [
            'income_name' => '收入名称',
            'payment_name' => '支付名称',
            'amount' => '金额',
            'currency' => '货币',
            'date' => '日期',
            'frequency' => '重复频率',
            'saving_name' => '储蓄名称',
            'target_amount' => '目标金额',
            'current_amount' => '当前金额',
            'start_date' => '开始日期',
            'target_date' => '目标日期'
        ]
    ],

    'user' => [
        'not_found' => '未找到用户信息',
        'update_success' => '用户信息更新成功',
        'update_error' => '用户信息无法更新'
    ],
];