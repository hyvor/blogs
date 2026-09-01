<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# Exporter les données

Vous êtes propriétaire de vos données, et nous nous assurons que vous puissiez y accéder à tout moment. Vous pouvez actuellement exporter les données du blog au format JSON. Nous travaillons actuellement à l'ajout du format d'export WordPress et de l'export des médias prochainement !

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

Remarque : Chaque entrée dans `posts` associe un **Objet Post** à un tableau `variants` d'**Objets PostVariant**, un par langue. Ces variantes possèdent une propriété supplémentaire `content_html` contenant le contenu converti en HTML, qui n'est pas présente lors de la récupération des variantes via l'API Console.
