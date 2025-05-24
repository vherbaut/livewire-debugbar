<div class="livewire-debugbar__component">
    <div class="livewire-debugbar__component-header">
        <div class="livewire-debugbar__component-info">
            <span class="livewire-debugbar__component-name" x-text="component.name"></span>
            <code class="livewire-debugbar__component-id" x-text="id"></code>
            <span class="livewire-debugbar__badge livewire-debugbar__badge--locked" x-text="component.state"></span>
        </div>

        <div class="livewire-debugbar__component-actions">
            <button class="livewire-debugbar__btn livewire-debugbar__btn--icon"
                    @click="refreshComponent(id)"
            >
                <svg class="livewire-debugbar__btn__icon" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="livewire-debugbar__component-body">
        @include('livewire-debugbar::partials.components.properties')
    </div>
</div>
