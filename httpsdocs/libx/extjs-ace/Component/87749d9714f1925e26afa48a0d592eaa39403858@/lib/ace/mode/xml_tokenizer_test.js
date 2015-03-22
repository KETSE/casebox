require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/mode/xml_tokenizer_test'), ['ace/mode/xml','ace/test/assertions','asyncjs'], function (require, exports, module) {


var XmlMode = require("ace/mode/xml").Mode;
var assert = require("ace/test/assertions");

module.exports = {
    setUp : function() {
        this.tokenizer = new XmlMode().getTokenizer();
    },

    "test: tokenize1" : function() {

        var line = "<Juhu>//Juhu Kinners</Kinners>";
        var tokens = this.tokenizer.getLineTokens(line, "start").tokens;

        assert.equal(5, tokens.length);
        assert.equal("text", tokens[0].type);
        assert.equal("keyword", tokens[1].type);
        assert.equal("text", tokens[2].type);
        assert.equal("keyword", tokens[3].type);
        assert.equal("text", tokens[4].type);
    }
};

});
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/mode/xml'), ['pilot/oop','ace/mode/text','ace/tokenizer','ace/mode/xml_highlight_rules'], function (require, exports, module) {


var oop = require("pilot/oop");
var TextMode = require("ace/mode/text").Mode;
var Tokenizer = require("ace/tokenizer").Tokenizer;
var XmlHighlightRules = require("ace/mode/xml_highlight_rules").XmlHighlightRules;

var Mode = function() {
    this.$tokenizer = new Tokenizer(new XmlHighlightRules().getRules());
};

oop.inherits(Mode, TextMode);

(function() {

    this.getNextLineIndent = function(state, line, tab) {
        return this.$getIndent(line);
    };

}).call(Mode.prototype);

exports.Mode = Mode;
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
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/test/assertions'), ['assert'], function (require, exports, module) {


var assert = require("assert");
    
assert.position = function(cursor, row, column) {
    assert.equal(cursor.row, row);
    assert.equal(cursor.column, column);
};

assert.range = function(range, startRow, startColumn, endRow, endColumn) {
    assert.position(range.start, startRow, startColumn);
    assert.position(range.end, endRow, endColumn);
};

assert.notOk = function(value) {
    assert.equal(value, false);   
}

exports.jsonEquals = function(foundJson, expectedJson) {
    assert.equal(JSON.stringify(foundJson), JSON.stringify(expectedJson));
};

module.exports = assert;

});
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/a3d9ddf257e98144c883cd2dbc03ab62243dbc09@/modules/assert'), [], function (require, exports, module) {

});
__bravojs_loaded_moduleIdentifier = bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/mode/xml_tokenizer_test');