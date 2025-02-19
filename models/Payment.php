<?php
require_once __DIR__ . '/../Model.php';

class Payment extends Model
{
    protected $table = 'payments';

    // Get payments for a specific month
    public function getMonthlyPayments(int $userId, int $year, int $month): array
    {
        $query = "
            SELECT 
                p.*,
                ps.is_paid,
                ps.paid_at,
                c.name as category_name
            FROM payments p
            LEFT JOIN payment_statuses ps ON p.id = ps.payment_id 
                AND ps.year = :year 
                AND ps.month = :month
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.user_id = :user_id
            AND (
                -- One-time payments in this month
                (p.frequency = 0 AND YEAR(p.first_payment_date) = :year AND MONTH(p.first_payment_date) = :month)
                OR
                -- Recurring payments that fall in this month
                (p.frequency > 0 AND (
                    -- If repeat_count is NULL, it's infinite
                    p.repeat_count IS NULL
                    OR
                    -- Otherwise check if we haven't exceeded repeat_count
                    (
                        SELECT COUNT(*)
                        FROM payment_statuses ps2
                        WHERE ps2.payment_id = p.id
                        AND (ps2.year < :year OR (ps2.year = :year AND ps2.month <= :month))
                    ) <= p.repeat_count
                ))
            )
            ORDER BY p.first_payment_date";

        return $this->db->select($query, [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);
    }

    // Create new payment with initial status
    public function createWithStatus(array $paymentData, bool $isPaid = false): int
    {
        try {
            $this->beginTransaction();

            // Create payment
            $paymentId = $this->create($paymentData);

            // Create initial payment status if it's a one-time payment or first payment of recurring
            $firstDate = new DateTime($paymentData['first_payment_date']);

            $statusData = [
                'payment_id' => $paymentId,
                'year' => (int)$firstDate->format('Y'),
                'month' => (int)$firstDate->format('n'),
                'is_paid' => $isPaid,
                'paid_at' => $isPaid ? date('Y-m-d H:i:s') : null
            ];

            $query = "INSERT INTO payment_statuses (payment_id, year, month, is_paid, paid_at) 
                     VALUES (:payment_id, :year, :month, :is_paid, :paid_at)";

            $this->db->insert($query, $statusData);

            $this->commit();
            return $paymentId;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    // Update payment status for a specific month
    public function updateStatus(int $paymentId, int $year, int $month, bool $isPaid): bool
    {
        $query = "
            INSERT INTO payment_statuses (payment_id, year, month, is_paid, paid_at)
            VALUES (:payment_id, :year, :month, :is_paid, :paid_at)
            ON DUPLICATE KEY UPDATE 
                is_paid = VALUES(is_paid),
                paid_at = VALUES(paid_at)";

        $params = [
            'payment_id' => $paymentId,
            'year' => $year,
            'month' => $month,
            'is_paid' => $isPaid,
            'paid_at' => $isPaid ? date('Y-m-d H:i:s') : null
        ];

        return $this->db->update($query, $params) !== false;
    }

    // Delete payment and all its statuses
    public function delete(int $id): int
    {
        try {
            $this->beginTransaction();

            // Delete payment statuses first (foreign key will handle this automatically)
            $result = parent::delete($id);

            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    // Get total payments for a month by category
    public function getTotalsByCategory(int $userId, int $year, int $month): array
    {
        $query = "
            SELECT 
                c.id as category_id,
                c.name as category_name,
                SUM(p.amount) as total_amount,
                p.currency
            FROM payments p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN payment_statuses ps ON p.id = ps.payment_id 
                AND ps.year = :year 
                AND ps.month = :month
            WHERE p.user_id = :user_id
            AND ps.is_paid = 1
            GROUP BY c.id, c.name, p.currency";

        return $this->db->select($query, [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);
    }
}
