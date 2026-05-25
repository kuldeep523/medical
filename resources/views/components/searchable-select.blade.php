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
    highlightedIndex: -1,

    init() {
        this.syncOptions();
        this.updateSelectedLabel();
        this.isDisabled = this.$refs.originalSelect.disabled;

        this.$watch('value', () => this.updateSelectedLabel());

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
        let selectedOption = Array.from(this.$refs.originalSelect.options)
            .find(opt => opt.value == this.value);

        this.selectedLabel = selectedOption
            ? selectedOption.textContent.trim()
            : '';
    },

    get filteredOptions() {
        return this.optionsList.filter(o =>
            o.text.toLowerCase().includes(this.search.toLowerCase())
        );
    },

    selectOption(val) {
        this.value = val;

        let selected = this.optionsList.find(o => o.value == val);

        this.selectedLabel = selected ? selected.text : '';

        this.search = this.selectedLabel;

        this.open = false;

        this.$refs.originalSelect.dispatchEvent(
            new Event('change', { bubbles: true })
        );

        this.$refs.originalSelect.dispatchEvent(
            new Event('input', { bubbles: true })
        );
    },

    onInput() {
        this.open = true;

        if (this.search === '') {
            this.highlightedIndex = -1;
        } else {
            this.highlightedIndex = 0;
        }
    },

    moveHighlight(direction) {
        if (this.filteredOptions.length === 0) return;

        if (direction === 'down') {
            this.highlightedIndex =
                (this.highlightedIndex + 1) % this.filteredOptions.length;
        } else {
            this.highlightedIndex =
                (this.highlightedIndex - 1 + this.filteredOptions.length) %
                this.filteredOptions.length;
        }
    },

    selectHighlighted() {
        if (this.highlightedIndex >= 0) {
            this.selectOption(
                this.filteredOptions[this.highlightedIndex].value
            );
        }
    }
}"
@click.outside="open = false"
class="position-relative w-100 searchable-select-container">

    <!-- Hidden Select -->
    <select x-ref="originalSelect"
            {{ $attributes->except(['class', 'placeholder']) }}
            class="d-none">
        {{ $slot }}
    </select>

    <!-- Search Input -->
    <div class="position-relative">
        <input type="text"
               x-model="search"
               @focus="open = true"
               @input="onInput()"
               @keydown.arrow-down.prevent="moveHighlight('down')"
               @keydown.arrow-up.prevent="moveHighlight('up')"
               @keydown.enter.prevent="selectHighlighted()"
               :disabled="isDisabled"
               class="form-control form-control-sm pe-4 {{ $attributes->get('class') }}"
               :placeholder="'{{ $placeholder }}'"
               style="font-size:11px; font-weight:600;">

        <span class="position-absolute top-50 end-0 translate-middle-y pe-2 text-muted"
              style="font-size:10px;">
            <i class="bi bi-search"></i>
        </span>
    </div>

    <!-- Dropdown -->
    <div x-show="open"
         x-transition
         class="position-absolute w-100 bg-white border shadow-sm rounded-0 mt-1"
         style="z-index:9999; max-height:220px; overflow-y:auto;">

        <template x-for="(opt,index) in filteredOptions" :key="opt.value">
            <button type="button"
                    @click="selectOption(opt.value)"
                    class="btn btn-sm w-100 text-start rounded-0 border-0 py-1 px-2 select-option-btn"
                    :class="{
                        'active-option': value == opt.value,
                        'highlight-option': highlightedIndex == index
                    }"
                    style="font-size:11px;">

                <span x-text="opt.text"></span>
            </button>
        </template>

        <div x-show="filteredOptions.length === 0"
             class="text-center text-muted py-2"
             style="font-size:10px;">
            No matches found
        </div>
    </div>

    <style>
        .select-option-btn {
            background: transparent;
            color: #333;
        }

        .select-option-btn:hover,
        .highlight-option {
            background: #f1f5f9 !important;
            color: #000 !important;
        }

        .active-option {
            background: #008080 !important;
            color: white !important;
        }
    </style>
</div>