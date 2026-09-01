<script lang="ts">
   import { Table, TableRow } from '@hyvor/design/components';
</script>

# Hébergement

[Hyvor Blogs](https://blogs.hyvor.com) est une plateforme de blog rapide et simple qui peut être auto-hébergée sur vos propres serveurs. Cette page vous présentera le processus d'auto-hébergement. Pour commencer immédiatement, consultez la page [Déploiement](/hosting/deploy).

## L'auto-hébergement est une priorité

Hyvor Blogs est conçu pour être auto-hébergé par des développeurs et des organisations.

- **Dépendances minimales** : uniquement Docker, PostgreSQL et un fournisseur OIDC.
- **Open-source** : code source AGPLv3 disponible sur [Github](https://github.com/hyvor/blogs).
- **Multi-tenant** : exécutez plusieurs blogs sur une seule instance.
- **Authentification unique** : authentification basée sur OIDC par défaut

## Auto-hébergement vs. Cloud

<Table columns="1fr 1fr" style="bordered">
   <TableRow head>
      <div>Auto-hébergement</div>
      <div>Cloud</div>
   </TableRow>
   <TableRow>
      <div>Vos propres serveurs. Les données ne quittent jamais votre infrastructure</div>
      <div>Hébergé sur les serveurs de HYVOR</div>
   </TableRow>
   <TableRow>
      <div>Nécessite Docker, PostgreSQL et un fournisseur OIDC</div>
      <div>Aucune configuration, commencez à bloguer immédiatement</div>
   </TableRow>
   <TableRow>
      <div>Vous gérez les mises à jour et la maintenance</div>
      <div>Mises à jour automatiques gérées par HYVOR</div>
   </TableRow>
   <TableRow>
      <div>Payez vos propres coûts d'infrastructure</div>
      <div>Tarification par abonnement</div>
   </TableRow>
   <TableRow>
      <div>
         Support communautaire (plans de support payants disponibles)
      </div>
      <div>Support dédié de HYVOR</div>
   </TableRow>
</Table>

## Hyvor Blogs par rapport aux autres plateformes de blog

Quelques comparaisons avec d'autres plateformes de blog :

- Hyvor Blogs se concentre entièrement sur le blogging et est plus léger qu'un CMS généraliste comme **WordPress**, **Drupal** ou **Joomla**. Hyvor Blogs n'est pas extensible avec des plugins, mais regroupe dès le départ les outils dont vous avez besoin pour un blog
  - [Hyvor Blogs vs WordPress](https://hyvor.com/compare/blogs/wordpress)
- Hyvor Blogs est comparable à **Ghost**. Hyvor Blogs ne dispose pas de fonctionnalités intégrées d'adhésion et de newsletter, mais permet de s'intégrer à des services tiers et se concentre sur la création de blogs servis de manière statique, rapides et adaptés au SEO. Hyvor Blogs offre une meilleure collaboration d'équipe, des flux d'approbation, un support multilingue, etc.
  - [Hyvor Blogs vs Ghost](https://hyvor.com/compare/blogs/ghost)
- Hyvor Blogs vous donne plus de contrôle sur vos données et votre infrastructure que **Medium** ou **Substack**, qui sont des plateformes fermées. Avec Hyvor Blogs, vous possédez vos données et pouvez les héberger sur vos propres serveurs. Hyvor Blogs peut être plus adapté à un blog principal, tandis que Medium et Substack peuvent être utilisés comme plateformes secondaires pour toucher un public plus large.
  - [Hyvor Blogs vs Medium](https://hyvor.com/compare/blogs/medium)
  - [Hyvor Blogs vs Substack](https://hyvor.com/compare/blogs/substack)
- Hyvor Blogs n'est pas un moteur de blog basé sur du texte comme **Hugo** ou **Jekyll**. Il se concentre sur la rédaction de contenu basée sur un éditeur riche avec une interface simple et intuitive.
- Hyvor Blogs offre des fonctionnalités de CMS headless (par exemple, l'API de données), mais a un périmètre plus restreint sur le blogging que **Strapi**, **Payload CMS**, et d'autres plateformes de CMS headless généralistes.

## Licence et tarification

Nous proposons trois options de licence pour l'auto-hébergement de Hyvor Blogs :

- **Open-Source** :
  - Gratuit, licence AGPLv3
  - Support communautaire
  - Fonctionnalités de base, incluant l'éditeur riche, les médias, les tags, les auteurs, et plus encore.
  - Collaboration d'équipe
  - Support multilingue
  - Support des thèmes personnalisés
  - Fonctionnalités SEO intégrées
  - Support de domaine personnalisé et TLS
  - API Console, API de données, API de livraison, et Webhooks
  - Agent IA et traductions
  - Détection des liens brisés
- **Enterprise Unicorn** (bientôt) :
  - 5 €/utilisateur/mois (facturé annuellement), minimum 10 utilisateurs
  - Tout ce qui est inclus dans Open-Source, plus :
  - Support par e-mail de HYVOR
  - Journaux d'audit
  - Rôles et permissions personnalisés
  - Flux d'approbation personnalisés
- **Enterprise Apex** (bientôt) :
  - Contactez-nous pour la tarification
  - Tout ce qui est inclus dans Enterprise Unicorn, plus :
  - Support prioritaire avec SLA
  - Facturation

## Support

- [Dépôt Github](https://github.com/hyvor/blogs) pour les problèmes, les demandes de fonctionnalités
- [Support communautaire](https://hyvor.community)

Consultez la page [Déploiement](/hosting/deploy) pour des instructions étape par étape.
