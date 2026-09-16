# WalangBrownout Backend

Laravel 12 REST API and SQLite database for the WalangBrownout Inventory and Sales Monitoring System.

## Database records

- Users with Manager, Supervisor, and Staff roles
- Products and current stock quantities
- Opening balance, stock-in, sale, transfer, return, and adjustment transactions
- Physical-count discrepancies and supervisor approval
- Activity logs with user, department, location, and timestamp
- Sales summaries generated from saved transactions

## Setup in PowerShell

```powershell
git clone https://github.com/howellsy07/backend-walang-brownout.git
cd backend-walang-brownout
composer install
copy .env.example .env
php artisan key:generate
New-Item database/database.sqlite -ItemType File -Force
php artisan migrate:fresh --seed
php artisan serve
```

The API runs at `http://127.0.0.1:8000`.

To run the feature tests:

```powershell
php artisan test
```
