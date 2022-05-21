import {VisitKeaPropertyArguments} from "kea-typegen";
import ts from 'typescript';

/**
 * From https://github.com/keajs/kea-typegen/blob/kea-3.0/samples/typed-builder/typedForm.typegen.ts
 */
export function ajax({ name, parsedLogic, node, type, getTypeNodeForNode, prepareForPrint } : VisitKeaPropertyArguments) {

    if (name !== 'ajax')
        return;

    let typeNode

    // extract `() => ({})` to just `{}`
    if (
        ts.isArrowFunction(node) &&
        ts.isParenthesizedExpression(node.body) &&
        ts.isObjectLiteralExpression(node.body.expression)
    ) {
        node = node.body.expression
    }

    for (const property of type.getProperties()) {
        console.log(property);
    }

}