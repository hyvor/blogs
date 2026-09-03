<script lang="ts">
	import KeywordDisplay from './KeywordDisplay.svelte';
	import { toast } from '@hyvor/design/components';
	import KeywordAdder from './KeywordAdder.svelte';
	import { postVariantStore, updatePostVariantStore } from '../../../../postStore';
	import { updatePostVariant } from '../../../../postActions';
	import { getI18n } from '../../../../../../../lib/i18n';

	const i18n = getI18n();

	function updatePrimaryKeyword(keyword: string | null) {
		updatePostVariantStore(
			{
				seo_primary_keyword: keyword
			},
			true
		);
		updatePostVariant({
			seo_primary_keyword: keyword
		});
	}

	function handlePrimaryKeywordAdd(e: CustomEvent<string>) {
		addPrimaryKeyword(e.detail);
	}

	function addPrimaryKeyword(k: string) {
		k = k.trim().toLowerCase();
		if ($postVariantStore.seo_secondary_keywords.find((keyword) => keyword.toLowerCase() === k)) {
			toast.error(i18n.t('console.postEditor.seo.keywordAlreadyAdded'));
			return false;
		}

		updatePrimaryKeyword(k);

		return true;
	}

	function handlePrimaryKeywordRemove() {
		updatePrimaryKeyword(null);
	}

	function handleSecondaryKeywordAdd(e: CustomEvent<string>) {
		addSecondaryKeyword(null, e.detail);
	}

	function addSecondaryKeyword(keyword: string | null, newKeyword: string) {
		newKeyword = newKeyword.trim().toLowerCase();

		if ($postVariantStore.seo_primary_keyword === newKeyword) {
			toast.error(i18n.t('console.postEditor.seo.keywordAlreadyPrimary'));
			return false;
		}

		if ($postVariantStore.seo_secondary_keywords.find((k) => k.toLowerCase() === newKeyword)) {
			toast.error(i18n.t('console.postEditor.seo.keywordAlreadyAdded'));
			return false;
		}

		if (keyword !== null) {
			updateSecondaryKeywords(
				$postVariantStore.seo_secondary_keywords.map((k) => (k === keyword ? newKeyword : k))
			);
		} else {
			updateSecondaryKeywords([...$postVariantStore.seo_secondary_keywords, newKeyword]);
		}

		return true;
	}

	function updateSecondaryKeywords(keywords: string[]) {
		updatePostVariantStore({ seo_secondary_keywords: keywords }, true);
		updatePostVariant({ seo_secondary_keywords: keywords });
	}

	function handleSecondaryRemove(keyword: string) {
		updateSecondaryKeywords($postVariantStore.seo_secondary_keywords.filter((k) => k !== keyword));
	}
</script>

<div class="keywords">
	<div class="input">
		<div class="title">{i18n.t('console.postEditor.seo.primaryKeyword')}</div>

		{#if $postVariantStore.seo_primary_keyword === null}
			<KeywordAdder on:add={handlePrimaryKeywordAdd} />
		{:else}
			<KeywordDisplay
				keyword={$postVariantStore.seo_primary_keyword}
				onUpdate={addPrimaryKeyword}
				on:remove={handlePrimaryKeywordRemove}
			/>
		{/if}
	</div>

	<div class="input">
		<div class="title">{i18n.t('console.postEditor.seo.secondaryKeywords')}</div>

		{#each $postVariantStore.seo_secondary_keywords as keyword}
			<KeywordDisplay
				{keyword}
				onUpdate={(newKeyword) => addSecondaryKeyword(keyword, newKeyword)}
				on:remove={() => handleSecondaryRemove(keyword)}
			/>
		{/each}

		<div style="margin-top:10px;">
			<KeywordAdder on:add={handleSecondaryKeywordAdd} />
		</div>
	</div>
</div>

<style lang="scss">
	.keywords {
		flex: 1;
		margin-left: 15px;
		.title {
			margin-bottom: 5px;
			color: var(--text-light);
			font-size: 12px;
		}
		.input {
			margin-bottom: 15px;
		}
	}
</style>
