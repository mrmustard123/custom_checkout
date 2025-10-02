<!DOCTYPE html>
<html lang="pt-BR"> 
    <head> 
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>Pagamento PIX - Aguardando</title> 
        <script src="https://cdn.tailwindcss.com"></script> </head> 
    <body class="bg-gray-50 min-h-screen"> 
        <div class="container mx-auto px-4 py-8 max-w-md"> 
            <!-- Header --> 
            <div class="text-center mb-8"> 
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4"> 
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/> </svg> 
                </div> 
                <h1 class="text-2xl font-bold text-gray-800">Aguardando Pagamento PIX</h1> 
                <p class="text-gray-600 mt-2">Escaneie o QR Code abaixo para pagar</p> 
            </div>
            
                <!-- Order Info -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6 text-center">
                <p class="text-lg font-semibold text-gray-800">{{ $order->product_name }}</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">{{ $order->formatted_amount }}</p>
                <p class="text-sm text-gray-600 mt-1">Nº do Pedido: {{ $order->order_number }}</p>
            </div>

            <!-- QR Code -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="text-center">
                    @if($order->pix_qr_code)
                        <img src="data:image/png;base64,{{ $order->pix_qr_code }}" 
                             alt="QR Code PIX" 
                             class="mx-auto mb-4 border rounded-lg max-w-xs">
                    @else
                        <div class="bg-gray-200 rounded-lg w-64 h-64 mx-auto mb-4 flex items-center justify-center">
                            <span class="text-gray-500">QR Code não disponível</span>
                        </div>
                    @endif

                    <p class="text-sm text-gray-600 mb-4">
                        Escaneie o QR Code com seu app bancário ou copie o código PIX
                    </p>

                    @if($order->pix_qr_code_url)
                        <a href="{{ $order->pix_qr_code_url }}" target="_blank"
                           class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                            Abrir QR Code em Nova Janela
                        </a>
                    @endif
                </div>
            </div>      

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-2">Como pagar:</h3>
                <ol class="list-decimal list-inside space-y-1 text-sm text-blue-700">
                    <li>Abra seu app bancário</li>
                    <li>Toque em "Pagar com PIX"</li>
                    <li>Escaneie o QR Code acima</li>
                    <li>Confirme o pagamento</li>
                </ol>
            </div>

            <!-- Countdown -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <div class="flex items-center justify-center space-x-2 text-yellow-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium" id="countdown">30:00</span>
                </div>
                <p class="text-sm text-yellow-700 mt-1">Este QR Code expira em</p>
            </div>

            <!-- Loading Indicator -->
            <div class="text-center mt-6">
                <div class="inline-flex items-center space-x-2 text-gray-600">
                    <div class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                    <span>Aguardando confirmação do pagamento...</span>
                </div>
            </div>

            <!-- Error Message (hidden by default) -->
            <div id="errorMessage" class="hidden bg-red-50 border border-red-200 rounded-lg p-4 mt-4 text-center">
                <p class="text-red-700" id="errorText"></p>
                <a href="{{ route('checkout.show') }}" 
                   class="inline-block mt-2 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    Tentar Novamente
                </a>
            </div>
        </div>

<script>
    let expirationTime = new Date('{{ $order->pix_expiration }}').getTime();
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = expirationTime - now;
        
        if (distance < 0) {
            document.getElementById('countdown').textContent = 'Expirado';
            document.getElementById('errorText').textContent = 'O QR Code PIX expirou. Por favor, inicie uma nova compra.';
            document.getElementById('errorMessage').classList.remove('hidden');
            clearInterval(countdownInterval);
            return;
        }
        
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById('countdown').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    
    // Update countdown every second
    const countdownInterval = setInterval(updateCountdown, 1000);
    updateCountdown(); // Initial call
    
    // Check payment status every 5 seconds
    function checkPaymentStatus() {
        fetch('{{ route("checkout.pix.status", $order) }}')
            .then(response => response.json())
            .then(data => {
                if (data.paid) {
                    window.location.href = data.redirect_url;
                } else if (data.expired) {
                    document.getElementById('errorText').textContent = 'O QR Code PIX expirou. Por favor, inicie uma nova compra.';
                    document.getElementById('errorMessage').classList.remove('hidden');
                    clearInterval(paymentCheckInterval);
                }
            })
            .catch(error => {
                console.error('Error checking payment status:', error);
            });
    }
    
    const paymentCheckInterval = setInterval(checkPaymentStatus, 5000);
    checkPaymentStatus(); // Initial check
</script>

    </body>
</html> 