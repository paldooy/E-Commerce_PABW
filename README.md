# Ecommerce PABW

Laravel 11 application for the e-commerce system based on the provided requirements, ERD, and diagrams. The system covers buyer, seller, courier, and admin flows with a manual wallet balance model.

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+

## Setup

1. Copy `.env.example` to `.env` and set your database credentials.
2. Generate app key:

```bash
php artisan key:generate
```

3. Run migrations and seeders:

```bash
php artisan migrate --seed
```

4. Build frontend assets:

```bash
npm install
npm run build
```

5. Start the server:

```bash
php artisan serve
```

## Demo Accounts (Seeder)

- Admin: `admin` / `password`
- Courier: `kurir1` / `password`
- Seller: `seller1` / `password`
- Seller: `seller2` / `password`
- Buyer: `buyer1` / `password`

## Notes

- All transactions use the internal wallet balance (dummy, no real payment integration).
- Status flows follow the requirement rules for seller, courier, and buyer actions.
