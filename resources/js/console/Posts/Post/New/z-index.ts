

function els() {
    return {
        leftHeader: document.querySelector('.post-left-header') as HTMLElement | null,
        right: document.querySelector('.post-right') as HTMLElement | null,
    }
}

export function bringLeftHeaderToFront() {
    const { leftHeader, right } = els()
    if (leftHeader && right) {
        leftHeader.style.zIndex = '10';
        right.style.zIndex = '9';
    }
}

export function bringRightToFront() {
    const { leftHeader, right } = els()
    if (leftHeader && right) {
        leftHeader.style.zIndex = '9';
        right.style.zIndex = '10';
    }
}