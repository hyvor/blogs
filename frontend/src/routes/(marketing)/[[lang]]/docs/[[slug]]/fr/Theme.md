<script>
	import { Callout } from '@hyvor/design/components';
</script>

# Thème

Hyvor Blogs est fourni avec quelques thèmes prêts à l'emploi (voir [Thèmes](/themes)). Lorsque vous créez un blog, le thème par défaut, **Hello**, est copié dans votre blog. Vous pouvez changer de thème ou modifier les fichiers du thème pour personnaliser votre blog.

- [Changer de thème](/docs/theme#changing)
- [Modifier les fichiers du thème](/docs/theme#editing)

<Callout type="info">
	<p>
		Si vous souhaitez développer un thème personnalisé, consultez la page <a href="/docs/themes-overview"
			>Développement de thème</a
		>.
	</p>
</Callout>

<h2 id="changing">Changer de thème</h2>

Pour changer le thème de votre blog,

- Rendez-vous dans la section **Thème** de la [console Hyvor Blogs](/console).
- Cliquez sur le bouton **Changer**.
- Sélectionnez un thème dans la liste.

<Callout type="warning">
	<p>
		<b>Important</b> : changer de thème écrasera tous les fichiers du thème de votre blog. Si vous
		avez apporté des modifications aux fichiers du thème, vous les perdrez.
	</p>
</Callout>

<h2 id="editing">Modifier les fichiers du thème</h2>

Vous pouvez également modifier les fichiers du thème de votre blog pour le personnaliser. Pour modifier les fichiers du thème, rendez-vous dans la section **Thème** de la [console Hyvor Blogs](/console). Vous verrez la liste de tous les fichiers du thème. Cliquez sur un fichier pour le modifier.

<!---image-->

`config.yaml` contient toutes les configurations du thème telles que les polices, les couleurs et d'autres paramètres. Tous les fichiers de modèles se trouvent dans le répertoire `templates`.
