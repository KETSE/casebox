require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/mode/perl_highlight_rules'), ['pilot/oop','pilot/lang','ace/mode/text_highlight_rules'], function (require, exports, module) {


var oop = require("pilot/oop");
var lang = require("pilot/lang");
var TextHighlightRules = require("ace/mode/text_highlight_rules").TextHighlightRules;

var PerlHighlightRules = function() {

    var keywords = lang.arrayToMap(
        ("base|constant|continue|else|elsif|for|foreach|format|goto|if|last|local|my|next|" +
         "no|package|parent|redo|require|scalar|sub|unless|until|while|use|vars").split("|")
    );

    var buildinConstants = lang.arrayToMap(
        ("ARGV|ENV|INC|SIG").split("|")
    );

    var builtinFunctions = lang.arrayToMap(
        ("getprotobynumber|getprotobyname|getservbyname|gethostbyaddr|" +
         "gethostbyname|getservbyport|getnetbyaddr|getnetbyname|getsockname|" +
         "getpeername|setpriority|getprotoent|setprotoent|getpriority|" +
         "endprotoent|getservent|setservent|endservent|sethostent|socketpair|" +
         "getsockopt|gethostent|endhostent|setsockopt|setnetent|quotemeta|" +
         "localtime|prototype|getnetent|endnetent|rewinddir|wantarray|getpwuid|" +
         "closedir|getlogin|readlink|endgrent|getgrgid|getgrnam|shmwrite|" +
         "shutdown|readline|endpwent|setgrent|readpipe|formline|truncate|" +
         "dbmclose|syswrite|setpwent|getpwnam|getgrent|getpwent|ucfirst|sysread|" +
         "setpgrp|shmread|sysseek|sysopen|telldir|defined|opendir|connect|" +
         "lcfirst|getppid|binmode|syscall|sprintf|getpgrp|readdir|seekdir|" +
         "waitpid|reverse|unshift|symlink|dbmopen|semget|msgrcv|rename|listen|" +
         "chroot|msgsnd|shmctl|accept|unpack|exists|fileno|shmget|system|" +
         "unlink|printf|gmtime|msgctl|semctl|values|rindex|substr|splice|" +
         "length|msgget|select|socket|return|caller|delete|alarm|ioctl|index|" +
         "undef|lstat|times|srand|chown|fcntl|close|write|umask|rmdir|study|" +
         "sleep|chomp|untie|print|utime|mkdir|atan2|split|crypt|flock|chmod|" +
         "BEGIN|bless|chdir|semop|shift|reset|link|stat|chop|grep|fork|dump|" +
         "join|open|tell|pipe|exit|glob|warn|each|bind|sort|pack|eval|push|" +
         "keys|getc|kill|seek|sqrt|send|wait|rand|tied|read|time|exec|recv|" +
         "eof|chr|int|ord|exp|pos|pop|sin|log|abs|oct|hex|tie|cos|vec|END|ref|" +
         "map|die|uc|lc|do").split("|")
    );

    // regexp must not have capturing parentheses. Use (?:) instead.
    // regexps are ordered -> the first match is used

    this.$rules = {
        "start" : [
            {
                token : "comment",
                regex : "#.*$"
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
                regex : "0x[0-9a-fA-F]+\\b"
            }, {
                token : "constant.numeric", // float
                regex : "[+-]?\\d+(?:(?:\\.\\d*)?(?:[eE][+-]?\\d+)?)?\\b"
            }, {
                token : function(value) {
                    if (keywords.hasOwnProperty(value))
                        return "keyword";
                    else if (buildinConstants.hasOwnProperty(value))
                        return "constant.language";
                    else if (builtinFunctions.hasOwnProperty(value))
                        return "support.function";
                    else
                        return "identifier";
                },
                regex : "[a-zA-Z_$][a-zA-Z0-9_$]*\\b"
            }, {
                token : "keyword.operator",
                regex : "\\.\\.\\.|\\|\\|=|>>=|<<=|<=>|&&=|=>|!~|\\^=|&=|\\|=|\\.=|x=|%=|\\/=|\\*=|\\-=|\\+=|=~|\\*\\*|\\-\\-|\\.\\.|\\|\\||&&|\\+\\+|\\->|!=|==|>=|<=|>>|<<|,|=|\\?\\:|\\^|\\||x|%|\\/|\\*|<|&|\\\\|~|!|>|\\.|\\-|\\+|\\-C|\\-b|\\-S|\\-u|\\-t|\\-p|\\-l|\\-d|\\-f|\\-g|\\-s|\\-z|\\-k|\\-e|\\-O|\\-T|\\-B|\\-M|\\-A|\\-X|\\-W|\\-c|\\-R|\\-o|\\-x|\\-w|\\-r|\\b(?:and|cmp|eq|ge|gt|le|lt|ne|not|or|xor)"
            }, {
                token : "lparen",
                regex : "[[({]"
            }, {
                token : "rparen",
                regex : "[\\])}]"
            }, {
                token : "text",
                regex : "\\s+"
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
};

oop.inherits(PerlHighlightRules, TextHighlightRules);

exports.PerlHighlightRules = PerlHighlightRules;
});
__bravojs_loaded_moduleIdentifier = bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/lib/ace/mode/perl_highlight_rules');