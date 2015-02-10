require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/mode/svg_highlight_rules'), ['pilot/oop','ace/mode/javascript_highlight_rules','ace/mode/xml_highlight_rules'], function (require, exports, module) {


var oop = require("pilot/oop");
var JavaScriptHighlightRules = require("ace/mode/javascript_highlight_rules").JavaScriptHighlightRules;
var XmlHighlightRules = require("ace/mode/xml_highlight_rules").XmlHighlightRules;

var SvgHighlightRules = function() {
    XmlHighlightRules.call(this);

    this.$rules.start.splice(3, 0, {
        token : "text",
        regex : "<(?=\s*script)",
        next : "script"
    });
    this.$rules.script = [{
        token : "text",
        regex : ">",
        next : "js-start"
    }, {
        token : "keyword",
        regex : "[-_a-zA-Z0-9:]+"
    }, {
        token : "text",
        regex : "\\s+"
    }, {
        token : "string",
        regex : '".*?"'
    }, {
        token : "string",
        regex : "'.*?'"
    }];    

    var jsRules = new JavaScriptHighlightRules().getRules();
    this.addRules(jsRules, "js-");
    this.$rules["js-start"].unshift({
        token: "comment",
        regex: "\\/\\/.*(?=<\\/script>)",
        next: "tag"
    }, {
        token: "text",
        regex: "<\\/(?=script)",
        next: "tag"
    });

};

oop.inherits(SvgHighlightRules, XmlHighlightRules);

exports.SvgHighlightRules = SvgHighlightRules;
});
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/mode/javascript_highlight_rules'), ['pilot/oop','pilot/lang','ace/mode/doc_comment_highlight_rules','ace/mode/text_highlight_rules'], function (require, exports, module) {


var oop = require("pilot/oop");
var lang = require("pilot/lang");
var DocCommentHighlightRules = require("ace/mode/doc_comment_highlight_rules").DocCommentHighlightRules;
var TextHighlightRules = require("ace/mode/text_highlight_rules").TextHighlightRules;

var JavaScriptHighlightRules = function() {

    var docComment = new DocCommentHighlightRules();

    var keywords = lang.arrayToMap(
        ("break|case|catch|continue|default|delete|do|else|finally|for|function|" +
        "if|in|instanceof|new|return|switch|throw|try|typeof|let|var|while|with|" +
        "const|yield|import|get|set").split("|")
    );

    var buildinConstants = lang.arrayToMap(
        ("null|Infinity|NaN|undefined").split("|")
    );

    var futureReserved = lang.arrayToMap(
        ("class|enum|extends|super|export|implements|private|" +
        "public|interface|package|protected|static").split("|")
    );

    // regexp must not have capturing parentheses. Use (?:) instead.
    // regexps are ordered -> the first match is used

    this.$rules = {
        "start" : [
            {
                token : "comment",
                regex : "\\/\\/.*$"
            },
            docComment.getStartRule("doc-start"),
            {
                token : "comment", // multi line comment
                regex : "\\/\\*",
                next : "comment"
            }, {
                token : "string.regexp",
                regex : "[/](?:(?:\\[(?:\\\\]|[^\\]])+\\])|(?:\\\\/|[^\\]/]))*[/]\\w*\\s*(?=[).,;]|$)"
            }, {
                token : "string", // single line
                regex : '["](?:(?:\\\\.)|(?:[^"\\\\]))*?["]'
            }, {
                token : "string", // multi line string start
                regex : '["].*\\\\$',
                next : "qqstring"
            }, {
                token : "string", // single line
                regex : "['](?:(?:\\\\.)|(?:[^'\\\\]))*?[']"
            }, {
                token : "string", // multi line string start
                regex : "['].*\\\\$",
                next : "qstring"
            }, {
                token : "constant.numeric", // hex
                regex : "0[xX][0-9a-fA-F]+\\b"
            }, {
                token : "constant.numeric", // float
                regex : "[+-]?\\d+(?:(?:\\.\\d*)?(?:[eE][+-]?\\d+)?)?\\b"
            }, {
                token : "constant.language.boolean",
                regex : "(?:true|false)\\b"
            }, {
                token : function(value) {
                    if (value == "this")
                        return "variable.language";
                    else if (keywords.hasOwnProperty(value))
                        return "keyword";
                    else if (buildinConstants.hasOwnProperty(value))
                        return "constant.language";
                    else if (futureReserved.hasOwnProperty(value))
                        return "invalid.illegal";
                    else if (value == "debugger")
                        return "invalid.deprecated";
                    else
                        return "identifier";
                },
                // TODO: Unicode escape sequences
                // TODO: Unicode identifiers
                regex : "[a-zA-Z_$][a-zA-Z0-9_$]*\\b"
            }, {
                token : "keyword.operator",
                regex : "!|\\$|%|&|\\*|\\-\\-|\\-|\\+\\+|\\+|~|===|==|=|!=|!==|<=|>=|<<=|>>=|>>>=|<>|<|>|!|&&|\\|\\||\\?\\:|\\*=|%=|\\+=|\\-=|&=|\\^=|\\b(?:in|instanceof|new|delete|typeof|void)"
            }, {
                token : "lparen",
                regex : "[[({]"
            }, {
                token : "rparen",
                regex : "[\\])}]"
            }, {
                token: "comment",
                regex: "^#!.*$" 
            }, {
                token : "text",
                regex : "\\s+"
            }
        ],
        "comment" : [
            {
                token : "comment", // closing comment
                regex : ".*?\\*\\/",
                next : "start"
            }, {
                token : "comment", // comment spanning whole line
                regex : ".+"
            }
        ],
        "qqstring" : [
            {
                token : "string",
                regex : '(?:(?:\\\\.)|(?:[^"\\\\]))*?"',
                next : "start"
            }, {
                token : "string",
                regex : '.+'
            }
        ],
        "qstring" : [
            {
                token : "string",
                regex : "(?:(?:\\\\.)|(?:[^'\\\\]))*?'",
                next : "start"
            }, {
                token : "string",
                regex : '.+'
            }
        ]
    };

    this.addRules(docComment.getRules(), "doc-");
    this.$rules["doc-start"][0].next = "start";
};

