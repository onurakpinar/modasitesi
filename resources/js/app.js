import Alpine from 'alpinejs';
import { registerNavigationComponents } from './navigation';

window.Alpine = Alpine;

registerNavigationComponents(Alpine);
Alpine.start();
