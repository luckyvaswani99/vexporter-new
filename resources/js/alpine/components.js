import http from './http';

/**
 * Reusable Alpine components used across the storefront.
 */
export default function registerComponents(Alpine) {
    Alpine.data('countdown', (endsAt) => ({
        days: '00',
        hours: '00',
        minutes: '00',
        seconds: '00',
        expired: false,
        timer: null,

        init() {
            this.tick();
            this.timer = setInterval(() => this.tick(), 1000);
        },

        destroy() {
            clearInterval(this.timer);
        },

        tick() {
            const diff = new Date(endsAt).getTime() - Date.now();

            if (diff <= 0) {
                this.expired = true;
                clearInterval(this.timer);

                return;
            }

            const pad = (value) => String(value).padStart(2, '0');

            this.days = pad(Math.floor(diff / 86400000));
            this.hours = pad(Math.floor((diff % 86400000) / 3600000));
            this.minutes = pad(Math.floor((diff % 3600000) / 60000));
            this.seconds = pad(Math.floor((diff % 60000) / 1000));
        },
    }));

    Alpine.data('searchAutocomplete', () => ({
        query: '',
        vertical: '',
        results: { products: [], categories: [], vendors: [] },
        open: false,
        loading: false,
        debounce: null,

        onInput() {
            clearTimeout(this.debounce);

            if (this.query.trim().length < 2) {
                this.open = false;

                return;
            }

            this.debounce = setTimeout(() => this.fetch(), 250);
        },

        async fetch() {
            this.loading = true;

            try {
                const params = new URLSearchParams({ q: this.query, vertical: this.vertical });

                this.results = await http.get(`/x/search/suggest?${params}`);
                this.open = true;
            } catch {
                this.open = false;
            } finally {
                this.loading = false;
            }
        },

        get hasResults() {
            return this.results.products.length || this.results.categories.length || this.results.vendors.length;
        },
    }));

    Alpine.data('quantityStepper', (moq = 1, step = 1, max = null) => ({
        qty: moq,

        increase() {
            const next = this.qty + step;

            this.qty = max ? Math.min(next, max) : next;
        },

        decrease() {
            this.qty = Math.max(moq, this.qty - step);
        },

        normalize() {
            const value = Number(this.qty) || moq;

            this.qty = Math.max(moq, Math.ceil(value / step) * step);
        },
    }));

    Alpine.data('addToCart', (productId, variantId = null) => ({
        busy: false,

        async submit(qty = 1) {
            if (this.busy) {
                return;
            }

            this.busy = true;

            try {
                await Alpine.store('cart').add({ product_id: productId, variant_id: variantId, qty });
            } catch {
                // Toast already surfaced by the store.
            } finally {
                this.busy = false;
            }
        },
    }));

    /** Quantity stepper + add-to-cart in one component for the PDP buy box. */
    Alpine.data('buyBox', (productId, moq = 1, step = 1, variants = []) => ({
        qty: moq,
        busy: false,
        variants,
        // Variable products open on their default option; everything else has none.
        variantId: variants.find((variant) => variant.is_default)?.id ?? variants[0]?.id ?? null,

        get variant() {
            return this.variants.find((option) => option.id === this.variantId) ?? null;
        },

        get priceLabel() {
            return this.variant?.price_label ?? null;
        },

        get stockLabel() {
            return this.variant && this.variant.stock_qty <= 0 ? 'Made to order' : null;
        },

        increase() {
            this.qty += step;
        },

        decrease() {
            this.qty = Math.max(moq, this.qty - step);
        },

        normalize() {
            const value = Number(this.qty) || moq;

            this.qty = Math.max(moq, moq + Math.ceil((value - moq) / step) * step);
        },

        async add() {
            if (this.busy) {
                return;
            }

            this.busy = true;
            this.normalize();

            try {
                await Alpine.store('cart').add({
                    product_id: productId,
                    qty: this.qty,
                    variant_id: this.variantId,
                });
            } catch {
                // The store already surfaced the error toast.
            } finally {
                this.busy = false;
            }
        },
    }));

    Alpine.data('productGallery', (images = []) => ({
        images,
        active: 0,

        select(index) {
            this.active = index;
        },

        get current() {
            return this.images[this.active] ?? null;
        },
    }));

    Alpine.data('newsletterForm', (url) => ({
        email: '',
        busy: false,
        done: false,

        async submit() {
            if (this.busy) {
                return;
            }

            this.busy = true;

            try {
                await http.post(url, { email: this.email });

                this.done = true;
                this.email = '';
                Alpine.store('toast').push('You are subscribed. Welcome aboard!', 'success');
            } catch (error) {
                Alpine.store('toast').push(error.message, 'error');
            } finally {
                this.busy = false;
            }
        },
    }));

    Alpine.data('dropdown', () => ({
        open: false,

        toggle() {
            this.open = ! this.open;
        },

        close() {
            this.open = false;
        },
    }));
}
