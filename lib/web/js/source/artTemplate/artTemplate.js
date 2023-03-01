/*!
 * artTemplate - Template Engine
 * https://github.com/aui/artTemplate
 * Released under the MIT, BSD, and GPL Licenses
 */

!(function () {


/**
 * 
 * @name    template
 * @param   {String}            
 * @param   {Object, String}    
 * @return  {String, Function}  HTML
 */
var template = function (filename, content) {
    return typeof content === 'string'
    ?   compile(content, {
            filename: filename
        })
    :   renderFile(filename, content);
};


template.version = '3.0.0';


/**
 * 
 * @name    template.config
 * @param   {String}    
 * @param   {Any}       
 */
template.config = function (name, value) {
    defaults[name] = value;
};



var defaults = template.defaults = {
    openTag: '<%',    // 
    closeTag: '%>',   // 
    escape: true,     //  HTML 
    cache: true,      //  options  filename 
    compress: false,  // 
    parser: null      //  @see: template-syntax.js
};


var cacheStore = template.cache = {};


/**
 * 
 * @name    template.render
 * @param   {String}    
 * @param   {Object}    
 * @return  {String}    
 */
template.render = function (source, options) {
	//iWebShop2.0
	options = options ? options : {};
	return renderFile(source, options);
    //return compile(source, options);
};


/**
 * ()
 * @name    template.render
 * @param   {String}    
 * @param   {Object}    
 * @return  {String}    
 */
var renderFile = template.renderFile = function (filename, data) {
    var fn = template.get(filename) || showDebugInfo({
        filename: filename,
        name: 'Render Error',
        message: 'Template not found'
    });
    return data ? fn(data) : fn;
};


/**
 * 
 * @param   {String}    
 * @param   {Function}  
 */
template.get = function (filename) {

    var cache;

    if (cacheStore[filename]) {
        // 
        cache = cacheStore[filename];
    } else if (typeof document === 'object') {
        // 
        var elem = document.getElementById(filename);

        if (elem) {
            var source = (elem.value || elem.innerHTML)
            .replace(/^\s*|\s*$/g, '');
            cache = compile(source, {
                filename: filename
            });
        }
    }

    return cache;
};


var toString = function (value, type) {

    if (typeof value !== 'string') {

        type = typeof value;
        if (type === 'number') {
            value += '';
        } else if (type === 'function') {
            value = toString(value.call(value));
        } else {
            value = '';
        }
    }

    return value;

};


var escapeMap = {
    "<": "&#60;",
    ">": "&#62;",
    '"': "&#34;",
    "'": "&#39;",
    "&": "&#38;"
};


var escapeFn = function (s) {
    return escapeMap[s];
};

var escapeHTML = function (content) {
    return toString(content)
    .replace(/&(?![\w#]+;)|[<>"']/g, escapeFn);
};


var isArray = Array.isArray || function (obj) {
    return ({}).toString.call(obj) === '[object Array]';
};


var each = function (data, callback) {
    var i, len;
    if (isArray(data)) {
        for (i = 0, len = data.length; i < len; i++) {
            callback.call(data, data[i], i, data);
        }
    } else {
        for (i in data) {
            callback.call(data, data[i], i);
        }
    }
};


var utils = template.utils = {

	$helpers: {},

    $include: renderFile,

    $string: toString,

    $escape: escapeHTML,

    $each: each

};/**
 * 
 * @name    template.helper
 * @param   {String}    
 * @param   {Function}  
 */
template.helper = function (name, helper) {
    helpers[name] = helper;
};

var helpers = template.helpers = utils.$helpers;




/**
 * 
 * @name    template.onerror
 * @event
 */
template.onerror = function (e) {
    var message = 'Template Error\n\n';
    for (var name in e) {
        message += '<' + name + '>\n' + e[name] + '\n\n';
    }

    if (typeof console === 'object') {
        console.error(message);
    }
};


// 
var showDebugInfo = function (e) {

    template.onerror(e);

    return function () {
        return '{Template Error}';
    };
};


/**
 * 
 * 2012-6-6 @TooBug: define  compile Node Express 
 * @name    template.compile
 * @param   {String}    
 * @param   {Object}    
 *
 *      - openTag       {String}
 *      - closeTag      {String}
 *      - filename      {String}
 *      - escape        {Boolean}
 *      - compress      {Boolean}
 *      - debug         {Boolean}
 *      - cache         {Boolean}
 *      - parser        {Function}
 *
 * @return  {Function}  
 */
var compile = template.compile = function (source, options) {

    // 
    options = options || {};
    for (var name in defaults) {
        if (options[name] === undefined) {
            options[name] = defaults[name];
        }
    }


    var filename = options.filename;


    try {

        var Render = compiler(source, options);

    } catch (e) {

        e.filename = filename || 'anonymous';
        e.name = 'Syntax Error';

        return showDebugInfo(e);

    }


    // 

    function render (data) {

        try {

            return new Render(data, filename) + '';

        } catch (e) {

            // 
            if (!options.debug) {
                options.debug = true;
                return compile(source, options)(data);
            }

            return showDebugInfo(e)();

        }

    }


    render.prototype = Render.prototype;
    render.toString = function () {
        return Render.toString();
    };


    if (filename && options.cache) {
        cacheStore[filename] = render;
    }


    return render;

};




// 
var forEach = utils.$each;


// 
var KEYWORDS =
    // 
    'break,case,catch,continue,debugger,default,delete,do,else,false'
    + ',finally,for,function,if,in,instanceof,new,null,return,switch,this'
    + ',throw,true,try,typeof,var,void,while,with'

    // 
    + ',abstract,boolean,byte,char,class,const,double,enum,export,extends'
    + ',final,float,goto,implements,import,int,interface,long,native'
    + ',package,private,protected,public,short,static,super,synchronized'
    + ',throws,transient,volatile'

    // ECMA 5 - use strict
    + ',arguments,let,yield'

    + ',undefined';

var REMOVE_RE = /\/\*[\w\W]*?\*\/|\/\/[^\n]*\n|\/\/[^\n]*$|"(?:[^"\\]|\\[\w\W])*"|'(?:[^'\\]|\\[\w\W])*'|\s*\.\s*[$\w\.]+/g;
var SPLIT_RE = /[^\w$]+/g;
var KEYWORDS_RE = new RegExp(["\\b" + KEYWORDS.replace(/,/g, '\\b|\\b') + "\\b"].join('|'), 'g');
var NUMBER_RE = /^\d[^,]*|,\d[^,]*/g;
var BOUNDARY_RE = /^,+|,+$/g;


// 
function getVariable (code) {
    return code
    .replace(REMOVE_RE, '')
    .replace(SPLIT_RE, ',')
    .replace(KEYWORDS_RE, '')
    .replace(NUMBER_RE, '')
    .replace(BOUNDARY_RE, '')
    .split(/^$|,+/);
};


// 
function stringify (code) {
    return "'" + code
    // 
    .replace(/('|\\)/g, '\\$1')
    // (windows + linux)
    .replace(/\r/g, '\\r')
    .replace(/\n/g, '\\n') + "'";
}


function compiler (source, options) {

    var debug = options.debug;
    var openTag = options.openTag;
    var closeTag = options.closeTag;
    var parser = options.parser;
    var compress = options.compress;
    var escape = options.escape;



    var line = 1;
    var uniq = {$data:1,$filename:1,$utils:1,$helpers:1,$out:1,$line:1};



    var isNewEngine = ''.trim;// '__proto__' in {}
    var replaces = isNewEngine
    ? ["$out='';", "$out+=", ";", "$out"]
    : ["$out=[];", "$out.push(", ");", "$out.join('')"];

    var concat = isNewEngine
        ? "$out+=text;return $out;"
        : "$out.push(text);";

    var print = "function(){"
    +      "var text=''.concat.apply('',arguments);"
    +       concat
    +  "}";

    var include = "function(filename,data){"
    +      "data=data||$data;"
    +      "var text=$utils.$include(filename,data,$filename);"
    +       concat
    +   "}";

    var headerCode = "'use strict';"
    + "var $utils=this,$helpers=$utils.$helpers,"
    + (debug ? "$line=0," : "");

    var mainCode = replaces[0];

    var footerCode = "return new String(" + replaces[3] + ");"

    // html
    forEach(source.split(openTag), function (code) {
        code = code.split(closeTag);

        var $0 = code[0];
        var $1 = code[1];

        // code: [html]
        if (code.length === 1) {

            mainCode += html($0);

        // code: [logic, html]
        } else {

            mainCode += logic($0);

            if ($1) {
                mainCode += html($1);
            }
        }


    });

    var code = headerCode + mainCode + footerCode;

    // 
    if (debug) {
        code = "try{" + code + "}catch(e){"
        +       "throw {"
        +           "filename:$filename,"
        +           "name:'Render Error',"
        +           "message:e.message,"
        +           "line:$line,"
        +           "source:" + stringify(source)
        +           ".split(/\\n/)[$line-1].replace(/^\\s+/,'')"
        +       "};"
        + "}";
    }



    try {


        var Render = new Function("$data", "$filename", code);
        Render.prototype = utils;

        return Render;

    } catch (e) {
        e.temp = "function anonymous($data,$filename) {" + code + "}";
        throw e;
    }




    //  HTML 
    function html (code) {

        // 
        line += code.split(/\n/).length - 1;

        // 
        if (compress) {
            code = code
            .replace(/\s+/g, ' ')
            .replace(/<!--.*?-->/g, '');
        }

        if (code) {
            code = replaces[1] + stringify(code) + replaces[2] + "\n";
        }

        return code;
    }


    // 
    function logic (code) {

        var thisLine = line;

        if (parser) {

             // 
            code = parser(code, options);

        } else if (debug) {

            // 
            code = code.replace(/\n/g, function () {
                line ++;
                return "$line=" + line +  ";";
            });

        }


        // . : <%=value%> :<%=#value%>
        // <%=#value%>  v2.0.3  <%==value%>
        if (code.indexOf('=') === 0) {

            var escapeSyntax = escape && !/^=[=#]/.test(code);

            code = code.replace(/^=[=#]?|[\s;]*$/g, '');

            // 
            if (escapeSyntax) {

                var name = code.replace(/\s*\([^\)]+\)/, '');

                //  utils.* | include | print

                if (!utils[name] && !/^(include|print)$/.test(name)) {
                    code = "$escape(" + code + ")";
                }

            // 
            } else {
                code = "$string(" + code + ")";
            }


            code = replaces[1] + code + replaces[2];

        }

        if (debug) {
            code = "$line=" + thisLine + ";" + code;
        }

        // 
        forEach(getVariable(code), function (name) {

            // name 
            if (!name || uniq[name]) {
                return;
            }

            var value;

            // 
            // :
            // [include, print] > utils > helpers > data
            if (name === 'print') {

                value = print;

            } else if (name === 'include') {

                value = include;

            } else if (utils[name]) {

                value = "$utils." + name;

            } else if (helpers[name]) {

                value = "$helpers." + name;

            } else {

                value = "$data." + name;
            }

            headerCode += name + "=" + value + ",";
            uniq[name] = true;


        });

        return code + "\n";
    }


};




// RequireJS && SeaJS
if (typeof define === 'function') {
    define(function() {
        return template;
    });

// NodeJS
} else if (typeof exports !== 'undefined') {
    module.exports = template;
} else {
    this.template = template;
}

})();