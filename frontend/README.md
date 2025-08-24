# Frontend Docker Setup

This directory contains the Docker configuration for the Nuxt 3 frontend application.

## Quick Start

### Production Build
```bash
docker-compose up --build
```

### Development Mode
```bash
docker-compose --profile dev up --build frontend-dev
```

## Configuration

- **Production**: The app runs on port 3000 (configurable via `FRONTEND_PORT`)
- **Development**: The app runs on port 3001 (configurable via `FRONTEND_DEV_PORT`)

## Environment Variables

Copy `.env.example` to `.env` and adjust the values as needed:

```bash
cp .env.example .env
```

## Services

### frontend (Production)
- Multi-stage build for optimal production performance
- Runs the built Nuxt application
- Smaller image size and better security

### frontend-dev (Development)
- Hot reloading enabled
- Source code mounted as volume
- All development dependencies included

## Usage Examples

```bash
# Start production service
docker-compose up frontend

# Start development service
docker-compose --profile dev up frontend-dev

# Build and start in detached mode
docker-compose up -d --build

# Stop all services
docker-compose down

# View logs
docker-compose logs -f frontend
```

## Project Structure

```
frontend/
├── src/                 # Nuxt 3 application source code
├── Dockerfile          # Production build
├── Dockerfile.dev      # Development build
├── docker-compose.yml  # Docker Compose configuration
├── .env.example       # Environment variables template
└── README.md          # This file
```