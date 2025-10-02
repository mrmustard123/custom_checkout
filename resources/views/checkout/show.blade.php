<!DOCTYPE html>
<html lang="pt-BR"> 
    <head> 
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>Checkout - {{ $product['name'] ?? 'Produto' }}</title> 
        <script src="https://cdn.tailwindcss.com"></script> 
        <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.0/cdn.min.js"></script> 
    </head> 
    <body class="bg-gray-50 min-h-screen"> 
        <div class="container mx-auto px-4 py-8 max-w-2xl"> 
            <!-- Header --> 
            <div class="text-center mb-8"> 
                <h1 class="text-3xl font-bold text-gray-800">Finalizar Compra</h1> 
                <p class="text-gray-600 mt-2">{{ $product['name'] ?? 'Produto' }}</p> 
            </div>
            
            <!-- Progress Steps -->
            <div class="flex justify-center mb-8">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">1</div>
                        <span class="ml-2 text-sm font-medium text-blue-600">Informações</span>
                    </div>
                    <div class="w-12 h-0.5 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">2</div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Pagamento</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
                @csrf

                <!-- Product Summary -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Resumo do Pedido</h2>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-800">{{ $product['name'] ?? 'Produto' }}</p>
                            <p class="text-sm text-gray-600">{{ $product['description'] ?? '' }}</p>
                        </div>
                        <p class="text-xl font-bold text-blue-600">R$ {{ number_format($product['amount'] ?? 0, 2, ',', '.') }}</p>
                    </div>
                    <input type="hidden" name="product_name" value="{{ $product['name'] ?? 'Produto' }}">
                    <input type="hidden" name="product_type" value="{{ $product['type'] ?? 'one_time' }}">
                    <input type="hidden" name="product_description" value="{{ $product['description'] ?? '' }}">
                    <input type="hidden" name="amount" value="{{ $product['amount'] ?? 0 }}">
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Seus Dados</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                            <input type="text" name="customer_name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Seu nome completo">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                            <input type="email" name="customer_email" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="seu@email.com">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                            <input type="text" name="customer_cpf"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="000.000.000-00">
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Forma de Pagamento</h2>

                    <!-- Gateway Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gateway de Pagamento</label>
                        <select name="gateway" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="dummy">Dummy Gateway (Teste)</option>
                            <option value="stripe">Stripe</option>
                        </select>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" x-data="{ paymentMethod: 'pix' }">
                        <div>
                            <input type="radio" name="payment_method" value="pix" id="pix" x-model="paymentMethod" class="hidden">
                            <label for="pix" class="flex items-center p-4 border-2 rounded-lg cursor-pointer"
                                :class="paymentMethod === 'pix' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                                <div class="w-6 h-6 rounded-full border-2 mr-3 flex items-center justify-center"
                                    :class="paymentMethod === 'pix' ? 'border-blue-500 bg-blue-500' : 'border-gray-400'">
                                    <div x-show="paymentMethod === 'pix'" class="w-2 h-2 rounded-full bg-white"></div>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-800">PIX</span>
                                    <p class="text-sm text-gray-600">Pagamento instantâneo</p>
                                </div>
                            </label>
                        </div>

                        <div>
                            <input type="radio" name="payment_method" value="credit_card" id="credit_card" x-model="paymentMethod" class="hidden">
                            <label for="credit_card" class="flex items-center p-4 border-2 rounded-lg cursor-pointer"
                                :class="paymentMethod === 'credit_card' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                                <div class="w-6 h-6 rounded-full border-2 mr-3 flex items-center justify-center"
                                    :class="paymentMethod === 'credit_card' ? 'border-blue-500 bg-blue-500' : 'border-gray-400'">
                                    <div x-show="paymentMethod === 'credit_card'" class="w-2 h-2 rounded-full bg-white"></div>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-800">Cartão de Crédito</span>
                                    <p class="text-sm text-gray-600">À vista</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Credit Card Fields -->
                    <div x-show="paymentMethod === 'credit_card'" x-cloak class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número do Cartão</label>
                            <input type="text" name="card_number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0000 0000 0000 0000">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome no Cartão</label>
                            <input type="text" name="card_holder"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Como está no cartão">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Validade</label>
                                <div class="flex space-x-2">
                                    <input type="text" name="card_exp_month" placeholder="MM"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="self-center">/</span>
                                    <input type="text" name="card_exp_year" placeholder="AA"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                                <input type="text" name="card_cvv"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="000">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex items-start">
                        <input type="checkbox" name="terms_accepted" id="terms" required
                            class="mt-1 mr-3 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="terms" class="text-sm text-gray-700">
                            Concordo com os <a href="#" class="text-blue-600 hover:underline">Termos de Uso</a> 
                            e <a href="#" class="text-blue-600 hover:underline">Política de Privacidade</a>. 
                            Estou ciente de que esta é uma transação segura.
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Finalizar Compra - R$ {{ number_format($product['amount'] ?? 0, 2, ',', '.') }}
                </button>
            </form>         
        </div>

    <script>
        // Simple form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Processando...';

            // Add loading state
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        });

        // CPF mask
        document.querySelector('input[name="customer_cpf"]')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            }
        });

        // Card number mask
        document.querySelector('input[name="card_number"]')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value.substring(0, 19);
        });
    </script>

        <style>
            [x-cloak] { display: none !important; }
        </style> 

    </body> 

</html>