import { 
    smartQuotes, emDash, ellipsis, 
    textblockTypeInputRule, wrappingInputRule, 
    inputRules, InputRule } from 'prosemirror-inputrules';

function markInputRule(regexp, markType, getAttrs, skipStart) {
    return new InputRule(regexp, (state, match, start, end) => {
        let attrs = getAttrs instanceof Function ? getAttrs(match) : getAttrs
        let tr = state.tr
        if (match[1]) {
            let skipMatch;
            let skipLen = 0;

            if (skipMatch = skipStart && match[0].match(skipStart)) {
                console.log(skipMatch)
                skipLen = skipMatch[0].length;
                start += skipLen;
            }

            let textStart = start + match[0].indexOf(match[1]) - skipLen
            let textEnd = textStart + match[1].length
            if (textEnd < end) tr.delete(textEnd, end)
            if (textStart > start) tr.delete(start, textStart)
            end = start + match[1].length
        }
        tr.addMark(start, end, markType.create(attrs))
        tr.removeStoredMark(markType)
        return tr
    })
}

export default function inputRulesPlugin(schema) {

    var rules = [
        ...smartQuotes, 
        emDash, 
        ellipsis,

        headingRule(schema.nodes.heading),
        blockQuoteRule(schema.nodes.blockquote),
        orderedListRule(schema.nodes.ordered_list),
        bulletListRule(schema.nodes.bullet_list),

        hrRule(schema.nodes.horizontal_rule),

        ...inlineRules(schema.marks)
    ];

    return inputRules({rules})
}

function inlineRules(marks) {

    return [

        // strong (** AND __)
        markInputRule(/(?:\*\*)([^\*]+)(?:\*\*)$/, marks.strong),
        markInputRule(/(?:\s__)([^_]+)(?:__)$/, marks.strong),

        // em (* and _)
        markInputRule(/(?:^|[^\*])(?:\*)([^\*]+)(?:\*)$/, marks.em, {}, /^[^\*]/),
        markInputRule(/(?:^|[^_])(?:_)([^_]+)(?:_)$/, marks.em, {}, /^[^_]/),

        // links
        markInputRule(/(?:\[([^\]]+)\])(\([^\)]+\))$/, marks.link, function(match) {
            return {href: match[2]}
        }),

        // code (prosemirror-codemark adds this)
        // markInputRule(/(?:`)([^`]+)(?:`)$/, marks.code),

        // strikethrough
        markInputRule(/(?:~~)([^~]+)(?:~~)$/, marks.strike),

        // sup & sub
        markInputRule(/(?:\^)([^\^]+)(?:\^)$/, marks.sup),
        markInputRule(/(?:~)([^~]+)(?:~)$/, marks.sub),

        // mark
        markInputRule(/(?:==)([^=]+)(?:==)/, marks.highlight),

    ];

}

function headingRule(nodeType) {
    return textblockTypeInputRule(
            new RegExp("^(#{1,6})\\s$"),
            nodeType,
            function (match) { 
                return ({level: match[1].length}); }
        )
}

function blockQuoteRule(nodeType) {
    return wrappingInputRule(/^>\s$/, nodeType)
}

function orderedListRule(nodeType) {
    return wrappingInputRule(
        /^(\d+)\.\s$/, nodeType, 
        function (match) { 
            return ({
                order: +match[1]
            });
        },
        function (match, node) { 
            return node.childCount + node.attrs.order == +match[1]; 
        }
    )
}

function hrRule(nodeType) {
    return new InputRule(/^—-$/, function (state, match, start) {
        const {$from} = state.selection

        if ($from.depth !== 1)
            return null;

        let tr = state.tr
            .replaceWith(start - 1, start + 1, nodeType.create())
            .scrollIntoView()

        return tr;
    });
}

function bulletListRule(nodeType) {
    return wrappingInputRule(/^\s*([-+*])\s$/, nodeType)
}