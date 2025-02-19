<?php
require_once __DIR__ . '/../Model.php';

class Category extends Model
{
    protected $table = 'categories';

    // Get categories with their spending for a specific month
    public function getCategoriesWithSpending(int $userId, int $year, int $month): array
    {
        $query = "
            SELECT 
                c.*,
                COALESCE(SUM(p.amount), 0) as spent_amount,
                COALESCE(SUM(p.amount) / c.monthly_limit * 100, 0) as progress
            FROM categories c
            LEFT JOIN payments p ON c.id = p.category_id
            LEFT JOIN payment_statuses ps ON p.id = ps.payment_id 
                AND ps.year = :year 
                AND ps.month = :month
                AND ps.is_paid = 1
            WHERE c.user_id = :user_id
            GROUP BY c.id
            ORDER BY c.name";

        return $this->db->select($query, [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);
    }

    // Get category spending history
    public function getCategoryHistory(int $categoryId, int $year): array
    {
        $query = "
            SELECT 
                YEAR(p.first_payment_date) as year,
                MONTH(p.first_payment_date) as month,
                SUM(p.amount) as total_amount,
                p.currency
            FROM categories c
            LEFT JOIN payments p ON c.id = p.category_id
            LEFT JOIN payment_statuses ps ON p.id = ps.payment_id 
                AND ps.is_paid = 1
            WHERE c.id = :category_id
            AND YEAR(p.first_payment_date) = :year
            GROUP BY 
                YEAR(p.first_payment_date),
                MONTH(p.first_payment_date),
                p.currency
            ORDER BY 
                year, month";

        return $this->db->select($query, [
            'category_id' => $categoryId,
            'year' => $year
        ]);
    }

    // Update category monthly limit
    public function updateLimit(int $categoryId, float $newLimit): bool
    {
        return $this->update($categoryId, ['monthly_limit' => $newLimit]) !== false;
    }

    // Get categories exceeding their limits
    public function getExceededCategories(int $userId, int $year, int $month): array
    {
        $query = "
            SELECT 
                c.*,
                SUM(p.amount) as spent_amount,
                (SUM(p.amount) - c.monthly_limit) as excess_amount,
                (SUM(p.amount) / c.monthly_limit * 100) as progress
            FROM categories c
            JOIN payments p ON c.id = p.category_id
            JOIN payment_statuses ps ON p.id = ps.payment_id 
                AND ps.year = :year 
                AND ps.month = :month
                AND ps.is_paid = 1
            WHERE c.user_id = :user_id
            GROUP BY c.id
            HAVING spent_amount > c.monthly_limit
            ORDER BY progress DESC";

        return $this->db->select($query, [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);
    }
}
