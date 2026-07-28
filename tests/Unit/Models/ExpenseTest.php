<?php
namespace Tests\Unit\Models;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    public function test_categories_constant_has_expected_keys()
    {
        $categories = \App\Models\Tenant\Expense::CATEGORIES;
        $this->assertIsArray($categories);
        $this->assertArrayHasKey('operasional', $categories);
        $this->assertArrayHasKey('gaji', $categories);
        $this->assertArrayHasKey('listrik', $categories);
        $this->assertArrayHasKey('sewa', $categories);
        $this->assertArrayHasKey('marketing', $categories);
        $this->assertArrayHasKey('lainnya', $categories);
        $this->assertCount(6, $categories);
    }

    public function test_fillable_contains_photo_field()
    {
        $expense = new \App\Models\Tenant\Expense();
        $this->assertContains('photo', $expense->getFillable());
        $this->assertContains('description', $expense->getFillable());
        $this->assertContains('amount', $expense->getFillable());
        $this->assertContains('category', $expense->getFillable());
    }

    public function test_casts_amount_to_decimal()
    {
        $expense = new \App\Models\Tenant\Expense();
        $casts = $expense->getCasts();
        $this->assertEquals('decimal:2', $casts['amount']);
        $this->assertEquals('date', $casts['expense_date']);
    }

    public function test_photo_url_accessor_returns_null_when_no_photo()
    {
        $expense = new \App\Models\Tenant\Expense();
        $this->assertNull($expense->photo_url);
    }

    public function test_photo_url_accessor_returns_storage_path_for_local()
    {
        $expense = new \App\Models\Tenant\Expense(['photo' => 'expenses/test.jpg']);
        $this->assertEquals('/storage/expenses/test.jpg', $expense->photo_url);
    }

    public function test_photo_url_accessor_returns_direct_url_for_http()
    {
        $expense = new \App\Models\Tenant\Expense(['photo' => 'https://drive.google.com/test.jpg']);
        $this->assertEquals('https://drive.google.com/test.jpg', $expense->photo_url);
    }
}
