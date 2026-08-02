import '@fortawesome/fontawesome-free/css/all.min.css';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import persist from '@alpinejs/persist';

import registerStores from './alpine/stores';
import registerComponents from './alpine/components';
import initScrollReveal from './alpine/scroll-reveal';

Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(persist);

registerStores(Alpine);
registerComponents(Alpine);

window.Alpine = Alpine;
Alpine.start();

initScrollReveal();
