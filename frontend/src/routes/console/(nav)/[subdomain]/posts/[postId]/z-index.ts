/**
 * position: sticky causes a z-index problem between
 * the left header and the sidebar
 * We need to bring the each one to the front when
 * a modal is opened through them.
 */
export let Z_INDEX = 1000;

export function increaseZIndex() {
  Z_INDEX++;
}
