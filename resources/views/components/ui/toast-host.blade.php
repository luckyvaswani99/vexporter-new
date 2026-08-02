<div x-data class="fixed bottom-6 right-6 z-[80] flex flex-col gap-3 w-80 pointer-events-none">
    <template x-for="toast in $store.toast.messages" :key="toast.id">
        <div
            x-transition.opacity.duration.300ms
            class="pointer-events-auto flex items-start gap-3 rounded-xl bg-white shadow-2xl border border-gray-100 px-4 py-3"
            :class="{
                'border-l-4 border-l-green-500': toast.type === 'success',
                'border-l-4 border-l-brand-red': toast.type === 'error',
                'border-l-4 border-l-blue-500': toast.type === 'info',
            }"
            role="status"
        >
            <i
                class="mt-0.5"
                :class="{
                    'fas fa-check-circle text-green-500': toast.type === 'success',
                    'fas fa-exclamation-circle text-brand-red': toast.type === 'error',
                    'fas fa-info-circle text-blue-500': toast.type === 'info',
                }"
            ></i>
            <p class="text-sm text-brand-dark flex-1" x-text="toast.message"></p>
            <button type="button" class="text-gray-300 hover:text-gray-500" @click="$store.toast.dismiss(toast.id)" aria-label="Dismiss">
                <i class="fas fa-xmark text-xs"></i>
            </button>
        </div>
    </template>
</div>
