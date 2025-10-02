import './bootstrap';
import { createApp } from 'vue';

// Import components
import CheckoutForm from './components/CheckoutForm.vue';
import PixPayment from './components/PixPayment.vue';

// Create Vue app
const app = createApp({});

// Register components
app.component('checkout-form', CheckoutForm);
app.component('pix-payment', PixPayment);

// Mount app
app.mount('#app');
