# Nginx Reverse Proxy Setup

This directory contains the nginx configuration for the Urban Renewal project.

## What's included:

- **nginx.conf**: Main nginx configuration with reverse proxy setup
- **ssl/**: Directory for SSL certificates (for HTTPS setup)

## How it works:

### Reverse Proxy Configuration:
- **Frontend**: All requests (except `/api/`) are proxied to the frontend service (Nuxt.js)
- **Backend**: All `/api/` requests are proxied to the backend service (CodeIgniter 4)
- **Rate Limiting**: API endpoints are rate limited (10 req/s, login 5 req/min)
- **Security Headers**: Basic security headers are added to all responses
- **CORS**: Cross-Origin Resource Sharing is properly configured

### Port Configuration:
- **Port 80**: Main entry point for HTTP traffic
- **Port 443**: Ready for HTTPS (when SSL certificates are added)

## Usage:

### 1. Start with nginx:
```bash
docker-compose up nginx
```

### 2. Access your application:
- **Frontend**: http://localhost/
- **API**: http://localhost/api/
- **PHPMyAdmin**: http://localhost:9428/

### 3. For production deployment:
1. Update `.env` with your domain:
   ```
   BACKEND_API_URL=https://yourdomain.com/api
   BACKEND_URL=https://yourdomain.com
   ```

2. Add SSL certificates to the `ssl/` directory

3. Update nginx.conf to enable HTTPS (see ssl/README.md)

## Benefits of this setup:

✅ **Single entry point**: Access everything through port 80/443
✅ **Security**: Rate limiting, security headers, CORS handling
✅ **Performance**: Gzip compression, connection keep-alive
✅ **Production ready**: Easy to add SSL certificates
✅ **Scalable**: Can easily add load balancing later

## Direct service access (for debugging):
- Frontend: http://localhost:9128/
- Backend: http://localhost:9228/
- Database: http://localhost:9328/
- PHPMyAdmin: http://localhost:9428/