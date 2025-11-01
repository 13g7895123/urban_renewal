# Urban Renewal Management System - AI Coding Instructions

## Architecture Overview

This is a **Taiwanese urban renewal management system** with Docker-based fullstack architecture:

- **Backend**: CodeIgniter 4 PHP API (`backend/`) serving RESTful endpoints
- **Frontend**: Nuxt 3 Vue.js SPA (`frontend/`) with Nuxt UI components
- **Database**: MariaDB with comprehensive migration system
- **Infrastructure**: Docker Compose with separate dev/prod configurations

## Development Workflow

### Environment Setup
```bash
# Development (backend only, frontend runs locally)
./start-dev.sh    # Uses docker-compose.dev.yml with volume mounts

# Production (full stack)
./start-prod.sh   # Uses docker-compose.prod.yml
```

**Key Pattern**: Development uses **volume mounts** (`./backend:/var/www/html`) for instant code changes without rebuilds. Only rebuild when modifying `composer.json` or Dockerfiles.

### Testing Strategy
- **TDD Approach**: Follow Red-Green-Refactor cycle documented in `backend/TDD_IMPLEMENTATION_REPORT.md`
- **Test Command**: `docker exec urban_renewal_backend_dev php spark test`
- **Current Status**: 49 comprehensive test cases in RED phase (intentionally failing)

## CodeIgniter 4 API Patterns

### Route Structure (`backend/app/Config/Routes.php`)
```php
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->group('auth', function ($routes) {
        $routes->post('login', 'AuthController::login');
        $routes->get('me', 'AuthController::me');
    });
    $routes->group('urban-renewals', function ($routes) {
        $routes->get('/', 'UrbanRenewalController::index');
        $routes->get('(:num)', 'UrbanRenewalController::show/$1');
    });
});
```

### Model Conventions
- Use soft deletes: `protected $useSoftDeletes = true`
- Validation rules in Chinese: `'name' => ['required' => '更新會名稱為必填項目']`
- Standard fields: `allowedFields`, `useTimestamps`, `validationRules`

### Controller Patterns
- All API controllers extend base with CORS handling
- Consistent response format with proper HTTP status codes
- JWT authentication for protected endpoints

## Nuxt 3 Frontend Patterns

### Page Structure
```
pages/
├── tables/urban-renewal/
│   ├── index.vue              # List view
│   ├── [id]/basic-info.vue    # Edit view
│   └── [id]/property-owners/  # Nested resources
```

### Composable Usage
- `useAuth()` - JWT authentication with CodeIgniter backend
- `useUrbanRenewal()` - CRUD operations for urban renewals
- `useApi()` - Base HTTP client with error handling
- All composables in `frontend/composables/` follow consistent patterns

### UI Components
- **Nuxt UI Library**: Use `UButton`, `UCard`, `UInput` instead of custom components
- **Icons**: Heroicons via `<Icon name="heroicons:plus" />`
- **Colors**: Green theme (`bg-green-500`, `text-green-600`) for primary actions
- **Layout**: Main layout with sidebar navigation

## Database Schema

### Core Entities
- `urban_renewals` - Main urban renewal associations
- `property_owners` - Individual stakeholders with land holdings
- `land_plots` - Land parcel data with Taiwan location hierarchy
- `meetings` - Meeting management with attendance tracking
- `voting_topics` - Voting system with anonymous options

### Taiwan-Specific Features
- Complete administrative hierarchy: `counties` → `districts` → `sections` → `land_plots`
- Traditional Chinese field names and validation messages
- ROC identity number validation patterns

## Key Integration Points

### Frontend-Backend Communication
- Base API URL: `NUXT_PUBLIC_API_BASE_URL=${BACKEND_API_URL}`
- JWT token handling in `useAuth()` composable
- Consistent error response format across all endpoints

### Docker Service Dependencies
```yaml
# Development
backend → mariadb (health check required)

# Production  
frontend → backend → mariadb
```

### Taiwan Location Data
- Import script: `backend/process_taiwan_sections.php`
- CSV source: `taiwan_sections_official.csv`
- Processed JSON: `processed_taiwan_locations.json`

## Project Conventions

### Language & Localization
- **UI Language**: Traditional Chinese (zh-TW)
- **Code Comments**: English for technical, Chinese for business logic
- **Database**: Chinese field names where appropriate
- **Error Messages**: Localized Chinese validation messages

### Git Workflow
- **Branch Naming**: `{number}-{feature}` (e.g., `002-admin-user`)
- **No Collaboration Text**: Keep commit messages clean and technical
- **Reply Language**: Use zh-tw for user communication

### File Naming
- **Backend**: PascalCase for classes, snake_case for files
- **Frontend**: kebab-case for components and pages
- **Respect Asset Extensions**: Don't change `assets/images/bg.jpg` to `.png` without permission

## Critical Dependencies

- **Backend**: `firebase/php-jwt`, `phpoffice/phpspreadsheet`
- **Frontend**: `@nuxt/ui`, `@pinia/nuxt`
- **Development**: Volume mounts preserve `vendor/` directory
- **Production**: Multi-stage Docker builds for optimization