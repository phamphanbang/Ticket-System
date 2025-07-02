# Ticket System

A **simple and powerful ticket management system**, similar to Zendesk or Zoho Desk, built as a Laravel API to receive client requests via email and manage them efficiently with real-time notifications.

## 🚀 Features
- Receive client requests via email and convert them to tickets automatically
- Manage ticket status with detailed audit logs
- Add comments and attachments to tickets
- Support inline images in comments and emails
- Notify users via email and Slack when ticket updates occur
- API endpoints for dashboard statistics:
    - Total tickets
    - Completed tickets
    - Tickets count per status (today, last 7 days, last month)

## 🛠️ Tech Stack
- **Backend**: Laravel (API only)
- **Database**: MySQL with UUIDs for most IDs
- **Notification**: Email (SMTP or Gmail OAuth) and Slack (via bot integration)
- **Containerization**: Docker & Docker Compose
## 📁 Database Schema Overview
- `users`: System users and agents
- `clients`: Clients who submit requests
- `tickets`: Tickets for each client request
- `ticket_status`: Status values for tickets
- `ticket_audit_logs`: Logs ticket status changes and timestamps
- `ticket_comments`: Comments added to tickets
- `attachments`: Files and images attached to tickets and comments
- `received_emails`: Stores received email metadata and threading info
## ⚙️ Installation with Docker
### Prerequisites
- Docker
- Docker Compose

### Steps
1. Clone the repository:
```bash
git clone https://github.com/yourusername/ticket-system.git
cd ticket-system
```
2. Copy `.env.example` to `.env` and configure your environment variables if needed.
3. Build and start the containers:
```bash
docker compose up -d --build
```
4. Open app container bash:
```bash
docker exec -it ticket_app bash
```
5. Install Composer dependencies
```bash
composer install
```
6. Run migrations with seed:
```bash
php artisan migrate --seed
```
7. Access your application: 
	- API: `http://localhost:8080`

## 🐳 Docker Compose Services
- **app**: Laravel application running on PHP-FPM
- **nginx**: Serves the Laravel app
- **db**: MySQL database
- **cron**: Run cronjob for fetching email
- **queue**: Run queue work with Supervisor
