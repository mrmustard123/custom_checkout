<!DOCTYPE html>
<html lang="pt-BR"> 
    <head> 
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>Erro no Pagamento</title> 
        <script src="https://cdn.tailwindcss.com"></script> 
    </head> 
    <body class="bg-gray-50 min-h-screen"> 
        <div class="container mx-auto px-4 py-8 max-w-md"> 
            <!-- Error Icon --> 
            <div class="text-center mb-8"> 
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"> 
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/> 
                    </svg> 
                </div> 
                <h1 class="text-3xl font-bold text-gray-800">Pagamento Não Concluído</h1> 
                <p class="text-gray-600 mt-2">Ocorreu um erro ao processar seu pagamento</p> 
            </div>
            
            <!-- Error Message -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6 text-center">
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <p class="text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <p class="text-red-700 font-medium">Não foi possível processar seu pagamento.</p>
                    </div>
                @endif

                <p class="text-gray-600 mb-4">
                    Por favor, verifique os dados informados e tente novamente.
                </p>
            </div>

            <!-- Common Solutions -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-2">Soluções Comuns:</h3>
                <ul class="list-disc list-inside space-y-1 text-sm text-yellow-700">
                    <li>Verifique os dados do cartão</li>
                    <li>Confirme se há saldo suficiente</li>
                    <li>Tente um cartão diferente</li>
                    <li>Use a opção PIX para pagamento instantâneo</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <a href="{{ route('checkout.show') }}" 
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 text-center block">
                    Tentar Novamente
                </a>

                <a href="{{ url('/') }}" 
                   class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 text-center block">
                    Voltar ao Início
                </a>
            </div>

            <!-- Support -->
            <div class="text-center mt-8">
                <div class="bg-gray-100 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Precisa de Ajuda?</h4>
                    <p class="text-sm text-gray-600 mb-2">Nossa equipe está aqui para ajudar</p>
                    <div class="space-y-1 text-sm">
                        <p class="text-gray-700">
                            <span class="font-medium">E-mail:</span> 
                            <a href="mailto:support@exemplo.com" class="text-blue-600 hover:underline">support@exemplo.com</a>
                        </p>
                        <p class="text-gray-700">
                            <span class="font-medium">WhatsApp:</span> 
                            <a href="https://wa.me/5511999999999" class="text-blue-600 hover:underline">(11) 99999-9999</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </body> 
</html>        