

function els() {
    return {
        leftHeader: document.querySelector('.post-left-header') as HTMLElement | null,
        right: document.querySelector('.post-right') as HTMLElement | null,
    }
}

const Z_INDEX = 1000;

export function bringLeftHeaderToFront() {
    const { leftHeader, right } = els()
    if (leftHeader && right) {
        leftHeader.style.zIndex = Z_INDEX.toString();
        right.style.zIndex = (Z_INDEX - 1).toString();
    }
}

export function bringRightToFront() {
    const { leftHeader, right } = els()
    if (leftHeader && right) {
        leftHeader.style.zIndex = (Z_INDEX - 1).toString();
        right.style.zIndex = Z_INDEX.toString();
    }
}