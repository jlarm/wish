<div x-data="{
    showFilters: false,
    activeFilter: 'all'
}" class="space-y-6 w-full">
    <!-- Header with Search and Filters -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search items..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                >
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- User Filter -->
            <div class="relative">
                <select 
                    wire:model.live="selectedUser"
                    class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2.5 pr-10 focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-0"
                >
                    <option value="">All People</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            <!-- Sort Filter -->
            <div class="relative">
                <select 
                    wire:model.live="sortBy"
                    class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2.5 pr-10 focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-0"
                >
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                    <option value="alphabetical">A-Z</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="price_low">Price: Low to High</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filter Buttons -->
    <div class="flex flex-wrap gap-3" x-data="{ activeQuickFilter: 'all' }">
        <button 
            @click="activeQuickFilter = 'all'; $wire.set('selectedUser', '')"
            :class="activeQuickFilter === 'all' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
            class="px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200"
        >
            All Items
        </button>
        
        @foreach($users as $user)
            <button 
                @click="activeQuickFilter = '{{ $user->id }}'; $wire.set('selectedUser', '{{ $user->id }}')"
                :class="activeQuickFilter === '{{ $user->id }}' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                class="px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200"
            >
                {{ $user->name }}
            </button>
        @endforeach
    </div>

    <!-- Items Grid -->
    <div class="grid grid-cols-3 gap-6 w-full">
        @forelse($items as $item)
            <div 
                x-data="{ hovered: false }"
                @mouseenter="hovered = true"
                @mouseleave="hovered = false"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1 flex flex-col min-w-0"
            >
                <!-- Image -->
                <div class="relative aspect-square bg-gray-100 dark:bg-gray-700">
                    @if($item->image)
                        <img 
                            src="{{ Storage::url($item->image) }}" 
                            alt="{{ $item->name }}"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Status Badges -->
                    <div class="absolute top-3 right-3 flex gap-2">
                        @if($item->purchased)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Purchased
                            </span>
                        @endif
                        
                        @if($item->delivered)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                                </svg>
                                Delivered
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4 flex-1 flex flex-col">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-lg leading-tight line-clamp-2">
                            {{ $item->name }}
                        </h3>
                        @if($item->price)
                            <span class="text-lg font-bold text-red-600 dark:text-red-400 ml-2">
                                ${{ number_format($item->price, 2) }}
                            </span>
                        @endif
                    </div>

                    <!-- Person -->
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300 font-medium">
                            {{ $item->user->name }}
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="space-y-2 mb-4">
                        @if($item->size || $item->color)
                            <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                                @if($item->size)
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                        </svg>
                                        {{ $item->size }}
                                    </span>
                                @endif
                                @if($item->color)
                                    <span class="flex items-center gap-1">
                                        <div class="w-3 h-3 rounded-full border border-gray-300" style="background-color: {{ $item->color }}"></div>
                                        {{ $item->color }}
                                    </span>
                                @endif
                            </div>
                        @endif
                        
                        @if($item->store)
                            <div class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                {{ $item->store }}
                            </div>
                        @endif
                    </div>

                    <!-- Link Button -->
                    <div class="mt-auto pt-2">
                        @if($item->link)
                            <a 
                                href="{{ $item->link }}" 
                                target="_blank"
                                class="inline-flex items-center gap-2 text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                View Item
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m0 0v-2a1 1 0 011-1h1m0 0V8a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h1a1 1 0 011-1V6"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No items found</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    @if($search)
                        No items match your search criteria.
                    @else
                        There are no items to display yet.
                    @endif
                </p>
            </div>
        @endforelse
    </div>
    
    <!-- Results Count -->
    @if($items->count() > 0)
        <div class="text-center text-sm text-gray-500 dark:text-gray-400 pt-6 border-t border-gray-200 dark:border-gray-700">
            Showing {{ $items->count() }} {{ Str::plural('item', $items->count()) }}
        </div>
    @endif
</div>