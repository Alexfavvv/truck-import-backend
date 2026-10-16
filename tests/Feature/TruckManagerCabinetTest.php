<?php

namespace Tests\Feature;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\TruckInvoiceStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TruckManagerCabinetTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role, array $extra = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Пользователь',
            'full_name' => 'Пользователь',
            'login' => fake()->unique()->userName(),
            'password' => 'secret1234',
            'role' => $role,
            'is_admin' => $role->isTruckSide(),
            'is_active' => true,
        ], $extra));
    }

    private function order(User $customer, User $creator): Order
    {
        return Order::create([
            'order_number' => 'ORD-TEST-'.fake()->unique()->numberBetween(1000, 99999),
            'user_id' => $creator->id,
            'company_admin_id' => $customer->id,
            'status' => OrderStatus::Created,
        ]);
    }

    private function invoice(Order $order, User $creator): Invoice
    {
        return Invoice::create([
            'invoice_number' => 'INV-TEST-'.fake()->unique()->numberBetween(1000, 99999),
            'order_id' => $order->id,
            'created_by' => $creator->id,
            'amount' => 100,
            'truck_status' => TruckInvoiceStatus::PendingPayment,
            'customer_status' => CustomerInvoiceStatus::Pending,
        ]);
    }

    public function test_manager_login_redirects_to_own_cabinet_and_admin_cannot_enter_it(): void
    {
        $manager = $this->user(UserRole::ManagerTruck, ['login' => 'truckmanager']);
        $this->post('/login', ['login' => 'truckmanager', 'password' => 'secret1234'])
            ->assertRedirect(route('manager.dashboard'));
        $this->get(route('manager.dashboard'))->assertOk();
        $this->get(route('admin.index'))->assertForbidden();

        $admin = $this->user(UserRole::AdminTruck);
        $this->actingAs($admin)->get(route('manager.dashboard'))->assertForbidden();
        $this->get(route('admin.truck-managers.index'))->assertOk();
    }

    public function test_admin_can_create_assign_and_disable_manager_without_deleting_orders(): void
    {
        $admin = $this->user(UserRole::AdminTruck);
        $company = $this->user(UserRole::AdminCustomer, ['company' => 'ООО Альфа']);
        $this->actingAs($admin)->post(route('admin.truck-managers.store'), [
            'full_name' => 'Иван Менеджер', 'login' => 'ivan.manager',
            'password' => 'newsecret123', 'password_confirmation' => 'newsecret123',
            'company_ids' => [$company->id],
        ])->assertRedirect(route('admin.truck-managers.index'));

        $manager = User::where('login', 'ivan.manager')->firstOrFail();
        $this->assertTrue(Hash::check('newsecret123', $manager->password));
        $this->assertNotSame('newsecret123', $manager->password);
        $this->assertTrue($manager->managesCompany($company->id));
        $this->post(route('admin.truck-managers.store'), [
            'full_name' => 'Другой менеджер', 'login' => 'ivan.manager',
            'password' => 'anothersecret123', 'password_confirmation' => 'anothersecret123',
        ])->assertSessionHasErrors('login');
        $order = $this->order($company, $manager);
        $this->delete(route('admin.users.destroy', $manager))->assertForbidden();
        $token = $manager->createToken('test')->plainTextToken;

        $this->put(route('admin.truck-managers.update', $manager), [
            'full_name' => 'Иван Менеджер', 'login' => 'ivan.manager', 'is_active' => 0,
        ])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => $manager->id]);
        $this->assertFalse($manager->fresh()->is_active);
        $this->withToken($token)->getJson('/api/v1/auth/status')->assertJson(['authenticated' => false]);
        $this->post('/login', ['login' => 'ivan.manager', 'password' => 'newsecret123'])->assertSessionHasErrors();
        $this->actingAs($manager->fresh())->get(route('manager.dashboard'))->assertForbidden();
    }

    public function test_assigned_orders_invoices_and_documents_are_scoped_and_revoked_immediately(): void
    {
        $manager = $this->user(UserRole::ManagerTruck);
        $company = $this->user(UserRole::AdminCustomer, ['company' => 'Своя компания']);
        $other = $this->user(UserRole::AdminCustomer, ['company' => 'Чужая компания']);
        $manager->assignedCompanies()->attach($company);
        $ownOrder = $this->order($company, $other);
        $otherOrder = $this->order($other, $other);
        $ownInvoice = $this->invoice($ownOrder, $other);
        $otherInvoice = $this->invoice($otherOrder, $other);

        $this->actingAs($manager)->get(route('manager.orders.index'))
            ->assertOk()->assertSee($ownOrder->order_number)->assertDontSee($otherOrder->order_number);
        $this->get(route('manager.invoices.index'))
            ->assertOk()->assertSee($ownInvoice->invoice_number)->assertDontSee($otherInvoice->invoice_number);
        $this->get(route('manager.orders.show', $ownOrder))->assertOk();
        $this->get(route('manager.invoices.show', $ownInvoice))->assertOk();
        $this->get(route('manager.invoices.pdf', $ownInvoice))->assertOk();
        $this->get(route('manager.orders.show', $otherOrder))->assertForbidden();
        $this->get(route('manager.invoices.show', $otherInvoice))->assertForbidden();
        $this->get(route('manager.invoices.pdf', $otherInvoice))->assertForbidden();
        $this->get(route('manager.companies.show', $other))->assertForbidden();
        $this->patch(route('manager.orders.status', $otherOrder), ['status' => OrderStatus::Completed->value])->assertForbidden();
        $this->patch(route('manager.orders.status', $ownOrder), ['status' => OrderStatus::Completed->value])->assertRedirect();
        $this->assertSame(OrderStatus::Completed, $ownOrder->fresh()->status);

        $manager->assignedCompanies()->detach($company);
        $this->get(route('manager.orders.show', $ownOrder))->assertForbidden();
        $this->get(route('manager.invoices.show', $ownInvoice))->assertForbidden();
        $this->get(route('manager.invoices.pdf', $ownInvoice))->assertForbidden();
        $this->post(route('manager.orders.store'), [
            'company_admin_id' => $company->id,
            'quantities' => [1 => 1],
        ])->assertForbidden();
    }

    public function test_manager_cannot_manage_admin_resources_or_create_order_for_unassigned_company(): void
    {
        $manager = $this->user(UserRole::ManagerTruck);
        $assigned = $this->user(UserRole::AdminCustomer);
        $other = $this->user(UserRole::AdminCustomer);
        $manager->assignedCompanies()->attach($assigned);
        $this->actingAs($manager)->get(route('admin.truck-managers.index'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.products.index'))->assertForbidden();
        $this->get(route('admin.products.import.form'))->assertForbidden();
        $this->post(route('manager.orders.store'), [
            'company_admin_id' => $other->id,
            'quantities' => [1 => 1],
        ])->assertSessionHasErrors('company_admin_id');
    }

    public function test_customer_manager_remains_isolated_from_truck_manager_cabinet(): void
    {
        $customer = $this->user(UserRole::AdminCustomer);
        $customerManager = $this->user(UserRole::ManagerCustomer, ['parent_id' => $customer->id]);
        $otherCompany = $this->user(UserRole::AdminCustomer);
        $ownOrder = $this->order($customer, $customerManager);
        $otherOrder = $this->order($otherCompany, $otherCompany);
        $this->actingAs($customerManager)->get(route('manager.dashboard'))->assertForbidden();
        $this->get(route('customer.dashboard'))->assertOk();
        $this->get(route('customer.orders.index'))->assertOk()->assertSee($ownOrder->order_number)->assertDontSee($otherOrder->order_number);
        $this->get(route('customer.orders.show', $otherOrder))->assertForbidden();
    }

    public function test_manager_creates_order_only_for_assigned_company_and_api_rejects_other_company(): void
    {
        $manager = $this->user(UserRole::ManagerTruck);
        $company = $this->user(UserRole::AdminCustomer);
        $other = $this->user(UserRole::AdminCustomer);
        $manager->assignedCompanies()->attach($company);
        $product = Product::factory()->create(['price' => 125, 'quantity' => 10]);

        $this->actingAs($manager)->post(route('manager.orders.store'), [
            'company_admin_id' => $company->id,
            'quantities' => [$product->id => 2],
        ])->assertRedirect();
        $this->assertDatabaseHas('orders', ['company_admin_id' => $company->id, 'user_id' => $manager->id]);

        Sanctum::actingAs($manager);
        $this->postJson('/api/v1/client/orders', [
            'company_admin_id' => $other->id,
            'items' => [['sku' => $product->sku, 'quantity' => 1]],
        ])->assertForbidden();
    }

    public function test_manager_can_change_own_password_without_receiving_the_stored_hash(): void
    {
        $manager = $this->user(UserRole::ManagerTruck);
        $this->actingAs($manager)->put(route('manager.profile.password'), [
            'current_password' => 'secret1234',
            'password' => 'replacement123',
            'password_confirmation' => 'replacement123',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('replacement123', $manager->fresh()->password));
        $this->get(route('manager.profile'))->assertOk()->assertDontSee($manager->password);
    }
}
