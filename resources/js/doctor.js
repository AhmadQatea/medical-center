import './bootstrap';
import Alpine from 'alpinejs';
import { registerBookingFlow } from './booking-flow';

document.addEventListener('alpine:init', () => {
    registerBookingFlow(Alpine);
});

window.Alpine = Alpine;
Alpine.start();
