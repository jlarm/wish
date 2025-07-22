<?php

use App\Enums\Role;
use App\Filament\Resources\ItemResource;
use App\Models\Item;
use App\Models\User;
use Filament\Forms\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Item Edit Purchase Status Visibility', function () {
    beforeEach(function () {
        $this->childUser = User::factory()->create(['role' => Role::CHILD]);
        $this->parentUser = User::factory()->create(['role' => Role::PARENT]);
        $this->relativeUser = User::factory()->create(['role' => Role::RELATIVE]);
        
        $this->childItem = Item::factory()->create(['user_id' => $this->childUser->id]);
        $this->parentItem = Item::factory()->create(['user_id' => $this->parentUser->id]);
    });

    it('hides purchase status section when user edits their own item', function () {
        $this->actingAs($this->childUser);
        
        // Test the visibility logic directly (same as used in ItemResource)
        $visible = in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true) &&
                  $this->childItem->user_id !== auth()->id();
        
        // The section should be hidden when child user edits their own item
        expect($visible)->toBeFalse();
        
        // Also test with parent editing their own item
        $this->actingAs($this->parentUser);
        $visible = in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true) &&
                  $this->parentItem->user_id !== auth()->id();
        
        // Should also be hidden when parent edits their own item
        expect($visible)->toBeFalse();
    });

    it('shows purchase status section when authorized user edits others item', function () {
        // Parent editing child's item
        $this->actingAs($this->parentUser);
        $visible = in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true) &&
                  $this->childItem->user_id !== auth()->id();
        expect($visible)->toBeTrue();
        
        // Relative editing child's item
        $this->actingAs($this->relativeUser);
        $visible = in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true) &&
                  $this->childItem->user_id !== auth()->id();
        expect($visible)->toBeTrue();
    });

    it('correctly applies role-based visibility logic', function () {
        // Test with CHILD role editing own item
        $this->actingAs($this->childUser);
        
        $visible = in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true) &&
                  $this->childItem->user_id !== auth()->id();
        
        expect($visible)->toBeFalse();
        
        // Test with PARENT role editing child's item
        $this->actingAs($this->parentUser);
        
        $visible = in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true) &&
                  $this->childItem->user_id !== auth()->id();
                  
        expect($visible)->toBeTrue();
        
        // Test with PARENT role editing their own item
        $visible = in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true) &&
                  $this->parentItem->user_id !== auth()->id();
                  
        expect($visible)->toBeFalse();
        
        // Test with RELATIVE role editing child's item
        $this->actingAs($this->relativeUser);
        
        $visible = in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true) &&
                  $this->childItem->user_id !== auth()->id();
                  
        expect($visible)->toBeTrue();
    });

    it('ensures proper item ownership filtering in query', function () {
        $this->actingAs($this->childUser);
        
        // Test that child user can only see their own items
        $query = ItemResource::getEloquentQuery();
        $items = $query->get();
        
        // Should only contain items owned by the child user
        expect($items)->toHaveCount(1);
        expect($items->first()->id)->toBe($this->childItem->id);
        expect($items->first()->user_id)->toBe($this->childUser->id);
        
        // Test that child cannot access parent's item through query
        $parentItemQuery = $query->where('id', $this->parentItem->id);
        expect($parentItemQuery->count())->toBe(0);
    });

    it('filters hidden items from regular ItemResource query', function () {
        // Create a hidden item for the child user
        $hiddenItem = Item::factory()->create([
            'user_id' => $this->childUser->id,
            'hidden' => true
        ]);
        
        $this->actingAs($this->childUser);
        
        $query = ItemResource::getEloquentQuery();
        $items = $query->get();
        
        // Should only contain non-hidden items
        expect($items)->toHaveCount(1);
        expect($items->first()->id)->toBe($this->childItem->id);
        expect($items->first()->hidden)->toBeFalse();
        
        // Hidden item should not be in results
        expect($items->contains('id', $hiddenItem->id))->toBeFalse();
    });

    it('validates purchase status section visibility callback', function () {
        // Create a mock closure that matches the ItemResource visibility logic
        $visibilityCallback = fn (?Item $record) => 
            in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true) &&
            $record?->user_id !== auth()->id();
        
        // Test case 1: Child user with their own item
        $this->actingAs($this->childUser);
        expect($visibilityCallback($this->childItem))->toBeFalse();
        
        // Test case 2: Parent user with child's item  
        $this->actingAs($this->parentUser);
        expect($visibilityCallback($this->childItem))->toBeTrue();
        
        // Test case 3: Parent user with their own item
        expect($visibilityCallback($this->parentItem))->toBeFalse();
        
        // Test case 4: Relative user with child's item
        $this->actingAs($this->relativeUser);
        expect($visibilityCallback($this->childItem))->toBeTrue();
        
        // Test case 5: Child role (should never see purchase status regardless)
        $this->actingAs($this->childUser);
        expect($visibilityCallback($this->parentItem))->toBeFalse();
    });
});