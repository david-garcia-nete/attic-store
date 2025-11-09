<?php

namespace Tests\Unit\Models;

use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_commit_throws_if_it_would_go_negative(): void
    {
        $inv = Inventory::factory()->create(['qty_on_hand' => 1, 'qty_reserved' => 0]);
        $this->expectException(\RuntimeException::class);
        $inv->commit(2);
    }
}
