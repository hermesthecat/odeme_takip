<?php
require_once __DIR__ . '/../Model.php';

class Income extends Model
{
    protected $table = 'incomes';

    // Get incomes for a specific month
    public function getMonthlyIncomes(int $userId, int $year, int $month): array
    {
        $query = "
            SELECT *
            FROM incomes
            WHERE user_id = :user_id
            AND (
                -- One-time incomes in this month
                (frequency = 0 AND YEAR(first_income_date) = :year AND MONTH(first_income_date) = :month)
                OR
                -- Recurring incomes that started before or in this month
                (
                    frequency > 0
                    AND first_income_date <= LAST_DAY(:year-:month-01)
                    AND (
                        -- If repeat_count is NULL, it's infinite
                        repeat_count IS NULL
                        OR
                        -- Otherwise check if we haven't exceeded repeat_count
                        (
                            FLOOR(
                                TIMESTAMPDIFF(MONTH, first_income_date, :year-:month-01)
                                / frequency
                            ) + 1 <= repeat_count
                        )
                    )
                )
            )
            ORDER BY first_income_date";

        return $this->db->select($query, [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);
    }

    // Get total income for a month
    public function getMonthlyTotal(int $userId, int $year, int $month): array
    {
        $query = "
            SELECT currency, SUM(amount) as total_amount
            FROM incomes
            WHERE user_id = :user_id
            AND (
                (frequency = 0 AND YEAR(first_income_date) = :year AND MONTH(first_income_date) = :month)
                OR
                (
                    frequency > 0
                    AND first_income_date <= LAST_DAY(:year-:month-01)
                    AND (
                        repeat_count IS NULL
                        OR
                        (
                            FLOOR(
                                TIMESTAMPDIFF(MONTH, first_income_date, :year-:month-01)
                                / frequency
                            ) + 1 <= repeat_count
                        )
                    )
                )
            )
            GROUP BY currency";

        return $this->db->select($query, [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);
    }

    // Calculate next income date for a recurring income
    public function calculateNextIncomeDate(int $id): ?string
    {
        $income = $this->find($id);
        if (!$income || $income['frequency'] === 0) {
            return null;
        }

        $query = "
            SELECT 
                DATE_ADD(
                    first_income_date, 
                    INTERVAL (
                        FLOOR(
                            TIMESTAMPDIFF(MONTH, first_income_date, CURDATE())
                            / frequency
                        ) + 1
                    ) * frequency MONTH
                ) as next_date
            FROM incomes
            WHERE id = :id
            AND (
                repeat_count IS NULL
                OR
                (
                    FLOOR(
                        TIMESTAMPDIFF(MONTH, first_income_date, CURDATE())
                        / frequency
                    ) + 1 <= repeat_count
                )
            )";

        $result = $this->db->select($query, ['id' => $id]);
        return $result ? $result[0]['next_date'] : null;
    }

    // Get yearly income summary
    public function getYearlySummary(int $userId, int $year): array
    {
        $query = "
            SELECT 
                MONTH(first_income_date) as month,
                currency,
                SUM(amount) as total_amount
            FROM incomes
            WHERE user_id = :user_id
            AND (
                (frequency = 0 AND YEAR(first_income_date) = :year)
                OR
                (
                    frequency > 0
                    AND YEAR(first_income_date) <= :year
                    AND (
                        repeat_count IS NULL
                        OR
                        (
                            FLOOR(
                                TIMESTAMPDIFF(MONTH, first_income_date, :year-12-31)
                                / frequency
                            ) + 1 <= repeat_count
                        )
                    )
                )
            )
            GROUP BY MONTH(first_income_date), currency
            ORDER BY month";

        return $this->db->select($query, [
            'user_id' => $userId,
            'year' => $year
        ]);
    }
}
