import http from './http';

/**
 * Global Alpine stores. The cart/wishlist keep an optimistic local copy so the
 * header badges update instantly, then reconcile with the server response.
 */
export default function registerStores(Alpine) {
    Alpine.store('ui', {
        mobileMenu: false,
        cartDrawer: false,
        filterDrawer: false,
        search: false,

        toggle(key) {
            this[key] = ! this[key];
            document.body.classList.toggle('overflow-hidden', this.cartDrawer || this.filterDrawer || this.mobileMenu);
        },

        close(key) {
            this[key] = false;
            document.body.classList.remove('overflow-hidden');
        },
    });

    Alpine.store('cart', {
        count: window.VEXPORTER?.cart?.count ?? 0,
        total: window.VEXPORTER?.cart?.total ?? '0.00',
        items: [],
        loading: false,

        async load() {
            this.loading = true;

            try {
                this.apply(await http.get('/x/cart'));
            } finally {
                this.loading = false;
            }
        },

        async add(payload) {
            this.count += payload.qty ?? 1;

            try {
                this.apply(await http.post('/x/cart/items', payload));
                Alpine.store('toast').push('Added to cart', 'success');
            } catch (error) {
                this.count = Math.max(0, this.count - (payload.qty ?? 1));
                Alpine.store('toast').push(error.message, 'error');
                throw error;
            }
        },

        async update(itemId, qty) {
            this.apply(await http.patch(`/x/cart/items/${itemId}`, { qty }));
        },

        async remove(itemId) {
            this.apply(await http.delete(`/x/cart/items/${itemId}`));
        },

        apply(payload) {
            if (! payload) {
                return;
            }

            this.count = payload.count ?? this.count;
            this.total = payload.total ?? this.total;
            this.items = payload.items ?? this.items;
        },
    });

    Alpine.store('wishlist', {
        ids: window.VEXPORTER?.wishlist ?? [],

        has(productId) {
            return this.ids.includes(productId);
        },

        async toggle(productId) {
            const wasIn = this.has(productId);

            this.ids = wasIn ? this.ids.filter((id) => id !== productId) : [...this.ids, productId];

            try {
                await http.post('/x/wishlist/toggle', { product_id: productId });
            } catch (error) {
                this.ids = wasIn ? [...this.ids, productId] : this.ids.filter((id) => id !== productId);
                Alpine.store('toast').push(error.message, 'error');
            }
        },
    });

    Alpine.store('toast', {
        messages: [],
        nextId: 1,

        push(message, type = 'info') {
            const id = this.nextId++;

            this.messages.push({ id, message, type });

            setTimeout(() => this.dismiss(id), 4000);
        },

        dismiss(id) {
            this.messages = this.messages.filter((toast) => toast.id !== id);
        },
    });
}
