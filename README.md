# Limit Order Exchange

A Laravel + Vue.js (Inertia.js) limit order exchange mini engine for trading BTC and ETH.

## Features

- User authentication (login/logout)
- Wallet management (USD balance + crypto assets)
- Create buy/sell limit orders
- Order matching engine (full match only)
- 1.5% commission on matched trades
- Real-time updates via Pusher
- Order history with filtering

## Tech Stack

- **Backend**: Laravel 12, MySQL, Laravel Sanctum
- **Frontend**: Vue.js 3 (Composition API), Inertia.js, Tailwind CSS
- **Real-time**: Pusher

## Installation

You can run this application either locally or using Docker.

### Option 1: Local Installation

**Requirements:**
- PHP 8.4+
- Composer
- Node.js 20.19+ or 22.12+
- MySQL

```bash
# Clone repository
git clone https://github.com/Ayorinde-Codes/Limit-Order-Exchange.git
cd Limit-Order-Exchange

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_DATABASE=limit_order_exchange
DB_USERNAME=root
DB_PASSWORD=your_password

# Configure Pusher in .env (optional for real-time)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Run migrations and seed
php artisan migrate --seed

# Start development server
composer run dev

# Access at http://localhost:8000
```

### Option 2: Docker Installation

**Requirements:**
- Docker
- Docker Compose

```bash
# Clone repository
git clone https://github.com/Ayorinde-Codes/Limit-Order-Exchange.git
cd Limit-Order-Exchange

# Environment setup
cp .env.example .env

# Start containers (DB credentials are configured in docker-compose.yml)
docker compose up -d

# Access at http://localhost:8080
```

## Running Tests

```bash
./vendor/bin/pest
```

## Test Users

| Email | Password | Balance | Assets |
|-------|----------|---------|--------|
| john@example.com | p@ssw0rd | $100,000 | - |
| alan@example.com | p@ssw0rd | $50,000 | - |
| mike@example.com | p@ssw0rd | $30,000 | 2 BTC, 3 ETH |
| sarah@example.com | p@ssw0rd | $20,000 | 3 BTC, 3 ETH |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/profile | Get user profile with balance and assets |
| GET | /api/orders?symbol=BTC | Get orderbook |
| POST | /api/orders | Create new order |
| POST | /api/orders/{id}/cancel | Cancel order |
| GET | /api/orders/history | Get order history |
