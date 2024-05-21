Projet pour l'évaluation en cours de formation de mon Bachelor.

# Ecf SoigneMoi - Client web

Documentation dans les documents techniques.

# Certificat ssl

## Génération

```bash
docker compose -f compose-prod.yaml run --rm --entrypoint "\
certbot certonly --webroot -w /var/www/certbot \
-d cli.ecf.seb7.fr --email sebastienmonterisi@gmail.com --agree-tos --no-eff-email \
--force-renewal" certbot
```

## Renouvellement

0 12 * * * /usr/bin/docker-compose -f /path/to/your/docker-compose.yml run --rm --entrypoint "\
certbot renew --webroot -w /var/www/certbot --quiet --renew-hook \"nginx -s reload\"" certbot