oop.inherits(JavaScriptHighlightRules, TextHighlightRules);

exports.JavaScriptHighlightRules = JavaScriptHighlightRules;
});
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/mode/xml_highlight_rules'), ['pilot/oop','ace/mode/text_highlight_rules'], function (require, exports, module) {


var oop = require("pilot/oop");
var TextHighlightRules = require("ace/mode/text_highlight_rules").TextHighlightRules;

var XmlHighlightRules = function() {

    // regexp must not have capturing parentheses
    // regexps are ordered -> the first match is used

    this.$rules = {
        start : [ {
            token : "text",
            regex : "<\\!\\[CDATA\\[",
            next : "cdata"
        }, {
            token : "xml_pe",
            regex : "<\\?.*?\\?>"
        }, {
            token : "comment",
            regex : "<\\!--",
            next : "comment"
        }, {
            token : "text", // opening tag
            regex : "<\\/?",
            next : "tag"
        }, {
            token : "text",
            regex : "\\s+"
        }, {
            token : "text",
            regex : "[^<]+"
        } ],

        tag : [ {
            token : "text",
            regex : ">",
            next : "start"
        }, {
            token : "keyword",
            regex : "[-_a-zA-Z0-9:]+"
        }, {
            token : "text",
            regex : "\\s+"
        }, {
            token : "string",
            regex : '".*?"'
        }, {
            token : "string", // multi line string start
            regex : '["].*$',
            next : "qqstring"
        }, {
            token : "string",
            regex : "'.*?'"
        }, {
            token : "string", // multi line string start
            regex : "['].*$",
            next : "qstring"
        }],

        qstring: [{
            token : "string",
            regex : ".*'",
            next : "tag"
        }, {
            token : "string",
            regex : '.+'
        }],
        
        qqstring: [{
            token : "string",
            regex : ".*\"",
            next : "tag"
        }, {
            token : "string",
            regex : '.+'
        }],
        
        cdata : [ {
            token : "text",
            regex : "\\]\\]>",
            next : "start"
        }, {
            token : "text",
            regex : "\\s+"
        }, {
            token : "text",
            regex : "(?:[^\\]]|\\](?!\\]>))+"
        } ],

        comment : [ {
            token : "comment",
            regex : ".*?-->",
            next : "start"
        }, {
            token : "comment",
            regex : ".+"
        } ]
    };
};

oop.inherits(XmlHighlightRules, TextHighlightRules);

exports.XmlHighlightRules = XmlHighlightRules;
});
__bravojs_loaded_moduleIdentifier = bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/mode/svg_highlight_rules');