<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_domain_tables_exists(): void
    {
        $expectedTables = [
            'stores',
            'warehouses',
            'presentations',
            'customers',
            'warehouse_locations',
            'orders',
            'order_items',
            'packages',
            'vehicles',
            'shipments',
            'shipment_items',
            'movements',
            'incidents',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Falta la tabla [{$table}]."
            );
        }
    }

    public function test_users_table_has_role_and_store_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'role'));
        $this->assertTrue(Schema::hasColumn('users', 'store_id'));
    }

    public function test_orders_table_has_core_columns(): void
    {
        $columns = [
            'order_number', 'store_id', 'user_id', 'warehouse_id',
            'status', 'ordered_at', 'received_at', 'prepared_at',
            'shipped_at', 'completed_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('orders', $column),
                "Falta la columna [{$column}] en la tabla orders."
            );
        }
    }

    public function test_order_items_table_structure(): void
    {
        $this->assertTrue(Schema::hasColumn('order_items', 'quantity_requested'));
        $this->assertTrue(Schema::hasColumn('order_items', 'quantity_received'));
        $this->assertTrue(Schema::hasColumn('order_items', 'quantity_prepared'));
    }

    public function test_presentations_table_structure(): void
    {
        $this->assertTrue(Schema::hasColumn('presentations', 'presentation_type'));
        $this->assertTrue(Schema::hasColumn('presentations', 'flavor'));
        $this->assertTrue(Schema::hasColumn('presentations', 'pieces_per_box'));
        $this->assertTrue(Schema::hasColumn('presentations', 'sku'));
    }

    public function test_movements_table_is_polymorphic(): void
    {
        $this->assertTrue(Schema::hasColumn('movements', 'trackable_type'));
        $this->assertTrue(Schema::hasColumn('movements', 'trackable_id'));
        $this->assertTrue(Schema::hasColumn('movements', 'state'));
        $this->assertTrue(Schema::hasColumn('movements', 'action'));
    }

    public function test_incidents_table_structure(): void
    {
        $this->assertTrue(Schema::hasColumn('incidents', 'type'));
        $this->assertTrue(Schema::hasColumn('incidents', 'evidence_path'));
        $this->assertTrue(Schema::hasColumn('incidents', 'occurred_at'));
    }

    public function test_stores_table_has_contact_and_hours_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('stores', 'contact_name'));
        $this->assertTrue(Schema::hasColumn('stores', 'opening_hours'));
    }
}