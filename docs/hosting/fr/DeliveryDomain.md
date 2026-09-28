<script>
    import { Callout } from '@hyvor/design/components';
</script>

# Domaine de diffusion

Par défaut, un blog est hébergé via le sous-répertoire du domaine de l'application : `https://<app-domain>/blog/<subdomain>`

Vous pouvez configurer un **Domaine de diffusion** pour servir les blogs à partir de sous-domaines d'un domaine donné à la place. Par exemple, sur notre cloud, les blogs sont hébergés sur `*.hyvorblogs.io`.

## Pourquoi un domaine de diffusion ?

Dans la plupart des cas, nous recommandons d'utiliser la méthode par défaut d'hébergement des blogs via le domaine de l'application `/blog/*`. Un domaine de diffusion est recommandé dans un scénario particulier : votre instance Hyvor Blogs héberge des blogs pour différents utilisateurs, et vous souhaitez isoler leur contenu sur des sous-domaines distincts afin d'assurer une bonne séparation du contenu.

<Callout type="info">
    Le <strong>Domaine de diffusion</strong> est une fonctionnalité différente des <a href="/docs/custom-domain">Domaines personnalisés</a>. Le domaine de diffusion concerne la méthode d'hébergement par défaut d'un blog, alors que chaque blog peut ou non configurer son propre domaine personnalisé.
</Callout>

## Configurer un domaine de diffusion

Ajoutez la variable d'environnement `DELIVERY_URL` :

```yaml
DELIVERY_URL=https://delivery.domain
```

Ensuite, configurez un enregistrement DNS pour faire pointer tous les sous-domaines de votre domaine de diffusion vers votre serveur :

```txt
A   *.delivery.domain   1.2.3.4
```

Enfin, configurez votre serveur pour qu'il termine le TLS pour votre domaine de diffusion. Cela nécessite un certificat wildcard pour `*.delivery.domain`, ce qui peut nécessiter un challenge DNS-01 pour son émission.

Architecture complète :

```
*.delivery.domain
    ↓
Proxy inverse (HTTPS, Port 443)
    ↓
Conteneur Hyvor Blogs (HTTP, Port 80)
```
