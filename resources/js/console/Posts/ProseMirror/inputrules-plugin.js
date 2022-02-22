import { smartQuotes, emDash, ellipsis, textblockTypeInputRule, wrappingInputRule, inputRules, InputRule } from 'prosemirror-inputrules';

export default function inputRulesPlugin(schema) {

    var rules = [
        ...smartQuotes, 
        emDash, 
        ellipsis,

        headingRule(schema.nodes.heading),
        codeBlockRule(schema.nodes.code_block),
        blockQuoteRule(schema.nodes.blockquote),
        orderedListRule(schema.nodes.ordered_list),
        bulletListRule(schema.nodes.bullet_list),

        hrRule(schema.nodes.horizontal_rule)
    ];

    return inputRules({rules})
}

function headingRule(nodeType) {
    return textblockTypeInputRule(
            new RegExp("^(#{1,6})\\s$"),
            nodeType,
            function (match) { 
                return ({level: match[1].length}); }
        )
}

function codeBlockRule(nodeType) {
    return textblockTypeInputRule(/^```$/, nodeType)
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