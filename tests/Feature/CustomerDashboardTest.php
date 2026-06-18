<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\RepairJob;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_dashboard_only_shows_linked_customer_amounts(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customerUser = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customer = $this->customer($admin, 'CU-1001', 'Linked Customer', $customerUser);
        $otherCustomer = $this->customer($admin, 'CU-1002', 'Other Customer');

        $ownSale = $this->sale($customer, 'SAL-1001', 1000, 400, 600, $admin);
        $otherSale = $this->sale($otherCustomer, 'SAL-1002', 9000, 0, 9000, $admin);

        $this->actingAs($customerUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('AED 1,000')
            ->assertSee($ownSale->sale_number)
            ->assertDontSee($otherSale->sale_number)
            ->assertDontSee('AED 9,000')
            ->assertSee('My Report')
            ->assertDontSee('My Purchases')
            ->assertDontSee('My Payments')
            ->assertDontSee('My Repair Battery')
            ->assertDontSee('Account Settings')
            ->assertDontSee('Change Password');
    }

    public function test_customer_dashboard_combines_sale_and_repair_invoices(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customerUser = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customer = $this->customer($admin, 'CU-1001', 'Linked Customer', $customerUser);
        $this->sale($customer, 'SAL-1001', 1000, 400, 600, $admin);
        RepairJob::create([
            'repair_number' => 'RB-1001',
            'customer_id' => $customer->id,
            'battery_details' => 'Mix Batteries',
            'quantity' => 1,
            'unit_price' => 133,
            'estimated_cost' => 133,
            'advance_payment' => 0,
            'status' => 'received',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($customerUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('SAL-1001')
            ->assertSee('RB-1001')
            ->assertSee('Pending')
            ->assertDontSee('Received')
            ->assertSee('AED 1,133');
    }

    public function test_customer_cannot_open_another_customers_sale(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customerUser = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->customer($admin, 'CU-1001', 'Linked Customer', $customerUser);
        $otherCustomer = $this->customer($admin, 'CU-1002', 'Other Customer');
        $otherSale = $this->sale($otherCustomer, 'SAL-1002', 9000, 0, 9000, $admin);

        $this->actingAs($customerUser)
            ->get(route('sales.show', $otherSale))
            ->assertForbidden();
    }

    public function test_customer_cannot_access_any_module_except_dashboard_and_reports(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customerUser = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->customer($admin, 'CU-1001', 'Linked Customer', $customerUser);

        $this->actingAs($customerUser);

        $this->get(route('sales.index'))->assertForbidden();
        $this->get(route('repair-jobs.index'))->assertForbidden();
        $this->get(route('payments.index'))->assertForbidden();
        $this->get(route('customers.index'))->assertForbidden();
        $this->get(route('battery-inventory.index'))->assertForbidden();
        $this->get(route('suppliers.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('profile.edit'))->assertForbidden();
        $this->get(route('reports.pdf'))->assertForbidden();
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('reports.index'))->assertOk();
    }

    public function test_customer_report_ignores_another_customer_filter(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customerUser = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customer = $this->customer($admin, 'CU-1001', 'Linked Customer', $customerUser);
        $otherCustomer = $this->customer($admin, 'CU-1002', 'Other Customer');
        $ownSale = $this->sale($customer, 'SAL-1001', 1000, 400, 600, $admin);
        $otherSale = $this->sale($otherCustomer, 'SAL-1002', 9000, 0, 9000, $admin);

        $this->actingAs($customerUser)
            ->get(route('reports.index', [
                'customer_id' => $otherCustomer->id,
                'start_date' => now()->subDay()->toDateString(),
                'end_date' => now()->addDay()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('My Account Report')
            ->assertSee($ownSale->sale_number)
            ->assertDontSee($otherSale->sale_number);
    }

    private function customer(User $admin, string $code, string $name, ?User $user = null): Customer
    {
        return Customer::create([
            'user_id' => $user?->id,
            'customer_code' => $code,
            'full_name' => $name,
            'phone' => '050'.substr($code, -4),
            'country' => 'UAE',
            'customer_type' => 'both',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
    }

    private function sale(
        Customer $customer,
        string $number,
        float $total,
        float $received,
        float $remaining,
        User $admin
    ): Sale {
        return Sale::create([
            'sale_number' => $number,
            'customer_id' => $customer->id,
            'subtotal' => $total,
            'discount' => 0,
            'vat' => 0,
            'total_amount' => $total,
            'received_amount' => $received,
            'remaining_amount' => $remaining,
            'payment_status' => $remaining > 0 ? ($received > 0 ? 'partial' : 'pending') : 'paid',
            'created_by' => $admin->id,
        ]);
    }
}
