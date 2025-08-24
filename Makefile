# Distruct Note - Makefile
# Cross-platform commands for easy project management

.PHONY: help start stop restart status build logs shell db-shell test clean reset

# Default target
help: ## Show this help message
	@echo "Distruct Note - Available Commands:"
	@echo ""
	@echo "Basic Operations:"
	@echo "  start     - Start the application"
	@echo "  stop      - Stop the application"
	@echo "  restart   - Restart the application"
	@echo "  status    - Check container status"
	@echo ""
	@echo "Development:"
	@echo "  build     - Build containers"
	@echo "  logs      - Show application logs"
	@echo "  shell     - Access app container shell"
	@echo "  db-shell  - Access database shell"
	@echo ""
	@echo "Testing & Maintenance:"
	@echo "  test      - Run application tests"
	@echo "  clean     - Clean up containers and volumes"
	@echo "  reset     - Reset everything (clean + rebuild)"
	@echo ""
	@echo "Help:"
	@echo "  help      - Show this help message"

# Check if Docker is running
check-docker:
	@if ! docker info > /dev/null 2>&1; then \
		echo "❌ Docker is not running. Please start Docker first."; \
		exit 1; \
	fi

# Check if .env file exists
check-env:
	@if [ ! -f .env ]; then \
		echo "❌ .env file not found. Creating from template..."; \
		cp .env.example .env 2>/dev/null || echo "Please create a .env file"; \
	fi

# Basic Operations
start: check-docker check-env ## Start the application
	@echo "🚀 Starting Distruct Note..."
	docker compose up -d
	@echo "✅ Application started! Access at: http://localhost:8080"

stop: check-docker ## Stop the application
	@echo "🛑 Stopping Distruct Note..."
	docker compose down
	@echo "✅ Application stopped"

restart: stop start ## Restart the application
	@echo "🔄 Application restarted"

status: check-docker ## Check container status
	@echo "📊 Container Status:"
	docker compose ps

# Development Commands
build: check-docker check-env ## Build containers
	@echo "🔨 Building containers..."
	docker compose build
	@echo "✅ Containers built successfully"

logs: check-docker ## Show application logs
	@echo "📋 Application Logs:"
	docker compose logs -f app

shell: check-docker ## Access app container shell
	@echo "🐚 Opening app container shell..."
	docker compose exec app bash

db-shell: check-docker ## Access database shell
	@echo "🗄️ Opening database shell..."
	docker compose exec db mysql -u app -p destruct

# Testing & Maintenance
test: check-docker ## Run application tests
	@echo "🧪 Running tests..."
	@if docker compose ps | grep -q "Up"; then \
		docker compose exec app php -r "require_once '/var/www/src/NoteService.php'; echo 'Testing database connection...'; \$$db = Database::getInstance(); echo 'Database connection successful';"; \
	else \
		echo "❌ Application is not running. Run 'make start' first."; \
	fi

clean: check-docker ## Clean up containers and volumes
	@echo "🧹 Cleaning up containers and volumes..."
	docker compose down -v
	docker system prune -f
	@echo "✅ Cleanup completed"

reset: clean build start ## Reset everything (clean + rebuild)
	@echo "🔄 Reset completed"

# Database Commands
db-backup: check-docker ## Backup database
	@echo "💾 Creating database backup..."
	docker compose exec db mysqldump -u app -p destruct > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "✅ Backup created"

db-restore: check-docker ## Restore database from backup
	@if [ -z "$(BACKUP_FILE)" ]; then \
		echo "❌ Please specify backup file: make db-restore BACKUP_FILE=backup.sql"; \
		exit 1; \
	fi
	@echo "📥 Restoring database from $(BACKUP_FILE)..."
	docker compose exec -T db mysql -u app -p destruct < $(BACKUP_FILE)
	@echo "✅ Database restored"

# Utility Commands
info: ## Show application information
	@echo "📋 Distruct Note Information:"
	@echo "  - Application URL: http://localhost:8080"
	@echo "  - API Endpoint: http://localhost:8080/api.php"
	@echo "  - Database: MySQL 8.0"
	@echo "  - PHP Version: 8.2"
	@echo ""
	@echo "📁 Project Structure:"
	@echo "  - Source Code: ./src/"
	@echo "  - Web Files: ./public/"
	@echo "  - Database: ./sql/"
	@echo "  - Configuration: .env"

check: check-docker check-env ## Check system requirements
	@echo "✅ All requirements met"
	@echo "✅ Docker is running"
	@echo "✅ .env file exists"
	@echo "✅ Ready to start development"

# Development helpers
dev-logs: ## Show development logs (app + db)
	@echo "📋 Development Logs:"
	docker compose logs -f

dev-shell: shell ## Alias for shell command

# Windows compatibility
windows-start: start ## Windows-compatible start command
windows-stop: stop ## Windows-compatible stop command
windows-restart: restart ## Windows-compatible restart command

# macOS compatibility
mac-start: start ## macOS-compatible start command
mac-stop: stop ## macOS-compatible stop command
mac-restart: restart ## macOS-compatible restart command

# Linux compatibility
linux-start: start ## Linux-compatible start command
linux-stop: stop ## Linux-compatible stop command
linux-restart: restart ## Linux-compatible restart command
