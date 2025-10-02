<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl max-w-md w-full p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold">Pay with PIX</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Amount -->
      <div class="text-center mb-6">
        <p class="text-gray-600 mb-2">Amount to pay</p>
        <p class="text-3xl font-bold text-primary-600">
          R$ {{ formatPrice(amount) }}
        </p>
      </div>

      <!-- QR Code -->
      <div class="bg-gray-50 rounded-lg p-6 mb-6">
        <div class="flex justify-center mb-4">
          <img v-if="qrCodeUrl" :src="qrCodeUrl" alt="PIX QR Code" class="w-48 h-48" />
          <div v-else class="w-48 h-48 bg-white rounded-lg flex items-center justify-center">
            <svg class="w-32 h-32 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
              <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-2zm-10 8h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v3h-3v-3zm0 5h3v3h-3v-3zm-5-5h3v8h-3v-8z"/>
            </svg>
          </div>
        </div>

        <!-- QR Code String (for copy) -->
        <div class="relative">
          <input
            ref="qrCodeInput"
            :value="qrCode"
            readonly
            class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-mono text-gray-600 pr-20"
          />
          <button
            @click="copyQRCode"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 px-3 py-1 bg-primary-600 text-white text-sm rounded hover:bg-primary-700 transition-colors"
          >
            {{ copied ? 'Copied!' : 'Copy' }}
          </button>
        </div>
      </div>

      <!-- Instructions -->
      <div class="space-y-3 mb-6">
        <div class="flex gap-3">
          <div class="flex-shrink-0 w-6 h-6 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-semibold">
            1
          </div>
          <p class="text-sm text-gray-600">Open your bank app and select PIX payment</p>
        </div>

        <div class="flex gap-3">
          <div class="flex-shrink-0 w-6 h-6 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-semibold">
            2
          </div>
          <p class="text-sm text-gray-600">Scan the QR Code or paste the code</p>
        </div>

        <div class="flex gap-3">
          <div class="flex-shrink-0 w-6 h-6 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-semibold">
            3
          </div>
          <p class="text-sm text-gray-600">Confirm the payment in your bank app</p>
        </div>
      </div>

      <!-- Status -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-yellow-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
          </svg>
          <p class="text-sm text-yellow-800">Waiting for payment confirmation...</p>
        </div>
      </div>

      <!-- Expiration Warning -->
      <p class="text-xs text-gray-500 text-center">
        This QR Code expires in 30 minutes
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PixPayment',
  
  props: {
    qrCode: {
      type: String,
      required: true
    },
    qrCodeUrl: {
      type: String,
      required: true
    },
    amount: {
      type: Number,
      required: true
    }
  },

  data() {
    return {
      copied: false
    };
  },

  methods: {
    formatPrice(price) {
      return price.toFixed(2).replace('.', ',');
    },

    copyQRCode() {
      this.$refs.qrCodeInput.select();
      document.execCommand('copy');
      this.copied = true;

      setTimeout(() => {
        this.copied = false;
      }, 2000);
    }
  }
};
</script>
