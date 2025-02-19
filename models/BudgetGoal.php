<?php
require_once __DIR__ . '/../Model.php';

class BudgetGoal extends Model
{
    protected $table = 'monthly_budgets';

    // Get budget goal for specific month
    public function getMonthlyBudget(int $userId, int $year, int $month): ?array
    {
        $query = "
            SELECT *,
                (
                    SELECT COALESCE(SUM(p.amount), 0)
                    FROM payments p
                    JOIN payment_statuses ps ON p.id = ps.payment_id 
                        AND ps.year = mb.year 
                        AND ps.month = mb.month
                        AND ps.is_paid = 1
                    WHERE p.user_id = mb.user_id
                ) as total_spent,
                (
                    SELECT COALESCE(SUM(amount), 0)
                    FROM incomes
                    WHERE user_id = mb.user_id
                    AND (
                        (frequency = 0 AND YEAR(first_income_date) = mb.year AND MONTH(first_income_date) = mb.month)
                        OR
                        (
                            frequency > 0
                            AND first_income_date <= LAST_DAY(CONCAT(mb.year, '-', mb.month, '-01'))
                            AND (
                                repeat_count IS NULL
                                OR
                                (
                                    FLOOR(
                                        TIMESTAMPDIFF(MONTH, first_income_date, CONCAT(mb.year, '-', mb.month, '-01'))
                                        / frequency
                                    ) + 1 <= repeat_count
                                )
                            )
                        )
                    )
                ) as total_income
            FROM monthly_budgets mb
            WHERE user_id = :user_id
            AND year = :year
            AND month = :month";

        $result = $this->db->select($query, [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);

        return $result ? $result[0] : null;
    }

    // Set or update monthly budget using the new insertOrUpdate method
    public function setMonthlyBudget(int $userId, int $year, int $month, float $limit): bool
    {
        $data = [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month,
            'limit_amount' => $limit
        ];

        try {
            $this->db->insertOrUpdate($this->table, $data, ['limit_amount']);
            return true;
        } catch (Exception $e) {
            error_log("Failed to set monthly budget: " . $e->getMessage());
            return false;
        }
    }

    // Get yearly budget summary
    public function getYearlySummary(int $userId, int $year): array
    {
        $query = "
            SELECT 
                mb.month,
                mb.limit_amount,
                COALESCE(
                    (
                        SELECT SUM(p.amount)
                        FROM payments p
                        JOIN payment_statuses ps ON p.id = ps.payment_id 
                            AND ps.year = mb.year 
                            AND ps.month = mb.month
                            AND ps.is_paid = 1
                        WHERE p.user_id = mb.user_id
                    ),
                    0
                ) as total_spent,
                COALESCE(
                    (
                        SELECT SUM(amount)
                        FROM incomes
                        WHERE user_id = mb.user_id
                        AND (
                            (frequency = 0 AND YEAR(first_income_date) = mb.year AND MONTH(first_income_date) = mb.month)
                            OR
                            (
                                frequency > 0
                                AND first_income_date <= LAST_DAY(CONCAT(mb.year, '-', mb.month, '-01'))
                                AND (
                                    repeat_count IS NULL
                                    OR
                                    (
                                        FLOOR(
                                            TIMESTAMPDIFF(MONTH, first_income_date, CONCAT(mb.year, '-', mb.month, '-01'))
                                            / frequency
                                        ) + 1 <= repeat_count
                                    )
                                )
                            )
                        )
                    ),
                    0
                ) as total_income
            FROM monthly_budgets mb
            WHERE user_id = :user_id
            AND year = :year
            ORDER BY month";

        return $this->db->select($query, [
            'user_id' => $userId,
            'year' => $year
        ]);
    }

    // Get budget status statistics
    public function getBudgetStats(int $userId): array
    {
        $query = "
            SELECT
                COUNT(*) as total_months,
                SUM(CASE WHEN limit_amount > 0 THEN 1 ELSE 0 END) as months_with_budget,
                AVG(limit_amount) as average_budget,
                SUM(
                    CASE 
                        WHEN (
                            SELECT COALESCE(SUM(p.amount), 0)
                            FROM payments p
                            JOIN payment_statuses ps ON p.id = ps.payment_id 
                                AND ps.year = mb.year 
                                AND ps.month = mb.month
                                AND ps.is_paid = 1
                            WHERE p.user_id = mb.user_id
                        ) <= limit_amount 
                        THEN 1 
                        ELSE 0 
                    END
                ) as months_within_budget
            FROM monthly_budgets mb
            WHERE user_id = :user_id
            AND year = YEAR(CURDATE())";

        $result = $this->db->select($query, ['user_id' => $userId]);
        return $result ? $result[0] : null;
    }
}
