# SSL Certificates

This directory is for SSL certificates when you want to enable HTTPS.

## To enable HTTPS:

1. Place your SSL certificate files here:
   - `certificate.crt` (your SSL certificate)
   - `private.key` (your private key)
   - `ca_bundle.crt` (certificate authority bundle, if required)

2. Update `nginx.conf` to include SSL configuration:

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/nginx/ssl/certificate.crt;
    ssl_certificate_key /etc/nginx/ssl/private.key;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;

    # Your existing location blocks here...
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

3. Update your `.env` file with your domain:
```
BACKEND_API_URL=https://your-domain.com/api
BACKEND_URL=https://your-domain.com
```