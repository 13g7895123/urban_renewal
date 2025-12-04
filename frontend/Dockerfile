# Multi-stage build for Nuxt 3 application
FROM node:20-alpine AS base

# Install dependencies only when needed
FROM base AS deps
RUN apk add --no-cache libc6-compat
WORKDIR /app

# Copy package files
COPY package*.json ./
RUN npm ci --only=production && npm cache clean --force

# Build stage
FROM base AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci

# Copy source code
COPY . .

# Set proper permissions for node_modules/.bin and make sure nuxt is executable
RUN chmod -R 755 node_modules/.bin 2>/dev/null || true
RUN if [ -f node_modules/.bin/nuxt ]; then chmod +x node_modules/.bin/nuxt; fi

# Set production environment variables for build
# These are placeholders - override with docker-compose environment
ENV NODE_ENV=production
ENV NUXT_PUBLIC_API_BASE_URL=http://backend:8000/api
ENV NUXT_PUBLIC_BACKEND_URL=http://backend:8000
ENV NUXT_PUBLIC_BACKEND_HOST=backend
ENV NUXT_PUBLIC_BACKEND_PORT=8000

# Build the application
RUN npm run build

# Production stage
FROM base AS runner
WORKDIR /app

# Don't run production as root
RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nuxtjs

# Copy built application
COPY --from=builder --chown=nuxtjs:nodejs /app/.output /app/.output

USER nuxtjs

# Expose port
EXPOSE 3000

ENV PORT 3000
ENV HOST 0.0.0.0
ENV NODE_ENV=production

# Set production API configuration - override with docker-compose environment
ENV NUXT_PUBLIC_API_BASE_URL=http://backend:8000/api
ENV NUXT_PUBLIC_BACKEND_URL=http://backend:8000
ENV NUXT_PUBLIC_BACKEND_HOST=backend
ENV NUXT_PUBLIC_BACKEND_PORT=8000

# Start the application
CMD ["node", "/app/.output/server/index.mjs"]