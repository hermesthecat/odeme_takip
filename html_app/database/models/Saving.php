<?php
require_once __DIR__ . '/../Model.php';

class Saving extends Model
{
    protected $table = 'savings';

    // Get active savings (not completed and not expired)
    public function getActiveSavings(int $userId): array
    {
        $query = "
            SELECT *,
                (current_amount / target_amount * 100) as progress,
                DATEDIFF(target_date, CURDATE()) as days_remaining
            FROM savings
            WHERE user_id = :user_id
            AND current_amount < target_amount
            AND target_date >= CURDATE()
            ORDER BY target_date";

        return $this->db->select($query, ['user_id' => $userId]);
    }

    // Get all savings with progress
    public function getAllWithProgress(int $userId): array
    {
        $query = "
            SELECT *,
                (current_amount / target_amount * 100) as progress,
                CASE 
                    WHEN target_date >= CURDATE() THEN DATEDIFF(target_date, CURDATE())
                    ELSE 0
                END as days_remaining,
                CASE
                    WHEN current_amount >= target_amount THEN 'completed'
                    WHEN target_date < CURDATE() THEN 'expired'
                    ELSE 'active'
                END as status
            FROM savings
            WHERE user_id = :user_id
            ORDER BY target_date";

        return $this->db->select($query, ['user_id' => $userId]);
    }

    // Update savings progress
    public function updateProgress(int $id, float $newAmount): bool
    {
        $query = "
            UPDATE savings
            SET current_amount = :amount
            WHERE id = :id
            AND current_amount <= target_amount";  // Prevent oversaving

        return $this->db->update($query, [
            'id' => $id,
            'amount' => $newAmount
        ]) !== false;
    }

    // Add to current amount
    public function addAmount(int $id, float $amount): bool
    {
        $query = "
            UPDATE savings
            SET current_amount = LEAST(current_amount + :amount, target_amount)
            WHERE id = :id";

        return $this->db->update($query, [
            'id' => $id,
            'amount' => $amount
        ]) !== false;
    }

    // Get savings summary
    public function getSummary(int $userId): array
    {
        $query = "
            SELECT
                currency,
                COUNT(*) as total_goals,
                SUM(CASE WHEN current_amount >= target_amount THEN 1 ELSE 0 END) as completed_goals,
                SUM(target_amount) as total_target,
                SUM(current_amount) as total_saved,
                SUM(current_amount) / SUM(target_amount) * 100 as overall_progress
            FROM savings
            WHERE user_id = :user_id
            GROUP BY currency";

        return $this->db->select($query, ['user_id' => $userId]);
    }

    // Get monthly savings progress
    public function getMonthlyProgress(int $userId, int $year, int $month): array
    {
        $query = "
            SELECT
                currency,
                SUM(
                    CASE 
                        WHEN YEAR(start_date) = :year AND MONTH(start_date) = :month
                        THEN current_amount
                        ELSE 0
                    END
                ) as new_savings,
                COUNT(
                    CASE 
                        WHEN YEAR(start_date) = :year AND MONTH(start_date) = :month
                        THEN 1
                    END
                ) as new_goals,
                SUM(
                    CASE 
                        WHEN current_amount >= target_amount 
                        AND YEAR(start_date) <= :year 
                        AND (
                            YEAR(target_date) = :year AND MONTH(target_date) = :month
                        )
                        THEN 1
                        ELSE 0
                    END
                ) as completed_goals
            FROM savings
            WHERE user_id = :user_id
            GROUP BY currency";

        return $this->db->select($query, [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);
    }
}
