<script>
    import {DocsImage} from '@hyvor/design/marketing';
    import { Callout } from '@hyvor/design/components';
</script>

# Agent (IA)

Hyvor Blogs propose un agent IA qui peut accéder à vos articles et fournir des suggestions pour les améliorer. Vous pouvez activer ou désactiver l'agent IA à tout moment dans **Console &rarr; Paramètres &rarr; IA**.

- [Fonctionnalités](#features)
- [Fournisseurs et modèles](#providers-models)
- [Comment accéder à l'agent IA](#access)
- [Examiner les modifications de documents](#review-changes)
- [Tarification](#pricing)

<h2 id="features">Fonctionnalités</h2>

Ce que l'agent IA peut faire :

- Lire vos articles
- Lire les paramètres du blog : tags, auteurs, langues, etc.
- Résumer les articles
- Répondre aux questions relatives à vos articles
- Suggérer des améliorations pour vos articles
- Générer des idées de contenu pour vos articles

Ce que l'agent IA ne peut pas faire :

- Modifier des articles sans votre permission, une approbation est toujours requise (voir [Examiner les modifications de documents](#review-changes))
- Modifier les paramètres du blog
- Supprimer des articles ou tout autre contenu

<h2 id="providers-models">Fournisseurs et modèles</h2>

Hyvor Blogs prend en charge plusieurs fournisseurs et modèles d'IA :

- **OpenAI** (États-Unis)
  - `gpt-5.6-luna` (par défaut)
  - `gpt-5.6-terra`
  - `gpt-5.6-sol`
- **Anthropic** (États-Unis)
  - `claude-sonnet-5`
  - `claude-opus-5`
- **Mistral** (France)
  - `mistral-small-latest`
  - `mistral-medium-latest`
  - `mistral-large-latest`
  - `zai-glm-5-2`

Vous pouvez changer le modèle dans la section **Console &rarr; Paramètres &rarr; IA**. Pour la plupart des tâches orientées contenu (suggérer des améliorations, résumer, générer des idées de contenu), même les modèles les plus petits fonctionnent bien. Vous pouvez choisir un modèle plus performant si vous avez besoin de capacités plus avancées. Consultez la section [tarification](#pricing) ci-dessous pour plus de détails.

<h2 id="access">
    Comment accéder à l'agent IA
</h2>

L'agent IA est disponible dans **Console &rarr; Agent** et **dans l'éditeur d'articles**. Les deux agents offrent la même fonctionnalité, mais l'agent dans l'éditeur d'articles est configuré pour vous aider principalement avec l'article que vous êtes en train de modifier.

<DocsImage src="/images/docs/agent/agent-in-editor.png" width={700} />

<h2 id="review-changes">Examiner les modifications de documents</h2>

Un agent IA peut suggérer des modifications à vos articles. Cliquez sur « Examiner les modifications » pour voir les modifications suggérées et approuver ou rejeter chacune d'entre elles.

Voici un exemple de modifications suggérées par l'agent IA pour corriger des fautes de grammaire :

<DocsImage src="/images/docs/agent/review-changes-button.png" width={400} />

Lorsque vous cliquez sur « Examiner les modifications », un panneau apparaît vous permettant d'examiner chaque modification suggérée et de l'approuver ou de la rejeter une par une.

<DocsImage src="/images/docs/agent/review-modal.png" width={900} />

Une fois terminé, cliquez sur « Appliquer les modifications » pour enregistrer l'article.

<Callout type="info">
    Si vous avez appliqué des modifications à des articles publiés ou programmés, vous devrez mettre à jour l'article dans l'éditeur d'articles pour refléter ces modifications.
</Callout>

<h2 id="pricing">Tarification</h2>

Chaque [plan tarifaire](/pricing) est accompagné d'un quota d'utilisation IA pour votre organisation, qui sera utilisé par tous les blogs de cette organisation. Dans **Console & Facturation**, vous pouvez consulter votre utilisation actuelle et le quota restant. Il se réinitialise le premier jour de chaque mois.

Les modèles plus grands et plus coûteux consomment davantage votre quota d'utilisation IA. **Console &rarr; Paramètres &rarr; IA** affiche une comparaison relative de la consommation des modèles. Nous recommandons de commencer avec des modèles plus petits et de n'utiliser des modèles plus grands que lorsque cela est nécessaire.

<h2 id="faq">FAQ</h2>

**Puis-je demander à l'agent IA de contourner l'approbation et d'apporter des modifications directement ?**<br />
Non, l'agent IA ne peut pas contourner le processus d'approbation. Toutes les modifications doivent être examinées et approuvées manuellement par vous.

**Puis-je utiliser un modèle personnalisé (auto-hébergé) ?**<br />
Non, nous ne prenons actuellement pas en charge les modèles personnalisés.
