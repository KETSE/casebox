require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/theme/eclipse'), ['pilot/dom'], function (require, exports, module) {


    var dom = require("pilot/dom");

    var cssText = ".ace-eclipse .ace_editor {\
  border: 2px solid rgb(159, 159, 159);\
}\
\
.ace-eclipse .ace_editor.ace_focus {\
  border: 2px solid #327fbd;\
}\
\
.ace-eclipse .ace_gutter {\
  width: 50px;\
  background: rgb(227, 227, 227);\
  border-right: 1px solid rgb(159, 159, 159);	 \
  color: rgb(136, 136, 136);\
}\
\
.ace-eclipse .ace_gutter-layer {\
  width: 100%;\
  text-align: right;\
}\
\
.ace-eclipse .ace_gutter-layer .ace_gutter-cell {\
  padding-right: 6px;\
}\
\
.ace-eclipse .ace_text-layer {\
  cursor: text;\
}\
\
.ace-eclipse .ace_cursor {\
  border-left: 1px solid black;\
}\
\
.ace-eclipse .ace_line .ace_keyword, .ace-eclipse .ace_line .ace_variable {\
  color: rgb(127, 0, 85);\
}\
\
.ace-eclipse .ace_line .ace_constant.ace_buildin {\
  color: rgb(88, 72, 246);\
}\
\
.ace-eclipse .ace_line .ace_constant.ace_library {\
  color: rgb(6, 150, 14);\
}\
\
.ace-eclipse .ace_line .ace_function {\
  color: rgb(60, 76, 114);\
}\
\
.ace-eclipse .ace_line .ace_string {\
  color: rgb(42, 0, 255);\
}\
\
.ace-eclipse .ace_line .ace_comment {\
  color: rgb(63, 127, 95);\
}\
\
.ace-eclipse .ace_line .ace_comment.ace_doc {\
  color: rgb(63, 95, 191);\
}\
\
.ace-eclipse .ace_line .ace_comment.ace_doc.ace_tag {\
  color: rgb(127, 159, 191);\
}\
\
.ace-eclipse .ace_line .ace_constant.ace_numeric {\
}\
\
.ace-eclipse .ace_line .ace_tag {\
	color: rgb(63, 127, 127);\
}\
\
.ace-eclipse .ace_line .ace_xml_pe {\
  color: rgb(104, 104, 91);\
}\
\
.ace-eclipse .ace_marker-layer .ace_selection {\
  background: rgb(181, 213, 255);\
}\
\
.ace-eclipse .ace_marker-layer .ace_bracket {\
  margin: -1px 0 0 -1px;\
  border: 1px solid rgb(192, 192, 192);\
}\
\
.ace-eclipse .ace_marker-layer .ace_active_line {\
  background: rgb(232, 242, 254);\
}";

    // import CSS once
    dom.importCssString(cssText);

    exports.cssClass = "ace-eclipse";
});
__bravojs_loaded_moduleIdentifier = bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/theme/eclipse');