# Maria Checkout

A customizable payment gateway checkout system built with Laravel and Vue.js, designed for easy integration with multiple payment providers.

## Developer
Develped by **Leonardo G. Tellez Saucedo** with Claude Sonnet 4.5 and DeepSeek V3.2 assistance.

Email: leonardo616@gmail.com

## Features

- 🎨 **Modern UI** - Clean, responsive design with Tailwind CSS
- 💳 **Multiple Payment Methods** - PIX and Credit Card support
- 🔌 **Gateway Agnostic** - Easy to switch between payment providers
- 🔄 **Subscription Management** - Recurring payments and subscription handling
- 📊 **Webhook Processing** - Automatic payment status updates
- 🛡️ **Secure** - PCI compliant card handling with tokenization
- 🌐 **API Ready** - RESTful API for subscription management

## Tech Stack

- **Backend:** Laravel 11.x, PHP 8.2+
- **Frontend:** Vue 3, Tailwind CSS or Alpine.js and Blade(alternativly)
- **Database:** MySQL 8.0+
- **Build Tool:** Vite

## Payment Gateways Supported

- ✅ Stripe
- ✅ Dummy Gateway (for testing)
- 🔜 Pagar.me
- 🔜 Mercado Pago

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and NPM
- MySQL 8.0+
- WampServer (for Windows) or similar local server

### Setup

1. **Clone the repository**
   ```bash
   cd C:\wamp64\www
   https://github.com/mrmustard123/custom_checkout.git
   cd maria-checkout
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database in `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=maria_checkout
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Configure payment gateway** (optional for production)
   ```env
   PAYMENT_GATEWAY_DEFAULT=dummy
   
   # For Stripe
   # STRIPE_SECRET_KEY=your_secret_key
   # STRIPE_PUBLISHABLE_KEY=your_publishable_key
   # STRIPE_WEBHOOK_SECRET=your_webhook_secret
   ```

7. **Run migrations**
   ```bash
   php artisan migrate
   ```

8. **Build frontend assets**
   ```bash
   # Development mode (with hot reload)
   npm run dev
   
   # OR Production build
   npm run build
   ```

9. **Start the development server**
   ```bash
   php artisan serve
   ```

10. **Access the application**
    ```
    http://localhost:8000/maria_checkout/
    ```

## Project Structure

```
maria-checkout/
├── app/
│   ├── Http/Controllers/
│   │   ├── CheckoutController.php      # Handles checkout process
│   │   ├── WebhookController.php       # Processes payment webhooks
│   │   └── SubscriptionController.php  # Manages subscriptions
│   ├── Models/
│   │   ├── Customer.php
│   │   ├── Order.php
│   │   ├── Subscription.php
│   │   ├── PaymentMethod.php
│   │   └── WebhookLog.php
│   └── Services/
│       └── PaymentGateways/
│           ├── Contracts/
│           │   └── PaymentGatewayInterface.php
│           ├── Gateways/
│           │   ├── DummyGateway.php
│           │   └── StripeGateway.php
│           ├── AbstractPaymentGateway.php
│           └── PaymentGatewayFactory.php
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── CheckoutForm.vue
│   │   │   └── PixPayment.vue
│   │   ├── app.js
│   │   └── bootstrap.js
│   ├── css/
│   │   └── app.css
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       └── checkout/
│           ├── show.blade.php
│           ├── success.blade.php
│           └── error.blade.php
├── config/
│   └── payment_gateways.php
└── routes/
    └── web.php
```

## Usage

### Basic Checkout Flow

1. Customer visits `/checkout/{product}`
2. Fills in personal information
3. Selects payment method (PIX or Credit Card)
4. Submits payment
5. Redirected to success/error page

### Testing with Dummy Gateway

Use these test cards:

- **Visa:** `4111 1111 1111 1111`
- **Mastercard:** `5555 5555 5555 4444`
- **Any future expiry date and CVV**

### API Endpoints

#### Checkout
```bash
GET  /checkout/{product?}           # Show checkout page
POST /checkout/process              # Process payment
GET  /checkout/success/{order}      # Success page
```

#### Webhooks
```bash
POST /webhook/stripe                # Stripe webhooks
POST /webhook/pagarme               # Pagar.me webhooks
POST /webhook/dummy                 # Dummy gateway webhooks
```

#### Subscriptions
```bash
GET  /subscriptions                 # List customer subscriptions
POST /subscriptions/{id}/cancel     # Cancel subscription
POST /subscriptions/{id}/resume     # Resume subscription
POST /subscriptions/{id}/sync       # Sync with gateway
```

## Adding New Payment Gateways

1. **Create gateway class**
   ```php
   // app/Services/PaymentGateways/Gateways/NewGateway.php
   class NewGateway extends AbstractPaymentGateway
   {
       // Implement interface methods
   }
   ```

2. **Register in factory**
   ```php
   // app/Services/PaymentGateways/PaymentGatewayFactory.php
   private static array $gateways = [
       'new_gateway' => NewGateway::class,
   ];
   ```

3. **Add configuration**
   ```php
   // config/payment_gateways.php
   'new_gateway' => [
       'api_key' => env('NEW_GATEWAY_API_KEY'),
   ],
   ```

## Database Schema

### Main Tables

- **customers** - Customer information
- **orders** - Payment orders
- **subscriptions** - Recurring subscriptions
- **payment_methods** - Saved payment methods
- **webhook_logs** - Webhook processing logs

## Configuration

### Environment Variables

```env
# App
APP_URL=http://localhost/maria-checkout/public

# Payment Gateway
PAYMENT_GATEWAY_DEFAULT=dummy

# PIX
PIX_EXPIRATION_MINUTES=30

# Subscriptions
SUBSCRIPTION_TRIAL_DAYS=0
SUBSCRIPTION_GRACE_PERIOD_DAYS=3
SUBSCRIPTION_MAX_FAILED_ATTEMPTS=3
```

## Security

- ✅ CSRF protection on all forms
- ✅ Webhook signature verification
- ✅ Card data never stored (tokenization)
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Blade templating)
- ✅ Rate limiting on API endpoints

## Logging

Payment gateway activities are logged to:
```
storage/logs/payment-gateways.log
```

## WordPress Integration

For WordPress integration, the checkout can receive user data via JWT token:

```javascript
// In WordPress
const token = generateJWT(userId, productId);
window.location.href = `checkout.domain.com?token=${token}`;

// Laravel validates and pre-fills form
```

## Troubleshooting

### Assets not loading
```bash
npm run build
php artisan optimize:clear
```

### Database issues
```bash
php artisan migrate:fresh
```

### Permission errors (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
```

## Development

### Code Style
- Follow PSR-12 standards
- Use meaningful variable names
- Comment complex logic
- Write in English

### Testing
```bash
php artisan test
```

## Production Deployment

1. Set environment to production
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. Build assets
   ```bash
   npm run build
   ```

3. Optimize Laravel
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. Set proper permissions
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

5. Configure SSL certificate

6. Set up cron for scheduled tasks
   ```bash
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

The Laravel framework is open-sourced software licensed under the MIT license.

## Support

For support, email: support@example.com

## Changelog

### Version 1.0.0 (2024)
- Initial release
- PIX and Credit Card support
- Stripe integration
- Subscription management
- Webhook processing
- Vue.js checkout interface

---

**Built with ❤️ for Maria's business**
