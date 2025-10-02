<template>
  <div class="max-w-4xl mx-auto py-8 px-4">
    <!-- Product Summary -->
    <div class="card mb-6">
      <h2 class="text-2xl font-bold mb-4">{{ product.name }}</h2>
      <p class="text-gray-600 mb-4">{{ product.description }}</p>
      <div class="flex items-baseline gap-2">
        <span class="text-3xl font-bold text-primary-600">
          R$ {{ formatPrice(product.price) }}
        </span>
        <span class="text-gray-500">/ {{ product.interval }}</span>
      </div>
    </div>

    <!-- Checkout Form -->
    <form @submit.prevent="processCheckout" class="space-y-6">
      <!-- Customer Information -->
      <div class="card">
        <h3 class="text-xl font-semibold mb-4">Customer Information</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Full Name *
            </label>
            <input
              v-model="form.customer_name"
              type="text"
              class="input-field"
              :class="{ 'border-red-500': errors.customer_name }"
              required
            />
            <p v-if="errors.customer_name" class="text-red-500 text-sm mt-1">
              {{ errors.customer_name[0] }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Email *
            </label>
            <input
              v-model="form.customer_email"
              type="email"
              class="input-field"
              :class="{ 'border-red-500': errors.customer_email }"
              required
            />
            <p v-if="errors.customer_email" class="text-red-500 text-sm mt-1">
              {{ errors.customer_email[0] }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              CPF (optional)
            </label>
            <input
              v-model="form.customer_cpf"
              type="text"
              class="input-field"
              placeholder="000.000.000-00"
              maxlength="14"
              @input="formatCPF"
            />
          </div>
        </div>
      </div>

      <!-- Payment Method Selection -->
      <div class="card">
        <h3 class="text-xl font-semibold mb-4">Payment Method</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <!-- PIX Option -->
          <div
            class="payment-method-card"
            :class="{ active: form.payment_method === 'pix' }"
            @click="form.payment_method = 'pix'"
          >
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-teal-600" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                </svg>
              </div>
              <div>
                <p class="font-semibold">PIX</p>
                <p class="text-sm text-gray-500">Instant payment</p>
              </div>
            </div>
          </div>

          <!-- Credit Card Option -->
          <div
            class="payment-method-card"
            :class="{ active: form.payment_method === 'credit_card' }"
            @click="form.payment_method = 'credit_card'"
          >
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
              </div>
              <div>
                <p class="font-semibold">Credit Card</p>
                <p class="text-sm text-gray-500">Visa, Mastercard, etc.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Credit Card Form -->
        <div v-if="form.payment_method === 'credit_card'" class="space-y-4 border-t pt-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Card Number *
            </label>
            <input
              v-model="form.card_number"
              type="text"
              class="input-field"
              :class="{ 'border-red-500': errors.card_number }"
              placeholder="0000 0000 0000 0000"
              maxlength="19"
              @input="formatCardNumber"
              required
            />
            <p v-if="errors.card_number" class="text-red-500 text-sm mt-1">
              {{ errors.card_number[0] }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Cardholder Name *
            </label>
            <input
              v-model="form.card_holder_name"
              type="text"
              class="input-field"
              :class="{ 'border-red-500': errors.card_holder_name }"
              placeholder="NAME ON CARD"
              required
            />
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Month *
              </label>
              <input
                v-model="form.card_exp_month"
                type="text"
                class="input-field"
                placeholder="MM"
                maxlength="2"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Year *
              </label>
              <input
                v-model="form.card_exp_year"
                type="text"
                class="input-field"
                placeholder="YYYY"
                maxlength="4"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                CVV *
              </label>
              <input
                v-model="form.card_cvv"
                type="text"
                class="input-field"
                placeholder="123"
                maxlength="4"
                required
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Terms and Conditions -->
      <div class="card">
        <label class="flex items-start gap-3 cursor-pointer">
          <input
            v-model="form.terms_accepted"
            type="checkbox"
            class="mt-1 w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
            required
          />
          <span class="text-sm text-gray-700">
            I agree to the <a href="#" class="text-primary-600 hover:underline">Terms and Conditions</a> and <a href="#" class="text-primary-600 hover:underline">Privacy Policy</a> *
          </span>
        </label>
        <p v-if="errors.terms_accepted" class="text-red-500 text-sm mt-2">
          {{ errors.terms_accepted[0] }}
        </p>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        class="btn-primary w-full py-4 text-lg"
        :disabled="processing"
      >
        <span v-if="!processing">
          Complete Payment - R$ {{ formatPrice(product.price) }}
        </span>
        <span v-else class="flex items-center justify-center gap-2">
          <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Processing...
        </span>
      </button>

      <!-- Error Message -->
      <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-700">{{ errorMessage }}</p>
      </div>
    </form>

    <!-- PIX Modal -->
    <pix-payment
      v-if="showPixModal"
      :qr-code="pixData.qr_code"
      :qr-code-url="pixData.qr_code_url"
      :amount="product.price"
      @close="showPixModal = false"
    />
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'CheckoutForm',
  
  props: {
    product: {
      type: Object,
      required: true
    },
    gateway: {
      type: String,
      default: 'dummy'
    }
  },

  data() {
    return {
      form: {
        customer_name: '',
        customer_email: '',
        customer_cpf: '',
        payment_method: 'pix',
        gateway: this.gateway,
        product_name: this.product.name,
        product_type: this.product.type,
        amount: this.product.price,
        plan_interval: this.product.interval,
        card_number: '',
        card_holder_name: '',
        card_exp_month: '',
        card_exp_year: '',
        card_cvv: '',
        terms_accepted: false
      },
      processing: false,
      errors: {},
      errorMessage: '',
      showPixModal: false,
      pixData: {}
    };
  },

  methods: {
    async processCheckout() {
      this.processing = true;
      this.errors = {};
      this.errorMessage = '';

      try {
        const response = await axios.post('/checkout/process', this.form);

        if (response.data.success) {
          if (this.form.payment_method === 'pix') {
            // Show PIX modal
            this.pixData = response.data.payment_data;
            this.showPixModal = true;
          } else {
            // Redirect to success page
            window.location.href = response.data.redirect_url;
          }
        }
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors || {};
        }
        this.errorMessage = error.response?.data?.message || 'An error occurred. Please try again.';
      } finally {
        this.processing = false;
      }
    },

    formatPrice(price) {
      return price.toFixed(2).replace('.', ',');
    },

    formatCPF(event) {
      let value = event.target.value.replace(/\D/g, '');
      if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.form.customer_cpf = value;
      }
    },

    formatCardNumber(event) {
      let value = event.target.value.replace(/\D/g, '');
      value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
      this.form.card_number = value.trim();
    }
  }
};
</script>
