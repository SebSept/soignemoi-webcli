#!/usr/bin/env sh

# Script de renouvellement du certificat ssl
# A executer au moins tous les 90 jours
#
# @todo gerer en interne
# gestion artisanale des crons.
# on a beaucoup mieux a faire
# refs :
# - https://jolicode.com/blog/maitrisez-la-planification-des-taches-avec-symfony-scheduler
# - https://github.com/symfonycorp/croncape
# pour le moment on ajouter le code a executer ici et ajouter manuelement un cron qui lance ce script
#
# mise en place :
# - `crontab -e` : `0 0 */90 * * /app/cron/certificates.sh >/dev/null 2>&1`
# - `chmod a+x /app/cron/certificates.sh`

# renouvellement certificats
docker compose -f compose-prod.yaml run --rm --entrypoint "certbot certonly --webroot -w /var/www/certbot -d cli.ecf.seb7.fr --email sebastienmonterisi@gmail.com --agree-tos --no-eff-email --force-renewal" certbot
# recharger nginx
docker compose -f compose-prod.yaml exec -t nginx nginx -s reload