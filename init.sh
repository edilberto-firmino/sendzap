# Parar tudo
pkill -f "node.*server.js"
pkill -f "php.*artisan"

# Iniciar novamente
cd whatsapp-service && npm start &
cd .. && php artisan serve &