import Alpine from '@alpinejs/csp';
import { registerNavigationComponents } from './navigation';

window.Alpine = Alpine;

registerNavigationComponents(Alpine);
Alpine.start();
