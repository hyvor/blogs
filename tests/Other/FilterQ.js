
function FilterQ(str) {
    let i = 0;

    str = str.trim();

    // add brackets around
    if (!str.match(/^\(.*\)$/)) {
        str = '(' + str + ')';
    }

    skipWhitespace();
    return parseParentheses();

    function parseParentheses() {
        i++;
        var result = [];
        while (str[i] !== ')') {
            
            var boolOperator = 'AND';

            if (str[i] === '(') {
                parseParentheses();
            }

            skipWhitespace();

            let propertyMatch = str.slice(i).match(/^([a-zA-Z0-9_.]+)(=|!=|>|<|>=|<=)/); 
            if (propertyMatch) {
                let fullMatch = propertyMatch[0];
                let property = propertyMatch[1];
                let operator = propertyMatch[2];

                i += fullMatch.length;

                let value = parseValue();

                result.push([property, operator, value]);
            }
        }
        i++;
        return result;
    }

    function parseValue() {
        skipWhitespace();

        const value = parseString() ??
            parseNumber() ??
            parseKeyword("true", true) ??
            parseKeyword("false", false) ??
            parseKeyword("null", null);

        skipWhitespace();

        return value;
    }

    function parseKeyword(name, value) {
        if (str.slice(i, i + name.length) === name) {
            i += name.length;
            return value;
        }
    }

    function parseString() {
        if (str[i] === "'") { // single quote
            i++;

            let result = "";
            while (str[i] !== "'") {
                if (str[i] === "\\") {
                    const char = str[i + 1];
                    if (
                        char === "'" ||
                        char === "\\" ||
                        char === "/" ||
                        char === "b" ||
                        char === "f" ||
                        char === "n" ||
                        char === "r" ||
                        char === "t"
                    ) {
                        result += char;
                        i++;
                    } else if (char === "u") {
                        if (
                            isHexadecimal(str[i + 2]) &&
                            isHexadecimal(str[i + 3]) &&
                            isHexadecimal(str[i + 4]) &&
                            isHexadecimal(str[i + 5])
                        ) {
                            result += String.fromCharCode(
                                parseInt(str.slice(i + 2, i + 6), 16)
                            );
                            i += 5;
                        }
                    }
                } else {
                    result += str[i];
                }
                i++;
            }
            i++;
            return result;
        }

        function isHexadecimal(char) {
            return (
                (char >= "0" && char <= "9") ||
                (char.toLowerCase() >= "a" && char.toLowerCase() <= "f")
            );
        }
    }

    function parseNumber() {
        let start = i;
        if (str[i] === "-") {
            i++;
        }
        if (str[i] === "0") {
            i++;
        } else if (str[i] >= "1" && str[i] <= "9") {
            i++;
            while (str[i] >= "0" && str[i] <= "9") {
                i++;
            }
        }

        if (str[i] === ".") {
            i++;
            while (str[i] >= "0" && str[i] <= "9") {
                i++;
            }
        }
        if (str[i] === "e" || str[i] === "E") {
            i++;
            if (str[i] === "-" || str[i] === "+") {
                i++;
            }
            while (str[i] >= "0" && str[i] <= "9") {
                i++;
            }
        }
        if (i > start) {
            return Number(str.slice(start, i));
        }
    }

    function skipWhitespace() {
        while (
            str[i] === " " ||
            str[i] === "\n" ||
            str[i] === "\t" ||
            str[i] === "\r"
        ) {
            i++;
        }
    }

}

console.log(FilterQ("str='photo'"));