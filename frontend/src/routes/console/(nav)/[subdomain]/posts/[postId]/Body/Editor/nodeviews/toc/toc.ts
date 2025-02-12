import type { Node } from "prosemirror-model";

export interface TocEntry {
    title: string,
    id: string | null,
    level: number,
    children: TocEntry[],
    pos: number
}
type Heading = Omit<TocEntry, 'children'>;

export function generateToc(doc: Node, levels: number[]) {
        
    const allHeadings = findHeadings(doc, levels);

    function findHeadings(doc: Node, levels: number[]) {
        let headings : Heading[] = [];
        doc.descendants((node, pos) => {
            if (node.type.name === 'heading') {
                const level = node.attrs.level;
                if (!level)
                    return;
                if (!levels.includes(level))
                    return;
                headings.push({
                    title: node.textContent,
                    id: node.attrs.id,
                    level: level,
                    pos
                });
            }
        });
        return headings;
    }

    function buildToc(headings: Heading[], previousLevel : null | number = null) {

        let toc: TocEntry[] = [];

        let currentLevel : null | number = null;

        for (let i = 0; i < headings.length; i++) {

            const heading = headings[i]!;

            if (previousLevel && heading.level <= previousLevel) {
                // if larger than previous level, stop
                break;
            }

            if (currentLevel && heading.level > currentLevel) {
                // handled via recursion
                continue;
            }

            toc.push({
                title: heading.title,
                id: heading.id || null,
                level: heading.level,
                children: buildToc(
                    headings.slice(i + 1),
                    heading.level
                ),
                pos: heading.pos
            });

            currentLevel = heading.level;

        }

        return toc;

    }

    return buildToc(allHeadings);

}