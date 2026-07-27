<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_index_includes_client_side_customer_data()
    {
        $customer = Customer::factory()->create([
            'name' => 'Maria Santos',
            'customer_code' => 'CUST-001',
            'status' => 'Active',
        ]);

        $response = $this->get('/customers');

        $response->assertOk();
        $response->assertSee("x-data='customerTable", false);
        $response->assertSee('Search Customer ID, Name');
        $response->assertSee($customer->name);
        $response->assertSee($customer->customer_code);
    }

    public function test_customer_index_escapes_apostrophes_in_client_side_data()
    {
        Customer::factory()->create([
            'name' => "O'Brien",
            'customer_code' => 'CUST-002',
            'status' => 'Active',
        ]);

        $response = $this->get('/customers');

        $response->assertOk();
        $response->assertSee('O\\u0027Brien', false);
    }
}
