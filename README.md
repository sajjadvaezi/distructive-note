# Distruct Note - Self-Destructing Notes

A secure, self-destructing note application with optional password protection and configurable view limits.

## Features

- 🔥 **Self-Destruct**: Notes automatically destroy after viewing (configurable)
- 🔒 **Optional Password Protection**: Secure notes with optional passwords
- ⚙️ **Configurable Views**: Set how many times a note can be viewed (1-100)
- 🕒 **Auto-Expiry**: Notes automatically expire after 7 days
- 🌐 **Modern UI**: Beautiful, responsive interface with glass morphism design
- 🔌 **API Support**: RESTful API for programmatic access
- 🐳 **Docker Ready**: Easy deployment with Docker Compose

## Quick Start

### Using Make (Recommended)

1. Clone the repository:
```bash
git clone <repository-url>
cd distruct-note
```

2. Start the application:
```bash
make start
```

3. Access the application at `http://localhost:8080`

### Using Docker Directly

1. Clone the repository:
```bash
git clone <repository-url>
cd distruct-note
```

2. Start the application:
```bash
docker compose up -d
```

3. Access the application at `http://localhost:8080`

### Manual Setup

1. **Requirements**:
   - PHP 8.2+
   - MySQL 8.0+
   - Apache with mod_rewrite

2. **Database Setup**:
   - Create a MySQL database
   - Import the schema from `sql/init.sql`

3. **Configuration**:
   - Copy `src/config.php` and update database credentials
   - Set up your web server to serve from the `public/` directory

4. **Permissions**:
   - Ensure the web server can write to the application directory

## Usage

### Web Interface

1. **Create a Note**:
   - Enter your message in the text area
   - Optionally set a password for protection
   - Configure the number of views (default: 1)
   - Click "Create Note"

2. **View a Note**:
   - Enter the note ID
   - Provide password if required
   - Click "View Note"

### API Usage

#### Create a Note
```bash
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Your secret message here",
    "password": "optional_password",
    "max_views": 3
  }'
```

Response:
```json
{
  "success": true,
  "note_id": "abc123...",
  "url": "http://localhost:8080/view.php?id=abc123..."
}
```

#### View a Note
```bash
curl "http://localhost:8080/api.php?id=abc123&password=optional_password"
```

Response:
```json
{
  "success": true,
  "note": {
    "content": "Your secret message here",
    "current_views": 1,
    "max_views": 3,
    "created_at": "2024-01-01 12:00:00",
    "expires_at": "2024-01-08 12:00:00"
  }
}
```

## Configuration

The application uses environment variables for configuration. Edit the `.env` file to customize:

- **Database Settings**: `DB_DSN`, `DB_USER`, `DB_PASS`
- **Application Settings**: `SITE_NAME`, `SITE_URL`
- **Security Settings**: `DEFAULT_MAX_VIEWS`, `MAX_VIEWS_LIMIT`, `NOTE_EXPIRY_DAYS`
- **Advanced Settings**: `ID_LENGTH`, `SALT_ROUNDS`

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_DSN` | `mysql:host=db;port=3306;dbname=destruct;charset=utf8mb4` | Database connection string |
| `DB_USER` | `app` | Database username |
| `DB_PASS` | `apppass` | Database password |
| `SITE_NAME` | `Distruct Note` | Application name |
| `SITE_URL` | `http://localhost:8080` | Application URL |
| `DEFAULT_MAX_VIEWS` | `1` | Default number of views before destruction |
| `MAX_VIEWS_LIMIT` | `100` | Maximum allowed views per note |
| `NOTE_EXPIRY_DAYS` | `7` | Days before notes auto-expire |
| `ID_LENGTH` | `32` | Length of note IDs |
| `SALT_ROUNDS` | `12` | Bcrypt salt rounds for passwords |

## Security Features

- **Password Hashing**: Uses bcrypt with configurable cost
- **SQL Injection Protection**: Prepared statements throughout
- **XSS Protection**: Output escaping on all user content
- **Secure IDs**: 32-character random hex IDs
- **Auto-Cleanup**: Expired notes are automatically destroyed

## Code Organization

- **Separation of Concerns**: CSS and JavaScript in dedicated files
- **Maintainable Styles**: Centralized CSS with utility classes
- **Modular JavaScript**: Copy functionality in separate module
- **Clean HTML**: No inline styles or scripts in PHP files
- **Better Readability**: Easier to understand and modify code

## File Structure

```
distruct-note/
├── .env                  # Environment variables (included for education)
├── .env.example         # Environment template
├── Makefile             # Cross-platform development commands
├── docker-compose.yaml  # Docker services configuration
├── Dockerfile          # PHP Apache container setup
├── sql/
│   └── init.sql       # Database schema
├── src/
│   ├── config.php     # Application configuration
│   ├── Database.php   # Database connection and queries
│   └── NoteService.php # Business logic
└── public/
    ├── css/
    │   └── styles.css # Application styles
    ├── js/
    │   └── copy.js    # Copy functionality
    ├── index.php      # Main application page
    ├── view.php       # Note viewing page
    ├── api.php        # REST API endpoint
    └── .htaccess      # Security headers
```

## Development

### Using Make Commands

```bash
# Start the application
make start

# Check status
make status

# View logs
make logs

# Run tests
make test

# Access shell
make shell

# Access database
make db-shell

# Stop application
make stop

# Clean up everything
make clean

# Reset everything
make reset
```

### Manual Docker Commands

```bash
# Start the application
docker compose up -d

# Run cleanup task
docker compose exec app php -r "
require_once '/var/www/src/NoteService.php';
\$service = new NoteService();
\$service->cleanupExpiredNotes();
"
```

### Database Access
```bash
# Connect to MySQL container
make db-shell

# Or manually:
docker compose exec db mysql -u app -p destruct

# View notes table
SELECT * FROM notes WHERE is_destroyed = FALSE;
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the MIT License.

## Support

For issues and questions, please open an issue on the GitHub repository.
