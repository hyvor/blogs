import { Plugin } from 'prosemirror-state';

// gives Prosemirror more breathing room when scrolling to the top or bottom
// by adding a margin based on the height of the top bar and footer, plus an extra margin

const EXTRA_MARGIN = 16;

function getBarHeights() {
	const topBar = document.querySelector<HTMLElement>('.post-top-bar');
	const footer = document.querySelector<HTMLElement>('.editor-footer');

	return {
		top: topBar?.offsetHeight ?? 0,
		bottom: footer?.offsetHeight ?? 0
	};
}

export default function scrollMarginPlugin() {
	return new Plugin({
		props: {
			get scrollThreshold() {
				const { top, bottom } = getBarHeights();
				return { top, bottom, left: 0, right: 0 };
			},
			get scrollMargin() {
				const { top, bottom } = getBarHeights();
				return {
					top: top + EXTRA_MARGIN,
					bottom: bottom + EXTRA_MARGIN,
					left: 0,
					right: 0
				};
			}
		}
	});
}
