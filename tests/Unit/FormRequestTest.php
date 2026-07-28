<?php
namespace Tests\Unit;
use Tests\TestCase;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\StoreCustomerRequest;
use Illuminate\Support\Facades\Validator;

class FormRequestTest extends TestCase
{
    public function test_store_expense_request_validation_passes_with_valid_data()
    {
        $rules = (new StoreExpenseRequest())->rules();
        $data = [
            'description' => 'Test expense',
            'amount' => 50000,
            'expense_date' => '2024-01-01',
        ];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_store_expense_request_fails_without_description()
    {
        $rules = (new StoreExpenseRequest())->rules();
        $data = ['amount' => 50000, 'expense_date' => '2024-01-01'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    public function test_store_expense_request_fails_with_negative_amount()
    {
        $rules = (new StoreExpenseRequest())->rules();
        $data = ['description' => 'Test', 'amount' => -100, 'expense_date' => '2024-01-01'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_store_customer_request_passes_with_valid_data()
    {
        $rules = (new StoreCustomerRequest())->rules();
        $data = ['name' => 'John Doe', 'phone' => '08123456789'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_store_customer_request_fails_without_name()
    {
        $rules = (new StoreCustomerRequest())->rules();
        $data = ['phone' => '08123456789'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
