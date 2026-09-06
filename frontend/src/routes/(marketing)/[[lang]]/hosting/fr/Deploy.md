<script>
   import { Table, TableRow } from '@hyvor/design/components';
</script>

# Déployer

Déployons Hyvor Blogs sur votre serveur en utilisant Docker Compose. Vous pouvez facilement adapter ce guide à d'autres méthodes de déploiement telles que Kubernetes.

<h2 id="pre-req">Prérequis</h2>

**Serveur** : Un serveur Linux avec au moins 1 Go de RAM et 1 vCPU.

**Docker** : Installez Docker en suivant le [guide officiel](https://docs.docker.com/engine/install/).

**Fournisseur OpenID Connect (OIDC)** : Hyvor Blogs s'appuie sur OIDC pour l'authentification. Créez une application chez votre fournisseur OIDC et obtenez l'URL de l'émetteur, l'ID client et le secret client. Puis, autorisez les URL suivantes :

- **URL de rappel (Callback URL)** : `https://<your-app-domain>/api/oidc/callback`
- **URL de déconnexion (Logout URL)** : `https://<your-app-domain>`

**Domaine** : Nom de domaine pour votre instance Hyvor Blogs. On l'appelle le « domaine de l'application » (App Domain).

<h2 id="dns">
   Routage DNS
</h2>

Pointez votre domaine d'application vers l'adresse IP de votre serveur.

<Table columns="1fr 2fr 150px" style="bordered">
   <TableRow head>
      <div>Type</div>
      <div>Hôte</div>
      <div>Valeur</div>
   </TableRow>
   <TableRow>
      <div>A</div>
      <div>blogs.example.com</div>
      <div>123.123.123.123</div>
   </TableRow>
</Table>

<h2 id="install">Installer</h2>

Téléchargez la dernière archive de version depuis la [page des releases](https://github.com/hyvor/blogs/releases) :

```bash
curl -L https://github.com/hyvor/blogs/releases/latest/download/deploy.tar.gz | tar -xz
cd deploy
```

Cela vous donne deux fichiers :

```
deploy/
   compose.yaml
   .env
```

## Configurer

Modifiez le fichier `.env` et renseignez les valeurs requises :

```yaml
# Required
APP_SECRET=           # Run: openssl rand -base64 32
POSTGRES_PASSWORD=    # A strong password for the database
DOMAIN_APP=           # e.g. blogs.example.com
DELIVERY_URL=         # e.g. https://blogs.example.com
MERCURE_JWT_SECRET=   # Run: openssl rand -base64 32

# OIDC (on-prem authentication)
OIDC_ISSUER_URL=      # e.g. https://accounts.google.com
OIDC_CLIENT_ID=
OIDC_CLIENT_SECRET=

# S3-compatible storage
S3_ACCESS_KEY_ID=
S3_SECRET_ACCESS_KEY=
S3_ENDPOINT=          # e.g. https://s3.amazonaws.com
S3_BUCKET=
S3_USE_PATH_STYLE_ENDPOINT=false
```

Le `DATABASE_URL` est préconfiguré pour se connecter au service Postgres défini dans `compose.yaml` en utilisant `POSTGRES_PASSWORD`, vous n'avez donc pas besoin de le modifier.

<h3 id="tls">Configuration TLS</h3>

`TLS_MODE` contrôle la manière dont HTTPS est géré pour le domaine de l'application (`DOMAIN_APP`). Cela n'affecte pas les domaines personnalisés attachés à des blogs individuels, qui obtiennent toujours des certificats TLS automatiquement. Définissez-le sur l'une des valeurs suivantes :

<Table columns="120px 1fr" style="bordered">
   <TableRow head>
      <div>Mode</div>
      <div>Comportement</div>
   </TableRow>
   <TableRow>
      <div><code>auto</code></div>
      <div>
         Par défaut. Caddy obtient et renouvelle automatiquement un certificat auprès de Let's Encrypt. Nécessite que
         <code>DOMAIN_APP</code> soit résolvable publiquement, avec les ports 80 et 443 accessibles depuis internet.
      </div>
   </TableRow>
   <TableRow>
      <div><code>external</code></div>
      <div>
         Utilisez ce mode si vous exécutez un reverse proxy (Nginx, Traefik, un équilibreur de charge, etc.) devant Hyvor
         Blogs qui termine le TLS. Le conteneur est accessible uniquement en HTTP ; seul le port 80 doit être
         publié. Le conteneur ne redirige pas lui-même HTTP vers HTTPS dans ce mode &mdash; gérez
         cela dans votre reverse proxy si nécessaire. Assurez-vous que votre proxy transmet les
         en-têtes <code>X-Forwarded-Proto: https</code> et <code>X-Forwarded-For</code>, et que son
         IP est incluse dans <code>TRUSTED_PROXIES</code>.
      </div>
   </TableRow>
   <TableRow>
      <div><code>manual</code></div>
      <div>
         Fournissez votre propre certificat et clé en les montant dans le conteneur à
         <code>/certs/cert.pem</code> et <code>/certs/key.pem</code>.
      </div>
   </TableRow>
   <TableRow>
      <div><code>disabled</code></div>
      <div>
         TLS est entièrement désactivé et aucune redirection HTTPS ne se produit. Tous les liens sont générés en
         <code>http://</code>. Utilisez ceci uniquement sur un réseau interne de confiance.
      </div>
   </TableRow>
</Table>

## Démarrer

```bash
docker compose up -d
```

Hyvor Blogs démarrera et exécutera automatiquement les migrations de base de données au premier lancement.

Pour vérifier les journaux (logs) :

```bash
docker compose logs -f
```

## Mise à niveau

Pour effectuer une mise à niveau vers la dernière version, récupérez la nouvelle image et redémarrez le conteneur :

```bash
docker compose pull
docker compose up -d
```

Les migrations sont appliquées automatiquement au démarrage.
