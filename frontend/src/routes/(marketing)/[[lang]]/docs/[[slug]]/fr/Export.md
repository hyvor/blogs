<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
    import { Callout } from '@hyvor/design/components';
</script>

# Exporter les données

Vous pouvez exporter les données de votre blog à tout moment grâce à la fonctionnalité d'export dans la Console. Les données exportées incluront tous vos articles, tags, utilisateurs et autres informations pertinentes. L'export est fourni au format JSON.

<Callout type="info" title="Exportation des médias">
    Le fichier d'export n'inclut pas les fichiers médias (images téléchargées, vidéos, etc.) eux-mêmes, uniquement les métadonnées à leur sujet. Pour obtenir un export de tous les fichiers médias, veuillez contacter le support.
</Callout>

<h2 id="how">Comment exporter</h2>

- Allez dans **Outils &rarr; Exporter** dans la Console
- Cliquez sur le bouton **Exporter maintenant**

Cela prendra quelques minutes selon la taille de votre blog. Vous pouvez suivre la progression dans l'onglet **Historique**.

<DocsImage src="/images/docs/export/export.gif" alt="Exportation des données" />

<h2 id="format">Format d'export</h2>

Le fichier JSON exporté a la structure suivante :

```js
{
    "blog": { a blog object },
    "languages": [
        language object,
        ...
    ],
    "posts": [
        {
            "post": post object,
            "variants": [
                post variant object,
                ...
            ]
        },
        ...
    ],
    "users": [
        user object,
        ...
    ],
    "tags": [
        tag object,
        ...
    ],
    "media": [
        media object,
        ...
    ],
    "navigation": [
        navigation object
        ...
    ],
    "routes": [
        route object,
        ...
    ],
    "redirects": [
        redirect object,
        ...
    ],
}
```

Tous les objets sont dans le même format que l'[API Console](/docs/api-console).

- [Objet Blog](/docs/api-console#blog-object)
- [Objet Language](/docs/api-console#language-object)
- [Objet Post](/docs/api-console#post-object)
- [Objet PostVariant](/docs/api-console#post-variant-object)
- [Objet User](/docs/api-console#user-object)
- [Objet Media](/docs/api-console#media-object)
- [Objet Tag](/docs/api-console#tag-object)
- [Objet Navigation](/docs/api-console#navigation-object)
- [Objet Route](/docs/api-console#route-object)
- [Objet Redirect](/docs/api-console#redirect-object)

Remarque : Chaque entrée dans `posts` associe un **Objet Post** à un tableau `variants` d'**Objets PostVariant**, un par langue. Ces variantes possèdent une propriété supplémentaire `content_html` contenant le contenu converti en HTML.
