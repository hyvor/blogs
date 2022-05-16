var __createBinding : any = (this && this.__createBinding) || (Object.create ? (function(o: any, m: any, k: any, k2: any) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o: any, m: any, k: any, k2: any) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault : any = (this && this.__setModuleDefault) || (Object.create ? (function(o: any, v: any) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o: any, v: any) {
    o["default"] = v;
});
var __importStar : any = (this && this.__importStar) || function (mod: any) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};

const ts = __importStar(require("typescript"));
const typescript_1 = require("typescript");
exports.default = {
    visitKeaProperty({ name, parsedLogic, node, getTypeNodeForNode, prepareForPrint } : any) {
        let typeNode;
        console.log(parsedLogic)
        parsedLogic.actions.push({
            name: 'submitForm',
            parameters: [],
            returnTypeNode: typescript_1.factory.createTypeLiteralNode([
                typescript_1.factory.createPropertySignature(undefined, typescript_1.factory.createIdentifier('value'), undefined, typescript_1.factory.createKeywordTypeNode(ts.SyntaxKind.BooleanKeyword)),
            ]),
        });
    },
};