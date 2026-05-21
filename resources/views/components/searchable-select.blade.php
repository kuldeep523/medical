@props([
    'placeholder' => 'Select option...',
    'id' => null,
])

<div x-data="{
    open: false,
    search: '',
    value: @entangle($attributes->wire('model')),
    optionsList: [],
    selectedLabel: '',
    isDisabled: false,
    
    init() {
        this.syncOptions();
        this.updateSelectedLabel();
        this.isDisabled = this.$refs.originalSelect.disabled;
        
        this.$watch('value', () => this.updateSelectedLabel());
        
        // Setup observer to detect when Livewire updates options list or disabled state
        const observer = new MutationObserver(() => {
            this.syncOptions();
            this.updateSelectedLabel();
            this.isDisabled = this.$refs.originalSelect.disabled;
        });
        observer.observe(this.$refs.originalSelect, { 
            childList: true, 
            subtree: true, 
            attributes: true, 
            attributeFilter: ['disabled'] 
        });
    },
    
    syncOptions() {
        this.optionsList = Array.from(this.$refs.originalSelect.options).map(opt => ({
            value: opt.value,
            text: opt.textContent.trim()
        }));
    },
    
    updateSelectedLabel() {
        let selectedOption = Array.from(this.$refs.originalSelect.options).find(opt => opt.value == this.value);
        this.selectedLabel = selectedOption ? selectedOption.textContent.trim() : '{{ $placeholder }}';
    },

    selectOption(val) {
        this.value = val;
        this.open = false;
        this.search = '';
        this.$refs.originalSelect.dispatchEvent(new Event('change', { bubbles: true }));
        this.$refs.originalSelect.dispatchEvent(new Event('input', { bubbles: true }));
    }
}" @click.outside="open = false" class="position-relative w-100 searchable-select-container">

    <!-- Hidden Select (Livewire interacts with this) -->
    <select x-ref="originalSelect" {{ $attributes->except(['class', 'placeholder']) }} class="d-none">
        {{ $slot }}
    </select>

    <!-- Trigger Button -->
    <button type="button" 
            @click="if(!isDisabled) open = !open" 
            :disabled="isDisabled"
            class="form-select form-select-sm text-start bg-white d-flex align-items-center justify-content-between {{ $attributes->get('class') }}"
            style="min-height: 28px; font-size: 11px; border-color: rgba(0,0,0,.15); font-weight: bold; color: #333; cursor: pointer;">
        <span x-text="selectedLabel" class="text-truncate pe-2"></span>
    </button>

    <!-- Dropdown List Panel -->
    <div x-show="open" 
         x-transition
         class="position-absolute w-100 bg-white border shadow-sm rounded-0 p-1" 
         style="z-index: 9999; top: 100%; left: 0; max-height: 250px; display: flex; flex-direction: column; min-width: 220px;">
        
        <!-- Search Input -->
        <div class="p-1 border-bottom">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 rounded-0 py-0 px-2"><i class="bi bi-search text-muted" style="font-size: 10px;"></i></span>
                <input type="text" x-model="search" 
                       class="form-control form-control-sm rounded-0 border-start-0 py-1" 
                       placeholder="Type to search..." 
                       style="font-size: 11px; outline: none; box-shadow: none; height: 24px;"
                       @click.stop>
            </div>
        </div>

        <!-- Options Container -->
        <div class="overflow-auto flex-grow-1 mt-1" style="max-height: 180px;">
            <template x-for="opt in optionsList.filter(o => o.text.toLowerCase().includes(search.toLowerCase()))" :key="opt.value">
                <button type="button" @click="selectOption(opt.value)" 
                        class="btn btn-sm w-100 text-start rounded-0 py-1 px-2 border-0 d-block select-option-btn"
                        :class="value == opt.value ? 'active-option' : ''"
                        style="font-size: 11px; font-weight: 500; height: 26px; line-height: 1.2;">
                    <span x-text="opt.text"></span>
                </button>
            </template>
            <div x-show="optionsList.filter(o => o.text.toLowerCase().includes(search.toLowerCase())).length === 0" 
                 class="text-muted text-center py-2" style="font-size: 10px;">
                No matches found
            </div>
        </div>
    </div>

    <style>
        .select-option-btn {
            background: transparent;
            color: #333;
        }
        .select-option-btn:hover {
            background-color: #f1f5f9;
            color: #000;
        }
        .active-option {
            background-color: #008080 !important;
            color: white !important;
        }
        .searchable-select-container .form-select::after {
            display: none;
        }
    </style>
</div>
