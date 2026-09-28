# Variables d'environnement

Voici les variables d'environnement que vous pouvez utiliser pour personnaliser Hyvor Blogs :

```yaml
# Environnement : prod, dev, ou test
# vous voudrez probablement utiliser prod pour un déploiement
APP_ENV=prod

# La clé secrète (32 octets) utilisée pour chiffrer les données sensibles.
# Générez-en une avec `openssl rand -base64 32`
APP_SECRET=

# L'URL de la base de données PostgreSQL.
# Utilisez le format : "postgresql://user:pass@host:5432/database_name?serverVersion=16&charset=utf8"
DATABASE_URL=

# Configuration OpenID Connect (OIDC)
# Créez une application chez votre fournisseur OIDC et définissez ces valeurs
# URL de rappel (Callback) : https://<DOMAIN_APP>/api/oidc/callback
# URL de déconnexion : https://<DOMAIN_APP>
OIDC_ISSUER_URL=
OIDC_CLIENT_ID=
OIDC_CLIENT_SECRET=

# Domaine de l'application
# Où Hyvor Blogs s'exécute (console, sudo, API)
# Exemple : blogs.yourcompany.com
DOMAIN_APP=

# Domaine / URL de livraison (optionnel)
# Si non défini, les blogs seront livrés à l'adresse https://<DOMAIN_APP>/blog/{subdomain}.
# Si défini, un sous-domaine du domaine de livraison sera utilisé pour héberger les blogs
# Si l'URL de livraison est https://blogs.yourcompany.com, les blogs seront hébergés à l'adresse https://<blog-subdomain>.blogs.yourcompany.com
# La terminaison TLS pour *.deliverydomain doit être gérée par un reverse proxy
# voir https://blogs.hyvor.com/hosting/delivery-domain
DELIVERY_URL=

# Système de fichiers pour le stockage des médias
# l'une des valeurs suivantes : file, s3
FILESYSTEM=file

# Configuration S3 si FILESYSTEM=s3
S3_ENDPOINT=
S3_ACCESS_KEY_ID=
S3_SECRET_ACCESS_KEY=
S3_BUCKET=
S3_REGION=
S3_USE_PATH_STYLE_ENDPOINT=

# Configuration SMTP pour l'envoi d'e-mails (notifications d'analyse de liens, etc.)
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=

# TLS_MODE contrôle la façon dont HTTPS est géré pour DOMAIN_APP.
# L'une des valeurs suivantes : auto, external, manual, ou disabled
# Voir https://blogs.hyvor.com/hosting/deploy#tls
TLS_MODE=

# Adresses IP ou plages CIDR des proxys de confiance.
# Ceci est utilisé pour déterminer l'adresse IP réelle du client et le statut HTTPS.
# Par défaut, toutes les plages d'adresses IP privées sont approuvées.
TRUSTED_PROXIES=

# Niveau de journalisation
# L'une des valeurs suivantes : debug, info (par défaut), notice, warning, error, critical, alert, emergency
LOG_LEVEL=

# Indique si les migrations doivent s'exécuter automatiquement au démarrage
# Définissez cette valeur sur false si vous souhaitez exécuter les migrations manuellement (par exemple, dans un pipeline CI) plutôt qu'à chaque démarrage
# par défaut : true
RUN_MIGRATIONS_ON_STARTUP=

# Configuration du hub Mercure
# Utilisé pour la communication en temps réel (par exemple, l'édition collaborative)
# si MERCURE_INTERNAL est true (par défaut : true), le hub Mercure intégré sera utilisé.
# et vous pouvez ignorer les autres paramètres liés à Mercure.
# définissez MERCURE_INTERNAL=false pour utiliser un hub Mercure externe.
MERCURE_INTERNAL=
MERCURE_JWT_SECRET=           # Exécutez : openssl rand -base64 32
MERCURE_URL=                  # où symfony appelle pour publier les mises à jour (URL privée)
MERCURE_PUBLIC_URL=           # où les clients JS se connectent

# Intégrations
# ===================

# Unsplash pour la recherche d'images
UNSPLASH_ACCESS_KEY=
UNSPLASH_SECRET_KEY=

# Clés des plateformes d'IA pour l'agent IA et les fonctionnalités de traduction automatique
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
MISTRAL_API_KEY=

# Sentry
# Utilisé pour le suivi des erreurs
SENTRY_DSN=
```
