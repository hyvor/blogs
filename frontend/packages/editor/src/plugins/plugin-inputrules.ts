import {
	smartQuotes,
	emDash,
	ellipsis,
	textblockTypeInputRule,
	wrappingInputRule,
	inputRules,
	InputRule
} from 'prosemirror-inputrules';
import type { MarkType, Node, NodeType } from 'prosemirror-model';
import { findWrapping, canJoin } from 'prosemirror-transform';
import schema from '../schema';

function markInputRule(regexp: RegExp, markType: MarkType, getAttrs?: any, skipStart?: any) {
	return new InputRule(regexp, (state, match, start, end) => {
		let attrs = getAttrs instanceof Function ? getAttrs(match) : getAttrs;
		let tr = state.tr;
		if (match[1]) {
			let skipMatch;
			let skipLen = 0;

			if ((skipMatch = skipStart && match[0].match(skipStart))) {
				skipLen = skipMatch[0].length;
				start += skipLen;
			}

			let textStart = start + match[0].indexOf(match[1]) - skipLen;
			let textEnd = textStart + match[1].length;
			if (textEnd < end) tr.delete(textEnd, end);
			if (textStart > start) tr.delete(start, textStart);
			end = start + match[1].length;
		}
		tr.addMark(start, end, markType.create(attrs));
		tr.removeStoredMark(markType);
		return tr;
	});
}

export default function inputRulesPlugin() {
	var rules = [
		...smartQuotes,
		emDash,
		ellipsis,

		headingRule(schema.nodes.heading!),
		blockQuoteRule(schema.nodes.blockquote!),
		orderedListRule(schema.nodes.ordered_list!),
		bulletListRule(schema.nodes.bullet_list!),

		hrRule(schema.nodes.horizontal_rule!),

		...inlineRules(schema.marks)
	];

	return inputRules({ rules });
}

function inlineRules(marks: any) {
	return [
		// strong (** AND __)
		markInputRule(/(?:\*\*)([^\*]+)(?:\*\*)$/, marks.strong),
		markInputRule(/(?:\s__)([^_]+)(?:__)$/, marks.strong),

		// em (* and _)
		markInputRule(/(?:^|[^\*])(?:\*)([^\*]+)(?:\*)$/, marks.em, {}, /^[^\*]/),
		markInputRule(/(?:^|[^_])(?:_)([^_]+)(?:_)$/, marks.em, {}, /^[^_]/),

		// links
		markInputRule(/(?:\[([^\]]+)\])(?:\(([^\)]+)\))$/, marks.link, function (match: any) {
			return { href: match[2] };
		}),

		// code (prosemirror-codemark adds this)
		// markInputRule(/(?:`)([^`]+)(?:`)$/, marks.code),

		// strikethrough
		markInputRule(/(?:~~)([^~]+)(?:~~)$/, marks.strike),

		// sup & sub
		markInputRule(/(?:\^)([^\^]+)(?:\^)$/, marks.sup),
		markInputRule(/(?:~)([^~]+)(?:~)$/, marks.sub),

		// mark
		markInputRule(/(?:==)([^=]+)(?:==)/, marks.highlight)
	];
}

function wrappingInputRuleFiltered(
	regexp: RegExp,
	nodeType: NodeType,
	getAttrs: object | Function,
	joinPredicate: any,
	filters: any
) {
	return new InputRule(regexp, (state, match, start, end) => {
		const currentNode = state.selection.$from.node();
		if (currentNode.type && filters.includes(currentNode.type.name)) {
			return null;
		}
		let attrs = getAttrs instanceof Function ? getAttrs(match) : getAttrs;
		let tr = state.tr.delete(start, end);
		let $start = tr.doc.resolve(start),
			range = $start.blockRange(),
			wrapping = range && findWrapping(range, nodeType, attrs);
		if (!wrapping) return null;
		tr.wrap(range!, wrapping);
		let before = tr.doc.resolve(start - 1).nodeBefore;
		if (
			before &&
			before.type == nodeType &&
			canJoin(tr.doc, start - 1) &&
			(!joinPredicate || joinPredicate(match, before))
		)
			tr.join(start - 1);
		return tr;
	});
}

function headingRule(nodeType: NodeType) {
	return textblockTypeInputRule(new RegExp('^(#{1,6})\\s$'), nodeType, function (match: any) {
		return { level: match[1].length };
	});
}

function blockQuoteRule(nodeType: NodeType) {
	return wrappingInputRule(/^>\s$/, nodeType);
}

function orderedListRule(nodeType: NodeType) {
	return wrappingInputRuleFiltered(
		/^(\d+)\.\s$/,
		nodeType,
		function (match: any) {
			return {
				order: +match[1]
			};
		},
		function (match: any, node: Node) {
			return node.childCount + node.attrs.order == +match[1];
		},
		['heading']
	);
}

function hrRule(nodeType: NodeType) {
	return new InputRule(/^—-$/, function (state, match, start) {
		const { $from } = state.selection;

		if ($from.depth !== 1) return null;

		let tr = state.tr.replaceWith(start - 1, start + 1, nodeType.create()).scrollIntoView();

		return tr;
	});
}

function bulletListRule(nodeType: NodeType) {
	return wrappingInputRuleFiltered(/^\s*([-+*])\s$/, nodeType, {}, null, ['heading']);
}
