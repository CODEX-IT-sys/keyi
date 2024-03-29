/**
 @Name：Kz.layedit 富文本编辑器
 @Author：贤心
 @Modifier:KnifeZ
 @License：MIT
 @Version: V18.11.27
 */

"use strict";
function AddCustomThemes(t, e, i) {
    var n = [];
    return layui.each(t,
        function(t, l) {
            n.push('<option value="' + e[t] + '"  data-img="' + i[t] + '">' + l + "</option>")
        }),
        ['<li class="layui-form-item" style="position: relative">', '<label class="layui-form-label">主题选择</label>', '<div class="layui-input-block">', '<select name="theme" style="display:block;height:38px;width:100%;">' + n.join("") + "</select>", "</div>", "</li>"].join("")
}
function style_html(t, e, i, n) {
    function l() {
        return this.pos = 0,
            this.token = "",
            this.current_mode = "CONTENT",
            this.tags = {
                parent: "parent1",
                parentcount: 1,
                parent1: ""
            },
            this.tag_type = "",
            this.token_text = this.last_token = this.last_text = this.token_type = "",
            this.Utils = {
                whitespace: "\n\r\t ".split(""),
                single_token: "br,input,link,meta,!doctype,basefont,base,area,hr,wbr,param,img,isindex,?xml,embed".split(","),
                extra_liners: "head,body,/html".split(","),
                in_array: function(t, e) {
                    for (var i = 0; i < e.length; i++) if (t === e[i]) return ! 0;
                    return ! 1
                }
            },
            this.get_content = function() {
                for (var t = "",
                         e = [], i = !1;
                     "<" !== this.input.charAt(this.pos);) {
                    if (this.pos >= this.input.length) return e.length ? e.join("") : ["", "TK_EOF"];
                    if (t = this.input.charAt(this.pos), this.pos++, this.line_char_count++, this.Utils.in_array(t, this.Utils.whitespace)) e.length && (i = !0),
                        this.line_char_count--;
                    else {
                        if (i) {
                            if (this.line_char_count >= this.max_char) {
                                e.push("\n");
                                for (var n = 0; n < this.indent_level; n++) e.push(this.indent_string);
                                this.line_char_count = 0
                            } else e.push(" "),
                                this.line_char_count++;
                            i = !1
                        }
                        e.push(t)
                    }
                }
                return e.length ? e.join("") : ""
            },
            this.get_script = function() {
                var t = "",
                    e = [],
                    i = new RegExp("<\/script>", "igm");
                i.lastIndex = this.pos;
                for (var n = i.exec(this.input), l = n ? n.index: this.input.length; this.pos < l;) {
                    if (this.pos >= this.input.length) return e.length ? e.join("") : ["", "TK_EOF"];
                    t = this.input.charAt(this.pos),
                        this.pos++,
                        e.push(t)
                }
                return e.length ? e.join("") : ""
            },
            this.record_tag = function(t) {
                this.tags[t + "count"] ? (this.tags[t + "count"]++, this.tags[t + this.tags[t + "count"]] = this.indent_level) : (this.tags[t + "count"] = 1, this.tags[t + this.tags[t + "count"]] = this.indent_level),
                    this.tags[t + this.tags[t + "count"] + "parent"] = this.tags.parent,
                    this.tags.parent = t + this.tags[t + "count"]
            },
            this.retrieve_tag = function(t) {
                if (this.tags[t + "count"]) {
                    for (var e = this.tags.parent; e && t + this.tags[t + "count"] !== e;) e = this.tags[e + "parent"];
                    e && (this.indent_level = this.tags[t + this.tags[t + "count"]], this.tags.parent = this.tags[e + "parent"]),
                        delete this.tags[t + this.tags[t + "count"] + "parent"],
                        delete this.tags[t + this.tags[t + "count"]],
                        1 == this.tags[t + "count"] ? delete this.tags[t + "count"] : this.tags[t + "count"]--
                }
            },
            this.get_tag = function() {
                var t = "",
                    e = [],
                    i = !1;
                do {
                    if (this.pos >= this.input.length) return e.length ? e.join("") : ["", "TK_EOF"];
                    t = this.input.charAt(this.pos), this.pos++, this.line_char_count++, this.Utils.in_array(t, this.Utils.whitespace) ? (i = !0, this.line_char_count--) : ("'" !== t && '"' !== t || e[1] && "!" === e[1] || (t += this.get_unformatted(t), i = !0), "=" === t && (i = !1), e.length && "=" !== e[e.length - 1] && ">" !== t && i && (this.line_char_count >= this.max_char ? (this.print_newline(!1, e), this.line_char_count = 0) : (e.push(" "), this.line_char_count++), i = !1), e.push(t))
                } while (">" !== t );
                var n, l = e.join("");
                n = -1 != l.indexOf(" ") ? l.indexOf(" ") : l.indexOf(">");
                var a = l.substring(1, n).toLowerCase();
                if ("/" === l.charAt(l.length - 2) || this.Utils.in_array(a, this.Utils.single_token)) this.tag_type = "SINGLE";
                else if ("script" === a) this.record_tag(a),
                    this.tag_type = "SCRIPT";
                else if ("style" === a) this.record_tag(a),
                    this.tag_type = "STYLE";
                else if ("!" === a.charAt(0)) if ( - 1 != a.indexOf("[if")) {
                    if ( - 1 != l.indexOf("!IE")) {
                        var o = this.get_unformatted("--\x3e", l);
                        e.push(o)
                    }
                    this.tag_type = "START"
                } else if ( - 1 != a.indexOf("[endif")) this.tag_type = "END",
                    this.unindent();
                else if ( - 1 != a.indexOf("[cdata[")) {
                    var o = this.get_unformatted("]]>", l);
                    e.push(o),
                        this.tag_type = "SINGLE"
                } else {
                    var o = this.get_unformatted("--\x3e", l);
                    e.push(o),
                        this.tag_type = "SINGLE"
                } else "/" === a.charAt(0) ? (this.retrieve_tag(a.substring(1)), this.tag_type = "END") : (this.record_tag(a), this.tag_type = "START"),
                this.Utils.in_array(a, this.Utils.extra_liners) && this.print_newline(!0, this.output);
                return e.join("")
            },
            this.get_unformatted = function(t, e) {
                if (e && -1 != e.indexOf(t)) return "";
                var i = "",
                    n = "",
                    l = !0;
                do {
                    if (i = this.input.charAt(this.pos), this.pos++, this.Utils.in_array(i, this.Utils.whitespace)) {
                        if (!l) {
                            this.line_char_count--;
                            continue
                        }
                        if ("\n" === i || "\r" === i) {
                            n += "\n";
                            for (var a = 0; a < this.indent_level; a++) n += this.indent_string;
                            l = !1,
                                this.line_char_count = 0;
                            continue
                        }
                    }
                    n += i, this.line_char_count++, l = !0
                } while ( - 1 == n . indexOf ( t ));
                return n
            },
            this.get_token = function() {
                var t;
                if ("TK_TAG_SCRIPT" === this.last_token) {
                    var e = this.get_script();
                    return "string" != typeof e ? e: (t = js_beautify(e, this.indent_size, this.indent_character, this.indent_level), [t, "TK_CONTENT"])
                }
                if ("CONTENT" === this.current_mode) return t = this.get_content(),
                    "string" != typeof t ? t: [t, "TK_CONTENT"];
                if ("TAG" === this.current_mode) {
                    if ("string" != typeof(t = this.get_tag())) return t;
                    return [t, "TK_TAG_" + this.tag_type]
                }
            },
            this.printer = function(t, e, i, n) {
                this.input = t || "",
                    this.output = [],
                    this.indent_character = e || " ",
                    this.indent_string = "",
                    this.indent_size = i || 2,
                    this.indent_level = 0,
                    this.max_char = n || 7e3,
                    this.line_char_count = 0;
                for (var l = 0; l < this.indent_size; l++) this.indent_string += this.indent_character;
                this.print_newline = function(t, e) {
                    if (this.line_char_count = 0, e && e.length) {
                        if (!t) for (; this.Utils.in_array(e[e.length - 1], this.Utils.whitespace);) e.pop();
                        e.push("\n");
                        for (var i = 0; i < this.indent_level; i++) e.push(this.indent_string)
                    }
                },
                    this.print_token = function(t) {
                        this.output.push(t)
                    },
                    this.indent = function() {
                        this.indent_level++
                    },
                    this.unindent = function() {
                        this.indent_level > 0 && this.indent_level--
                    }
            },
            this
    }
    var l, a;
    a = new l,
        a.printer(t, i, e);
    for (var o = !0;;) {
        var s = a.get_token();
        if (a.token_text = s[0], a.token_type = s[1], "TK_EOF" === a.token_type) break;
        switch (a.token_type) {
            case "TK_TAG_START":
            case "TK_TAG_SCRIPT":
            case "TK_TAG_STYLE":
                a.print_newline(!1, a.output),
                    a.print_token(a.token_text),
                    a.indent(),
                    a.current_mode = "CONTENT";
                break;
            case "TK_TAG_END":
                o && a.print_newline(!0, a.output),
                    a.print_token(a.token_text),
                    a.current_mode = "CONTENT",
                    o = !0;
                break;
            case "TK_TAG_SINGLE":
                a.print_newline(!1, a.output),
                    a.print_token(a.token_text),
                    a.current_mode = "CONTENT";
                break;
            case "TK_CONTENT":
                "" !== a.token_text && (o = !1, a.print_token(a.token_text)),
                    a.current_mode = "TAG"
        }
        a.last_token = a.token_type,
            a.last_text = a.token_text
    }
    return a.output.join("")
}
function js_beautify(t, e, i, n) {
    function l() {
        for (; y.length && (" " === y[y.length - 1] || y[y.length - 1] === T);) y.pop()
    }
    function a(t) {
        if (t = void 0 === t || t, l(), y.length) {
            "\n" === y[y.length - 1] && t || y.push("\n");
            for (var e = 0; e < n; e++) y.push(T)
        }
    }
    function o() {
        var t = y.length ? y[y.length - 1] : " ";
        " " !== t && "\n" !== t && t !== T && y.push(" ")
    }
    function s() {
        y.push(m)
    }
    function d() {
        n++
    }
    function r() {
        n && n--
    }
    function c(t) {
        _.push(b),
            b = t
    }
    function u() {
        L = "DO_BLOCK" === b,
            b = _.pop()
    }
    function p(t, e) {
        for (var i = 0; i < e.length; i++) if (e[i] === t) return ! 0;
        return ! 1
    }
    function f() {
        var t = 0,
            e = "";
        do {
            if (A >= h.length) return ["", "TK_EOF"];
            e = h.charAt(A), A += 1, "\n" === e && (t += 1)
        } while ( p ( e , k ));
        if (t > 1) for (var i = 0; i < 2; i++) a(0 === i);
        var n = 1 === t;
        if (p(e, w)) {
            if (A < h.length) for (; p(h.charAt(A), w) && (e += h.charAt(A), (A += 1) !== h.length););
            if (A !== h.length && e.match(/^[0-9]+[Ee]$/) && "-" === h.charAt(A)) {
                A += 1;
                return e += "-" + f(A)[0],
                    [e, "TK_WORD"]
            }
            return "in" === e ? [e, "TK_OPERATOR"] : [e, "TK_WORD"]
        }
        if ("(" === e || "[" === e) return [e, "TK_START_EXPR"];
        if (")" === e || "]" === e) return [e, "TK_END_EXPR"];
        if ("{" === e) return [e, "TK_START_BLOCK"];
        if ("}" === e) return [e, "TK_END_BLOCK"];
        if (";" === e) return [e, "TK_END_COMMAND"];
        if ("/" === e) {
            var l = "";
            if ("*" === h.charAt(A)) {
                if ((A += 1) < h.length) for (; ("*" !== h.charAt(A) || !h.charAt(A + 1) || "/" !== h.charAt(A + 1)) && A < h.length && (l += h.charAt(A), !((A += 1) >= h.length)););
                return A += 2,
                    ["/*" + l + "*/", "TK_BLOCK_COMMENT"]
            }
            if ("/" === h.charAt(A)) {
                for (l = e;
                     "\r" !== h.charAt(A) && "\n" !== h.charAt(A) && (l += h.charAt(A), !((A += 1) >= h.length)););
                return A += 1,
                n && a(),
                    [l, "TK_COMMENT"]
            }
        }
        if ("'" === e || '"' === e || "/" === e && ("TK_WORD" === g && "return" === v || "TK_START_EXPR" === g || "TK_END_BLOCK" === g || "TK_OPERATOR" === g || "TK_EOF" === g || "TK_END_COMMAND" === g)) {
            var o = e,
                s = !1;
            if (e = "", A < h.length) for (; (s || h.charAt(A) !== o) && (e += h.charAt(A), s = !s && "\\" === h.charAt(A), !((A += 1) >= h.length)););
            return A += 1,
            "TK_END_COMMAND" === g && a(),
                [o + e + o, "TK_STRING"]
        }
        if (p(e, E)) {
            for (; A < h.length && p(e + h.charAt(A), E) && (e += h.charAt(A), !((A += 1) >= h.length)););
            return [e, "TK_OPERATOR"]
        }
        return [e, "TK_UNKNOWN"]
    }
    var h, y, m, g, v, x, b, _, T, k, w, E, A, C, K, N, O, L, R, I;
    for (i = i || " ", e = e || 4, T = ""; e--;) T += i;
    for (h = t, x = "", g = "TK_START_EXPR", v = "", y = [], L = !1, R = !1, I = !1, k = "\n\r\t ".split(""), w = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_$".split(""), E = "+ - * / % & ++ -- = += -= *= /= %= == === != !== > < >= <= >> << >>> >>>= >>= <<= && &= | || ! !! , : ? ^ ^= |=".split(" "), C = "continue,try,throw,return,var,if,switch,case,default,for,while,break,function".split(","), b = "BLOCK", _ = [b], n = n || 0, A = 0, K = !1;;) {
        var S = f(A);
        if (m = S[0], "TK_EOF" === (O = S[1])) break;
        switch (O) {
            case "TK_START_EXPR":
                R = !1,
                    c("EXPRESSION"),
                "TK_END_EXPR" === g || "TK_START_EXPR" === g || ("TK_WORD" !== g && "TK_OPERATOR" !== g ? o() : p(x, C) && "function" !== x && o()),
                    s();
                break;
            case "TK_END_EXPR":
                s(),
                    u();
                break;
            case "TK_START_BLOCK":
                c("do" === x ? "DO_BLOCK": "BLOCK"),
                "TK_OPERATOR" !== g && "TK_START_EXPR" !== g && ("TK_START_BLOCK" === g ? a() : o()),
                    s(),
                    d();
                break;
            case "TK_END_BLOCK":
                "TK_START_BLOCK" === g ? (l(), r()) : (r(), a()),
                    s(),
                    u();
                break;
            case "TK_WORD":
                if (L) {
                    o(),
                        s(),
                        o();
                    break
                }
                if ("case" === m || "default" === m) {
                    ":" === v ?
                        function() {
                            y.length && y[y.length - 1] === T && y.pop()
                        } () : (r(), a(), d()),
                        s(),
                        K = !0;
                    break
                }
                N = "NONE",
                    "TK_END_BLOCK" === g ? p(m.toLowerCase(), ["else", "catch", "finally"]) ? (N = "SPACE", o()) : N = "NEWLINE": "TK_END_COMMAND" !== g || "BLOCK" !== b && "DO_BLOCK" !== b ? "TK_END_COMMAND" === g && "EXPRESSION" === b ? N = "SPACE": "TK_WORD" === g ? N = "SPACE": "TK_START_BLOCK" === g ? N = "NEWLINE": "TK_END_EXPR" === g && (o(), N = "NEWLINE") : N = "NEWLINE",
                    "TK_END_BLOCK" !== g && p(m.toLowerCase(), ["else", "catch", "finally"]) ? a() : p(m, C) || "NEWLINE" === N ? "else" === v ? o() : ("TK_START_EXPR" !== g && "=" !== v || "function" !== m) && ("TK_WORD" !== g || "return" !== v && "throw" !== v ? "TK_END_EXPR" !== g ? "TK_START_EXPR" === g && "var" === m || ":" === v || ("if" === m && "TK_WORD" === g && "else" === x ? o() : a()) : p(m, C) && ")" !== v && a() : o()) : "SPACE" === N && o(),
                    s(),
                    x = m,
                "var" === m && (R = !0, I = !1);
                break;
            case "TK_END_COMMAND":
                s(),
                    R = !1;
                break;
            case "TK_STRING":
                "TK_START_BLOCK" === g || "TK_END_BLOCK" === g ? a() : "TK_WORD" === g && o(),
                    s();
                break;
            case "TK_OPERATOR":
                var z = !0,
                    D = !0;
                if (R && "," !== m && (I = !0, ":" === m && (R = !1)), ":" === m && K) {
                    s(),
                        a();
                    break
                }
                if (K = !1, "," === m) {
                    R ? I ? (s(), a(), I = !1) : (s(), o()) : "TK_END_BLOCK" === g ? (s(), a()) : "BLOCK" === b ? (s(), a()) : (s(), o());
                    break
                }
                "--" === m || "++" === m ? ";" === v ? (z = !0, D = !1) : (z = !1, D = !1) : "!" === m && "TK_START_EXPR" === g ? (z = !1, D = !1) : "TK_OPERATOR" === g ? (z = !1, D = !1) : "TK_END_EXPR" === g ? (z = !0, D = !0) : "." === m ? (z = !1, D = !1) : ":" === m && (z = !!v.match(/^\d+$/)),
                z && o(),
                    s(),
                D && o();
                break;
            case "TK_BLOCK_COMMENT":
                a(),
                    s(),
                    a();
                break;
            case "TK_COMMENT":
                o(),
                    s(),
                    a();
                break;
            case "TK_UNKNOWN":
                s()
        }
        g = O,
            v = m
    }
    return y.join("")
}
layui.define(["layer", "form"],
    function(t) {
        var e = layui.$,
            i = layui.layer,
            n = layui.form,
            l = (layui.hint(), layui.device()),
            a = "layui-disabled",
            o = function() {
                var t = this;
                t.index = 0,
                    t.config = {
                        tool: ["strong", "italic", "underline", "del", "|", "left", "center", "right", "|", "link", "unlink", "face", "image","video"],
                        uploadImage: {
                            url: "",
                            field: "file",
                            accept: "image",
                            acceptMime: "image/*",
                            exts: "jpg|png|gif|bmp|jpeg",
                            size: 10240,
                            done: function(t) {}
                        },
                        uploadVideo: {
                            url: "",
                            field: "file",
                            accept: "video",
                            acceptMime: "video/*",
                            exts: "mp4|flv|avi|rm|rmvb",
                            size: 409600,
                            done: function(t) {}
                        },
                        calldel: {
                            url: "",
                            done: function(t) {}
                        },
                        quote: {
                            style: [],
                            js: []
                        },
                        customTheme: {
                            video: {
                                title: [],
                                content: [],
                                preview: []
                            }
                        },
                        devmode: !1,
                        hideTool: [],
                        height: 280
                    }
            };
        o.prototype.set = function(t) {
            var i = this;
            return e.extend(!0, i.config, t),
                i
        },
            o.prototype.on = function(t, e) {
                return layui.onevent("layedit", t, e)
            },
            o.prototype.build = function(t, i) {
                i = i || {};
                var n = this,
                    a = n.config,
                    o = "layui-layedit",
                    d = e("string" == typeof t ? "#" + t: t),
                    r = "LAY_layedit_" + ++n.index,
                    c = d.next("." + o),
                    u = e.extend({},
                        a, i),
                    p = function() {
                        var t = [],
                            e = {};
                        return layui.each(u.hideTool,
                            function(t, i) {
                                e[i] = !0
                            }),
                            layui.each(u.tool,
                                function(i, n) {
                                    w[n] && !e[n] && t.push(w[n])
                                }),
                            t.join("")
                    } (),
                    f = e(['<div class="' + o + '">', '<div class="layui-unselect layui-layedit-tool">' + p + "</div>", '<div class="layui-layedit-iframe">', '<iframe id="' + r + '" name="' + r + '" textarea="' + t + '" frameborder="0"></iframe>', "</div>", "</div>"].join(""));
                return l.ie && l.ie < 8 ? d.removeClass("layui-hide").addClass("layui-show") : (c[0] && c.remove(), s.call(n, f, d[0], u), d.addClass("layui-hide").after(f), n.index)
            },
            o.prototype.getContent = function(t) {
                var e = d(t);
                if (e[0]) return r(e[0].document.body.innerHTML)
            },
            o.prototype.getText = function(t) {
                var i = d(t);
                if (i[0]) return e(i[0].document.body).text()
            },
            o.prototype.setContent = function(t, i, n) {
                var l = d(t);
                l[0] && (n ? e(l[0].document.body).append(i) : e(l[0].document.body).html(i), this.sync(t))
            },
            o.prototype.sync = function(t) {
                var i = d(t);
                if (i[0]) {
                    e("#" + i[1].attr("textarea")).val(r(i[0].document.body.innerHTML))
                }
            },
            o.prototype.getSelection = function(t) {
                var e = d(t);
                if (e[0]) {
                    var i = p(e[0].document);
                    return document.selection ? i.text: i.toString()
                }
            };
        var s = function(t, i, n) {
                var l = this,
                    a = t.find("iframe");
                a.css({
                    height: n.height
                }).on("load",
                    function() {
                        var o = a.contents(),
                            s = a.prop("contentWindow"),
                            d = o.find("head"),
                            r = e(["<style>", "*{margin: 0; padding: 0;}", "body{padding: 10px; line-height: 20px; overflow-x: hidden; word-wrap: break-word; font: 14px Helvetica Neue,Helvetica,PingFang SC,Microsoft YaHei,Tahoma,Arial,sans-serif; -webkit-box-sizing: border-box !important; -moz-box-sizing: border-box !important; box-sizing: border-box !important;}", "a{color:#01AAED; text-decoration:none;}a:hover{color:#c00}", "p{margin-bottom: 10px;}", "video{max-width:400px;}", "td{border: 1px solid #DDD;width:80px}", "table{border-collapse: collapse;}", '.anchor:after{content:"¿";background-color:yellow;color: red;font - weight: bold;}', "img{display: inline-block; border: none; vertical-align: middle;}", "pre{margin: 10px 0; padding: 10px; line-height: 20px; border: 1px solid #ddd; border-left-width: 6px; background-color: #F2F2F2; color: #333; font-family: Courier New; font-size: 12px;}", "</style>"].join("")),
                            u = o.find("body"),
                            p = function() {
                                var t = [];
                                return layui.each(n.quote.style,
                                    function(e, i) {
                                        t.push('<link href="' + i + '" rel="stylesheet"/>')
                                    }),
                                    layui.each(n.quote.js,
                                        function(e, i) {
                                            t.push('<script src="' + i + '"><\/script>')
                                        }),
                                    t.join("")
                            } ();
                        d.append(r),
                            d.append(p),
                            u.attr("contenteditable", "true").css({
                                "min-height": n.height
                            }).html(i.value || ""),
                            c.apply(l, [s, a, i, n]),
                            m.call(l, s, t, n)
                    })
            },
            d = function(t) {
                var i = e("#LAY_layedit_" + t);
                return [i.prop("contentWindow"), i]
            },
            r = function(t) {
                return 8 == l.ie && (t = t.replace(/<.+>/g,
                    function(t) {
                        return t.toLowerCase()
                    })),
                    t
            },
            c = function(t, n, a, o) {
                var s = t.document,
                    d = e(s.body);
                d.on("keydown",
                    function(t) {
                        if (13 === t.keyCode) {
                            var e = p(s),
                                n = f(e),
                                l = n.parentNode;
                            if ("pre" === l.tagName.toLowerCase()) {
                                if (t.shiftKey) return;
                                return i.msg("请暂时用shift+enter"),
                                    !1
                            }
                            "body" === l.tagName.toLowerCase() && s.execCommand("formatBlock", !1, "<p>")
                        }
                    }),
                    e(a).parents("form").on("submit",
                        function() {
                            var t = d.html();
                            8 == l.ie && (t = t.replace(/<.+>/g,
                                function(t) {
                                    return t.toLowerCase()
                                })),
                                a.value = t
                        }),
                    d.on("paste",
                        function(e) {
                            s.execCommand("formatBlock", !1, "<p>"),
                                setTimeout(function() {
                                        u.call(t, d),
                                            a.value = d.html()
                                    },
                                    100)
                        })
            },
            u = function(t) {
                var i = this;
                i.document;
                t.find("*[style]").each(function() {
                    var t = this.style.textAlign;
                    this.removeAttribute("style"),
                        e(this).css({
                            "text-align": t || ""
                        })
                }),
                    t.find("script,link").remove()
            },
            p = function(t) {
                return t.selection ? t.selection.createRange() : t.getSelection().getRangeAt(0)
            },
            f = function(t) {
                return t.endContainer || t.parentElement().childNodes[0]
            },
            h = function(t, e, n) {
                var a = (this.document, document.createElement(t));
                for (var o in e) a.setAttribute(o, e[o]);
                if (a.removeAttribute("text"), l.ie) {
                    var s = n.text || e.text;
                    if ("a" === t && !s) return;
                    s && (a.innerHTML = s),
                        i.msg("暂不支持IE浏览器"),
                        n.selectNode(this.document.body.childNodes.item(0)),
                        n.insertNode(a)
                } else {
                    var s = n.toString() || e.text;
                    if ("a" === t && !s) return;
                    s && (a.innerHTML = s),
                        n.deleteContents(),
                        n.insertNode(a)
                }
            },
            y = function(t, i) {
                var n = this.document,
                    l = "layedit-tool-active",
                    o = f(p(n)),
                    s = function(e) {
                        return t.find(".layedit-tool-" + e)
                    };
                i && i[i.hasClass(l) ? "removeClass": "addClass"](l),
                    t.find(">i").removeClass(l),
                    s("unlink").addClass(a),
                    e(o).parents().each(function() {
                        var t = this.tagName.toLowerCase(),
                            e = this.style.textAlign;
                        "p" === t && ("center" === e ? s("center").addClass(l) : "right" === e ? s("right").addClass(l) : s("left").addClass(l)),
                        "a" === t && (s("link").addClass(l), s("unlink").removeClass(a))
                    })
            },
            m = function(t, n, o) {
                var s = t.document,
                    d = e(s.body),
                    r = {
                        link: function(i) {
                            var n = f(i),
                                l = e(n).parent();
                            g.call(d, {
                                    href: l.attr("href"),
                                    target: l.attr("target"),
                                    rel: l.attr("rel"),
                                    text: l.attr("text"),
                                    dmode: o.devmode
                                },
                                function(e) {
                                    var n = l[0];
                                    "A" === n.tagName ? (n.href = e.url, n.rel = e.rel, n.text = e.text) : h.call(t, "a", {
                                            target: e.target,
                                            href: e.url,
                                            rel: e.rel,
                                            text: e.text
                                        },
                                        i)
                                })
                        },
                        unlink: function(t) {
                            s.execCommand("unlink")
                        },
                        face: function(e) {
                            b.call(this,
                                function(i) {
                                    h.call(t, "img", {
                                            src: i.src,
                                            alt: i.alt
                                        },
                                        e),
                                        setTimeout(function() {
                                                d.focus()
                                            },
                                            100)
                                })
                        },
                        image: function(n) {
                            var l = this;
                            layui.use("upload",
                                function(a) {
                                    var s = o.uploadImage || {};
                                    a.render({
                                        url: s.url,
                                        field: s.field,
                                        accept: s.accept,
                                        acceptMime: s.acceptMime,
                                        exts: s.exts,
                                        size: s.size,
                                        elem: e(l).find("input")[0],
                                        done: function(e) {
                                            0 == e.code ? (e.data = e.data || {},
                                                h.call(t, "img", {
                                                        src: e.data.src,
                                                        alt: e.data.title
                                                    },
                                                    n), s.done(e), setTimeout(function() {
                                                    d.focus()
                                                },
                                                100)) : i.msg(e.msg || "上传失败")
                                        }
                                    })
                                })
                        },
                        code: function(e) {
                            var i = o.codeConfig || {
                                hide: !1
                            };
                            k.call(d, {
                                    hide: i.hide,
                                    default:
                                    i.
                                        default
                                },
                                function(i) {
                                    h.call(t, "pre", {
                                            text: i.code,
                                            "lay-lang": i.lang
                                        },
                                        e),
                                        setTimeout(function() {
                                                d.focus()
                                            },
                                            100)
                                })
                        },
                        images: function(n) {
                            i.open({
                                type: 1,
                                id: "fly-jie-image-upload",
                                title: "图片管理",
                                shade: .05,
                                shadeClose: !0,
                                area: function() {
                                    return /mobile/i.test(navigator.userAgent) || e(window).width() <= 485 ? ["90%"] : ["485px"]
                                } (),
                                offset: function() {
                                    return /mobile/i.test(navigator.userAgent) ? "auto": "100px"
                                } (),
                                skin: "layui-layer-border",
                                content: ['<ul class="layui-form layui-form-pane" style="margin: 20px 20px 0 20px;">', '<li class="layui-form-item">', '<div class="layui-upload">', '<button type="button" class="layui-btn" id="LayEdit_InsertImages"><i class="layui-icon"></i>多图上传</button> ', '<blockquote class="layui-elem-quote layui-quote-nm" style="margin-top: 10px;min-height: 116px">', '  预览图(点击图片可删除)：<div class="layui-upload-list" id="imgsPrev"></div>', "</blockquote>", "</div>", "</li>", '<li class="layui-form-item" style="position: relative;width: 48%;display: inline-block;">', '<label class="layui-form-label" style="position: relative;z-index: 10;width: 60px;">宽度</label>', '<input type="text" required name="imgWidth" placeholder="px" style="position: absolute;width: 100%;padding-left: 70px;left: 0;top:0" value="" class="layui-input">', "</li>", '<li class="layui-form-item" style="position: relative;width: 48%;display: inline-block;margin-left: 4%;">', '<label class="layui-form-label" style="width: 60px;position: relative;z-index: 10;">高度</label>', '<input type="text" required name="imgHeight" placeholder="px" style="position: absolute;width: 100%;padding-left: 70px;left: 0;top:0" value="" class="layui-input">', "</li>", "</ul>"].join(""),
                                btn: ["确定", "取消"],
                                btnAlign: "c",
                                yes: function(e, l) {
                                    var a = "";
                                    "" != l.find('input[name="imgWidth"]').val() && (a += "width:" + l.find('input[name="imgWidth"]').val() + "px;"),
                                    "" != l.find('input[name="imgHeight"]').val() && (a += "height:" + l.find('input[name="imgHeight"]').val() + "px;"),
                                        0 === l.find("#imgsPrev").find("img").length ? i.msg("请选择要插入的图片") : (h.call(t, "p", {
                                                text: l.find("#imgsPrev").html().replace(new RegExp(/(max-width:70px;margin:2px)/g), a)
                                            },
                                            n), i.close(e))
                                },
                                success: function(t, n) {
                                    layui.use("upload",
                                        function() {
                                            var n = layui.upload,
                                                l = o.uploadImage || {},
                                                a = [];
                                            n.render({
                                                elem: "#LayEdit_InsertImages",
                                                url: l.url,
                                                field: l.field,
                                                method: l.type,
                                                accept: l.accept,
                                                acceptMime: l.acceptMime,
                                                exts: l.exts,
                                                size: l.size,
                                                multiple: !0,
                                                before: function(t) {
                                                    t.preview(function(t, i, n) { - 1 === a.indexOf(t) && e("#imgsPrev").append('<img data-index="' + t + '" src="' + n + '" alt="' + i.name + '" style="max-width:70px;margin:2px" class="layui-upload-img">')
                                                    })
                                                },
                                                allDone: function() {
                                                    for (var t = 0; t < a.length; t++) e("#imgsPrev").find('img[data-index="' + a[t] + '"]').remove()
                                                },
                                                error: function(t, e) {
                                                    a.push(t)
                                                },
                                                done: function(n, a, s) {
                                                    0 == n.code ? (n.data = n.data || {},
                                                        e("#imgsPrev img:last")[0].src = n.data.src, l.done(n)) : i.msg(n.msg || "上传失败"),
                                                        t.find(".layui-upload-img").on("click",
                                                            function() {
                                                                i.confirm("是否删除该图片?", {
                                                                        icon: 3,
                                                                        title: "提示"
                                                                    },
                                                                    function(t) {
                                                                        var n = o.calldel;
                                                                        "" != n.url ? e.post(n.url, {
                                                                                imgpath: this.src
                                                                            },
                                                                            function(t) {
                                                                                e("#imgsPrev img:last")[0].remove(),
                                                                                    n.done(t)
                                                                            }) : (i.msg("没有配置回调参数"), e("#imgsPrev img:last")[0].remove()),
                                                                            i.close(t)
                                                                    })
                                                            })
                                                }
                                            })
                                        })
                                }
                            })
                        },
                        image_alt: function(n) {
                            i.open({
                                type: 1,
                                id: "fly-jie-image-upload",
                                title: "图片管理",
                                shade: .05,
                                shadeClose: !0,
                                area: function() {
                                    return /mobile/i.test(navigator.userAgent) || e(window).width() <= 485 ? ["90%"] : ["485px"]
                                } (),
                                offset: function() {
                                    return /mobile/i.test(navigator.userAgent) ? "auto": "100px"
                                } (),
                                skin: "layui-layer-border",
                                content: ['<ul class="layui-form layui-form-pane" style="margin: 20px 20px 0 20px">', '<li class="layui-form-item" style="position: relative">', '<button type="button" class="layui-btn" id="LayEdit_InsertImage" style="width: 110px;position: relative;z-index: 10;"><i class="layui-icon"></i>上传图片</button>', '<input type="text" name="Imgsrc" placeholder="请选择文件" style="position: absolute;width: 100%;padding-left: 120px;left: 0;top:0" class="layui-input">', "</li>", '<li class="layui-form-item" style="position: relative">', '<label class="layui-form-label" style="width: 110px;position: relative;z-index: 10;">描述</label>', '<input type="text" required name="altStr" placeholder="alt属性" style="position: absolute;width: 100%;padding-left: 120px;left: 0;top:0" value="" class="layui-input">', "</li>", '<li class="layui-form-item" style="position: relative">', '<label class="layui-form-label" style="width: 110px;position: relative;z-index: 10;">宽度</label>', '<input type="text" required name="imgWidth" placeholder="px" style="position: absolute;width: 100%;padding-left: 120px;left: 0;top:0" value="" class="layui-input">', "</li>", '<li class="layui-form-item" style="position: relative">', '<label class="layui-form-label" style="width: 110px;position: relative;z-index: 10;">高度</label>', '<input type="text" required name="imgHeight" placeholder="px" style="position: absolute;width: 100%;padding-left: 120px;left: 0;top:0" value="" class="layui-input">', "</li>", "</ul>"].join(""),
                                btn: ["确定", "取消"],
                                btnAlign: "c",
                                yes: function(e, l) {
                                    var a = "",
                                        o = l.find('input[name="altStr"]'),
                                        s = l.find('input[name="Imgsrc"]');
                                    "" != l.find('input[name="imgWidth"]').val() && (a += "width:" + l.find('input[name="imgWidth"]').val() + "px;"),
                                    "" != l.find('input[name="imgHeight"]').val() && (a += "height:" + l.find('input[name="imgHeight"]').val() + "px;"),
                                        "" == s.val() ? i.msg("请选择一张图片或输入图片地址") : (h.call(t, "img", {
                                                src: s.val(),
                                                alt: o.val(),
                                                style: a
                                            },
                                            n), i.close(e))
                                },
                                success: function(t, e) {
                                    layui.use("upload",
                                        function(e) {
                                            var n, e = layui.upload,
                                                l = t.find('input[name="altStr"]'),
                                                a = t.find('input[name="Imgsrc"]'),
                                                s = o.uploadImage || {};
                                            e.render({
                                                elem: "#LayEdit_InsertImage",
                                                url: s.url,
                                                field: s.field,
                                                accept: s.accept,
                                                acceptMime: s.acceptMime,
                                                exts: s.exts,
                                                size: s.size,
                                                before: function(t) {
                                                    n = i.msg("文件上传中,请稍等哦", {
                                                        icon: 16,
                                                        shade: .3,
                                                        time: 0
                                                    })
                                                },
                                                done: function(t, e, o) {
                                                    if (i.close(n), 0 == t.code) t.data = t.data || {},
                                                        a.val(t.data.src),
                                                        l.val(t.data.name),
                                                        s.done(t);
                                                    else if (2 == t.code) var d = i.open({
                                                        type: 1,
                                                        anim: 2,
                                                        icon: 5,
                                                        title: "提示",
                                                        area: ["390px", "260px"],
                                                        offset: "t",
                                                        content: t.msg + "<div style='text-align:center;'><img src='" + t.data.src + "' style='max-height:80px'/></div><p style='text-align:center'>确定使用该文件吗？</p>",
                                                        btn: ["确定", "取消"],
                                                        yes: function() {
                                                            t.data = t.data || {},
                                                                a.val(t.data.src),
                                                                l.val(t.data.name),
                                                                i.close(d)
                                                        }
                                                    });
                                                    else i.msg(t.msg || "上传失败")
                                                }
                                            })
                                        })
                                }
                            })
                        },
                        video: function(n) {
                            var l = o.customTheme || {
                                    video: []
                                },
                                a = "";
                            l.video.title.length > 0 && (a = AddCustomThemes(l.video.title, l.video.content, l.video.preview)),
                                i.open({
                                    type: 1,
                                    id: "fly-jie-video-upload",
                                    title: "视频管理",
                                    shade: .05,
                                    shadeClose: !0,
                                    area: function() {
                                        return /mobile/i.test(navigator.userAgent) || e(window).width() <= 485 ? ["90%"] : ["485px"]
                                    } (),
                                    offset: function() {
                                        return /mobile/i.test(navigator.userAgent) ? "auto": "100px"
                                    } (),
                                    skin: "layui-layer-border",
                                    content: ['<ul class="layui-form layui-form-pane" style="margin: 20px 20px 0 20px">', '<li class="layui-form-item" style="position: relative">', '<button type="button" class="layui-btn" id="LayEdit_InsertVideo" style="width: 110px;position: relative;z-index: 10;"> <i class="layui-icon"></i>上传视频</button>', '<input type="text" name="video" placeholder="请选择文件" style="position: absolute;width: 100%;padding-left: 120px;left: 0;top:0" class="layui-input">', "</li>", '<li class="layui-form-item" style="position: relative">', '<button type="button" class="layui-btn" id="LayEdit_InsertImage" style="width: 110px;position: relative;z-index: 10;"> <i class="layui-icon"></i>上传封面</button>', '<input type="text" name="cover" placeholder="请选择文件" style="position: absolute;width: 100%;padding-left: 120px;left: 0;top:0" class="layui-input">', "</li>", a, "</ul>"].join(""),
                                    btn: ["确定", "取消"],
                                    btnAlign: "c",
                                    yes: function(e, a) {
                                        var o = a.find('input[name="video"]'),
                                            s = a.find('input[name="cover"]'),
                                            d = a.find('select[name="theme"]');
                                        if ("" == o.val()) i.msg("请选择一个视频或输入视频地址");
                                        else {
                                            var r = '&nbsp;<video src="' + o.val() + '" poster="' + s.val() + '" controls="controls" >您的浏览器不支持video播放</video>&nbsp;';
                                            if (l.video.title.length > 0 && d.length > 0) {
                                                var c = d[0].options[d[0].selectedIndex].value;
                                                r = d[0].options[d[0].selectedIndex].text.indexOf("class_") > -1 ? '&nbsp;<video src="' + o.val() + '" poster="' + s.val() + '" class="' + c + '" controls="controls" >您的浏览器不支持video播放</video>&nbsp;': c.replace("nomarlobj", '&nbsp;<video src="' + o.val() + '" poster="' + s.val() + '" controls="controls" >您的浏览器不支持video播放</video>&nbsp;')
                                            }
                                            h.call(t, "p", {
                                                    text: r
                                                },
                                                n),
                                                i.close(e)
                                        }
                                    },
                                    success: function(t, e) {
                                        layui.use("upload",
                                            function(e) {
                                                var n, a = t.find('input[name="video"]'),
                                                    s = t.find('input[name="cover"]'),
                                                    e = layui.upload,
                                                    d = o.uploadImage || {},
                                                    r = o.uploadVideo || {};
                                                e.render({
                                                    elem: "#LayEdit_InsertImage",
                                                    url: d.url,
                                                    field: d.field,
                                                    accept: d.accept,
                                                    acceptMime: d.acceptMime,
                                                    exts: d.exts,
                                                    size: d.size,
                                                    before: function(t) {
                                                        n = i.msg("文件上传中,请稍等哦", {
                                                            icon: 16,
                                                            shade: .3,
                                                            time: 0
                                                        })
                                                    },
                                                    done: function(t, e, l) {
                                                        if (i.close(n), 0 == t.code) t.data = t.data || {},
                                                            s.val(t.data.src),
                                                            d.done(t);
                                                        else if (2 == t.code) var a = i.open({
                                                            type: 1,
                                                            anim: 2,
                                                            icon: 5,
                                                            title: "提示",
                                                            area: ["390px", "260px"],
                                                            offset: "t",
                                                            content: t.msg + "<div><img src='" + t.data.src + "' style='max-height:100px'/></div><p style='text-align:center'>确定使用该文件吗？</p>",
                                                            btn: ["确定", "取消"],
                                                            yes: function() {
                                                                t.data = t.data || {},
                                                                    s.val(t.data.src),
                                                                    i.close(a)
                                                            }
                                                        });
                                                        else i.msg(t.msg || "上传失败")
                                                    }
                                                }),
                                                    e.render({
                                                        elem: "#LayEdit_InsertVideo",
                                                        url: r.url,
                                                        field: r.field,
                                                        accept: r.accept,
                                                        acceptMime: r.acceptMime,
                                                        exts: r.exts,
                                                        size: r.size,
                                                        before: function(t) {
                                                            n = i.msg("文件上传中,请稍等哦", {
                                                                icon: 16,
                                                                shade: .3,
                                                                time: 0
                                                            })
                                                        },
                                                        done: function(t, e, l) {
                                                            if (i.close(n), 0 == t.code) t.data = t.data || {},
                                                                a.val(t.data.src),
                                                                r.done(t);
                                                            else if (2 == t.code) var o = i.open({
                                                                type: 1,
                                                                anim: 2,
                                                                icon: 5,
                                                                title: "提示",
                                                                area: ["390px", "260px"],
                                                                offset: "t",
                                                                content: t.msg + "<div><video src='" + t.data.src + "' style='max-height:100px' controls='controls'/></div><p style='text-align:center'>确定使用该文件吗？</p>",
                                                                btn: ["确定", "取消"],
                                                                yes: function() {
                                                                    t.data = t.data || {},
                                                                        a.val(t.data.src),
                                                                        i.close(o)
                                                                }
                                                            });
                                                            else i.msg(t.msg || "上传失败")
                                                        }
                                                    });
                                                var c = t.find('select[name="theme"]');
                                                l.video.title.length > 0 && c.length > 0 && t.find('select[name="theme"]').on("change mouseover",
                                                    function() {
                                                        i.tips("<img src='" + c[0].options[c[0].selectedIndex].attributes["data-img"].value + "' />", this)
                                                    })
                                            })
                                    }
                                })
                        },
                        html: function(e) {
                            var n = this,
                                l = n.parentElement.nextElementSibling.firstElementChild.contentDocument.body.innerHTML;
                            l = style_html(l, 4, " ", 80),
                                i.open({
                                    type: 1,
                                    id: "knife-z-html",
                                    title: "源码模式",
                                    shade: .3,
                                    area: ["85%", "85%"],
                                    content: '<div id ="aceHtmleditor" style="width:100%;height:100%"></div>',
                                    btn: ["确定", "取消"],
                                    btnAlign: "c",
                                    yes: function(e) {
                                        var n = ace.edit("aceHtmleditor");
                                        t.document.body.innerHTML = n.getValue(),
                                            i.close(e)
                                    },
                                    success: function(t, e) {
                                        var i = ace.edit("aceHtmleditor");
                                        i.setFontSize(14),
                                            i.session.setMode("ace/mode/html"),
                                            i.setTheme("ace/theme/tomorrow"),
                                            i.setValue(l),
                                            i.setOption("wrap", "free"),
                                            i.gotoLine(0)
                                    }
                                })
                        },
                        fullScreen: function(t) {
                            null == this.parentElement.parentElement.getAttribute("style") ? (this.parentElement.parentElement.setAttribute("style", "position: fixed;top: 0;left: 0;height: 100%;width: 100%;background-color: antiquewhite;z-index: 9999;"), this.parentElement.nextElementSibling.style = "height:100%", this.parentElement.nextElementSibling.firstElementChild.style = "height:100%") : (this.parentElement.parentElement.removeAttribute("style"), this.parentElement.nextElementSibling.removeAttribute("style"), this.parentElement.nextElementSibling.firstElementChild.style = "height:" + o.height)
                        },
                        colorpicker: function(t) {
                            _.call(this,
                                function(t) {
                                    s.execCommand("forecolor", !1, t),
                                        setTimeout(function() {
                                                d.focus()
                                            },
                                            100)
                                })
                        },
                        fontBackColor: function(t) {
                            _.call(this,
                                function(t) {
                                    l.ie ? s.execCommand("backColor", !1, t) : s.execCommand("hiliteColor", !1, t),
                                        setTimeout(function() {
                                                d.focus()
                                            },
                                            100)
                                })
                        },
                        fontFomatt: function(t) {
                            var e = o.fontFomatt || {
                                    code: ["p", "h1", "h2", "h3", "h4", "div"],
                                    text: ["正文(p)", "一级标题(h1)", "二级标题(h2)", "三级标题(h3)", "四级标题(h4)", "块级元素(div)"]
                                },
                                i = {},
                                n = {},
                                l = e.code,
                                a = e.text,
                                r = function() {
                                    return layui.each(l,
                                        function(t, e) {
                                            i[t] = e
                                        }),
                                        i
                                } (),
                                c = function() {
                                    return layui.each(a,
                                        function(t, e) {
                                            n[t] = e
                                        }),
                                        n
                                } ();
                            T.call(this, {
                                    fonts: r,
                                    texts: c
                                },
                                function(t) {
                                    s.execCommand("formatBlock", !1, "<" + t + ">"),
                                        setTimeout(function() {
                                                d.focus()
                                            },
                                            100)
                                })
                        },
                        anchors: function(e) {
                            v.call(d, {},
                                function(i) {
                                    h.call(t, "a", {
                                            name: "#" + i.text,
                                            text: " ",
                                            class: "anchor"
                                        },
                                        e)
                                })
                        },
                        table: function(e) {
                            x.call(this,
                                function(i) {
                                    for (var n = "<tr>",
                                             l = 0; l < i.cells; l++) n += "<td></td>";
                                    n += "</tr>";
                                    for (var a = n,
                                             l = 0; l < i.rows; l++) n += a;
                                    h.call(t, "table", {
                                            text: n
                                        },
                                        e),
                                        setTimeout(function() {
                                                d.focus()
                                            },
                                            10)
                                })
                        },
                        addhr: function(e) {
                            h.call(t, "hr", {},
                                e)
                        },
                        help: function() {
                            i.open({
                                type: 2,
                                title: "帮助",
                                area: ["600px", "380px"],
                                shadeClose: !0,
                                shade: .1,
                                offset: "100px",
                                skin: "layui-layer-msg",
                                content: ["http://www.layui.com/about/layedit/help.html", "no"]
                            })
                        }
                    },
                    c = n.find(".layui-layedit-tool"),
                    u = function() {
                        var i = e(this),
                            n = i.attr("layedit-event"),
                            l = i.attr("lay-command");
                        if (!i.hasClass(a)) {
                            d.focus();
                            var o = p(s),
                                u = o.commonAncestorContainer;
                            l ? (/justifyLeft|justifyCenter|justifyRight/.test(l) && "BODY" === u.parentNode.tagName && s.execCommand("formatBlock", !1, "<p>"), s.execCommand(l), setTimeout(function() {
                                    d.focus()
                                },
                                10)) : r[n] && r[n].call(this, o, s),
                                y.call(t, c, i)
                        }
                    },
                    m = /image/;
                c.find(">i").on("mousedown",
                    function() {
                        var t = e(this),
                            i = t.attr("layedit-event");
                        m.test(i) || u.call(this)
                    }).on("click",
                    function() {
                        var t = e(this),
                            i = t.attr("layedit-event");
                        m.test(i) && u.call(this)
                    }),
                    d.on("click",
                        function() {
                            y.call(t, c),
                                i.close(b.index),
                                i.close(x.index),
                                i.close(_.index),
                                i.close(T.index)
                        });
                var w = null;
                d.on("contextmenu",
                    function(t) {
                        if (null != t) {
                            i.close(w);
                            var n = t.toElement,
                                l = t.toElement.parentNode;
                            switch (t.target.tagName) {
                                case "IMG":
                                    w = i.open({
                                        type: 1,
                                        id: "fly-jie-image-upload",
                                        title: "图片管理",
                                        area: function() {
                                            return /mobile/i.test(navigator.userAgent) || e(window).width() <= 485 ? ["90%"] : ["485px"]
                                        } (),
                                        offset: function() {
                                            return /mobile/i.test(navigator.userAgent) ? "auto": "100px"
                                        } (),
                                        shade: 0,
                                        closeBtn: !1,
                                        content: ['<ul class="layui-form layui-form-pane" style="margin: 20px 20px 0 20px">', '<li class="layui-form-item" style="position: relative">', '<button type="button" class="layui-btn" id="LayEdit_UpdateImage" style="width: 110px;position: relative;z-index: 10;"> <i class="layui-icon"></i>上传图片</button>', '<input type="text" name="Imgsrc" placeholder="请选择文件" style="position: absolute;width: 100%;padding-left: 120px;left: 0;top:0" value="' + t.target.src + '" class="layui-input">', "</li>", '<li class="layui-form-item" style="position: relative">', '<label class="layui-form-label" style="width: 110px;position: relative;z-index: 10;">描述</label>', '<input type="text" required name="altStr" placeholder="alt属性" style="position: absolute;width: 100%;padding-left: 120px;left: 0;top:0" value="' + t.target.alt + '" class="layui-input">', "</li>", '<li class="layui-form-item" style="position: relative">', '<label class="layui-form-label" style="width: 110px;position: relative;z-index: 10;">宽度</label>', '<input type="text" required name="imgWidth" placeholder="px" style="position: absolute;width: 100%;padding-left: 120px;left: 0;top:0" value="' + (parseInt(t.target.style.width) || "") + '" class="layui-input">', "</li>", '<li class="layui-form-item" style="position: relative">', '<label class="layui-form-label" style="width: 110px;position: relative;z-index: 10;">高度</label>', '<input type="text" required name="imgHeight" placeholder="px" style="position: absolute;width: 100%;padding-left: 120px;left: 0;top:0" value="' + (parseInt(t.target.style.height) || "") + '" class="layui-input">', "</li>", "</ul>"].join(""),
                                        btn: ["确定", "取消", '<span style="color:red">删除</span>'],
                                        btnAlign: "c",
                                        yes: function(e, n) {
                                            var l = n.find('input[name="Imgsrc"]').val(),
                                                a = n.find('input[name="imgWidth"]').val(),
                                                o = n.find('input[name="imgHeight"]').val();
                                            "" == l ? i.msg("请选择一张图片或输入图片地址") : (t.target.src = l, t.target.alt = n.find('input[name="altStr"]').val(), t.target.style.width = "" != a ? a + "px": "", t.target.style.height = "" != o ? o + "px": "", i.close(e))
                                        },
                                        btn2: function(t, e) {},
                                        btn3: function(n, l) {
                                            var a = o.calldel;
                                            "" != a.url ? e.post(a.url, {
                                                    imgpath: t.target.src
                                                },
                                                function(e) {
                                                    t.toElement.remove(),
                                                        a.done(e)
                                                }) : t.toElement.remove(),
                                                i.close(n)
                                        },
                                        success: function(t, e) {
                                            var n = o.uploadImage || {};
                                            return layui.use("upload",
                                                function(e) {
                                                    var l, a = t.find('input[name="altStr"]'),
                                                        o = t.find('input[name="Imgsrc"]');
                                                    e = layui.upload,
                                                        e.render({
                                                            elem: "#LayEdit_UpdateImage",
                                                            url: n.url,
                                                            field: n.field,
                                                            accept: n.accept,
                                                            acceptMime: n.acceptMime,
                                                            exts: n.exts,
                                                            size: n.size,
                                                            before: function(t) {
                                                                l = i.msg("文件上传中,请稍等哦", {
                                                                    icon: 16,
                                                                    shade: .3,
                                                                    time: 0
                                                                })
                                                            },
                                                            done: function(t, e, n) {
                                                                if (i.close(l), 0 == t.code) t.data = t.data || {},
                                                                    o.val(t.data.src),
                                                                    a.val(t.data.name);
                                                                else if (2 == t.code) var s = i.open({
                                                                    type: 1,
                                                                    anim: 2,
                                                                    icon: 5,
                                                                    title: "提示",
                                                                    area: ["390px", "260px"],
                                                                    offset: "t",
                                                                    content: t.msg + "<div style='text-align:center;'><img src='" + t.data.src + "' style='max-height:80px'/></div><p style='text-align:center'>确定使用该文件吗？</p>",
                                                                    btn: ["确定", "取消"],
                                                                    yes: function() {
                                                                        t.data = t.data || {},
                                                                            o.val(t.data.src),
                                                                            a.val(t.data.name),
                                                                            i.close(s)
                                                                    }
                                                                });
                                                                else i.msg(t.msg || "上传失败")
                                                            }
                                                        })
                                                }),
                                                !1
                                        }
                                    });
                                    break;
                                case "TD":
                                    w = i.open({
                                        type: 1,
                                        title: !1,
                                        shade: 0,
                                        offset: [t.clientY + "px", t.clientX + "px"],
                                        skin: "layui-box layui-util-face",
                                        content: function() {
                                            return '<ul class="layui-clear" style="width: max-content;">' + [, '<li  style="float: initial;width:100%;"><a type="button"  style="width:100%" lay-command="addnewtr"> 新增行 </a></li>', '<li  style="float: initial;width:100%;"><a type="button" style="width:100%" lay-command="deltr"> 删除行 </a></li>'].join("") + "</ul>"
                                        } (),
                                        success: function(t, n) {
                                            t.find("a").on("click",
                                                function() {
                                                    var t = e(this),
                                                        a = t.attr("lay-command");
                                                    if (a) switch (a) {
                                                        case "deltr":
                                                            l.remove();
                                                            break;
                                                        case "addnewtr":
                                                            for (var o = "<tr>",
                                                                     s = 0; s < l.children.length; s++) o += "<td></td>";
                                                            o += "</tr>",
                                                                e(l).after(o)
                                                    }
                                                    i.close(n)
                                                })
                                        }
                                    });
                                    break;
                                default:
                                    w = i.open({
                                        type: 1,
                                        title: !1,
                                        closeBtn: !1,
                                        offset: [t.clientY + "px", t.clientX + "px"],
                                        shade: 0,
                                        content: ['<ul style="width:100px">', '<li><a type="button" class="layui-btn layui-btn-primary layui-btn-sm" style="width:100%" lay-command="left"> 居左 </a></li>', '<li><a type="button" class="layui-btn layui-btn-primary layui-btn-sm" style="width:100%" lay-command="center"> 居中 </a></li>', '<li><a type="button" class="layui-btn layui-btn-primary layui-btn-sm" style="width:100%" lay-command="right"> 居右 </a></li>', '<li><a type="button" class="layui-btn layui-btn-danger layui-btn-sm"  style="width:100%"> 删除 </a></li>', "</ul>"].join(""),
                                        success: function(a, s) {
                                            var d = o.calldel;
                                            a.find(".layui-btn-primary").on("click",
                                                function() {
                                                    var t = e(this),
                                                        a = t.attr("lay-command");
                                                    a && ("VIDEO" == n.tagName ? l.style = "text-align:" + a: n.style = "text-align:" + a),
                                                        i.close(s)
                                                }),
                                                a.find(".layui-btn-danger").on("click",
                                                    function() {
                                                        "BODY" == n.tagName ? i.msg("不能再删除了") : "VIDEO" == n.tagName ? "" != d.url ? e.post(d.url, {
                                                                filepath: t.target.src,
                                                                imgpath: t.target.poster
                                                            },
                                                            function(t) {
                                                                l.remove(),
                                                                    d.done(t)
                                                            }) : l.remove() : "IMG" == n.tagName && "" != d.url ? e.post(d.url, {
                                                                para: t.target.src
                                                            },
                                                            function(t) {
                                                                n.remove(),
                                                                    d.done(res)
                                                            }) : n.remove(),
                                                            i.close(s)
                                                    })
                                        }
                                    })
                            }
                        }
                        return ! 1
                    })
            },
            g = function t(l, a) {
                var o = l.dmode,
                    s = this,
                    d = i.open({
                        type: 1,
                        id: "LAY_layedit_link",
                        area: function() {
                            return /mobile/i.test(navigator.userAgent) || e(window).width() <= 460 ? ["90%"] : ["460px"]
                        } (),
                        offset: function() {
                            return /mobile/i.test(navigator.userAgent) ? "auto": "100px"
                        } (),
                        shade: .05,
                        shadeClose: !0,
                        moveType: 1,
                        title: "超链接",
                        skin: "layui-layer-msg",
                        content: ['<ul class="layui-form" style="margin: 15px;">', '<li class="layui-form-item">', '<label class="layui-form-label" style="width: 70px;">链接地址</label>', '<div class="layui-input-block">', '<input name="url" value="' + (l.href || "") + '" autofocus="true" autocomplete="off" class="layui-input">', "</div>", "</li>", '<li class="layui-form-item">', '<label class="layui-form-label" style="width: 70px;">链接文本</label>', '<div class="layui-input-block">', '<input name="text" value="' + (l.text || "") + '" autofocus="true" autocomplete="off" class="layui-input">', "</div>", "</li>", '<li class="layui-form-item ' + (o ? "": "layui-hide") + '">', '<label class="layui-form-label" style="width: 70px;">打开方式</label>', '<div class="layui-input-block">', '<input type="radio" name="target" value="_blank" class="layui-input" title="新窗口" ' + ("_blank" !== l.target && l.target ? "": "checked") + ">", '<input type="radio" name="target" value="_self" class="layui-input" title="当前窗口"' + ("_self" === l.target ? "checked": "") + ">", "</div>", "</li>", '<li class="layui-form-item ' + (o ? "": "layui-hide") + '">', '<label class="layui-form-label" style="width: 70px;">rel属性</label>', '<div class="layui-input-block">', '<input type="radio" name="rel" value="nofollow" class="layui-input" title="nofollow"' + ("nofollow" !== l.rel && l.target ? "": "checked") + ">", '<input type="radio" name="rel" value="" class="layui-input" title="无" ' + ("" === l.rel ? "checked": "") + ">", "</div>", "</li>", '<button type="button" lay-submit lay-filter="layedit-link-yes" id="layedit-link-yes" class="layui-btn" style="display: none;"> 确定 </button>', "</ul>"].join(""),
                        btn: ["确定", "取消"],
                        btnAlign: "c",
                        yes: function(t, i) {
                            e("#layedit-link-yes").click()
                        },
                        btn1: function(t, e) {
                            i.close(t),
                                setTimeout(function() {
                                        s.focus()
                                    },
                                    10)
                        },
                        success: function(e, l) {
                            n.render("radio"),
                                n.on("submit(layedit-link-yes)",
                                    function(e) {
                                        i.close(t.index),
                                        a && a(e.field)
                                    })
                        }
                    });
                t.index = d
            },
            v = function t(l, a) {
                var o = this,
                    s = i.open({
                        type: 1,
                        id: "LAY_layedit_addmd",
                        area: "300px",
                        offset: "100px",
                        shade: .05,
                        shadeClose: !0,
                        moveType: 1,
                        title: "添加锚点",
                        skin: "layui-layer-msg",
                        content: ['<ul class="layui-form" style="margin: 15px;">', '<li class="layui-form-item">', '<label class="layui-form-label" style="width: 60px;">名称</label>', '<div class="layui-input-block" style="margin-left: 90px">', '<input name="text" value="' + (l.name || "") + '" autofocus="true" autocomplete="off" class="layui-input">', "</div>", "</li>", '<button type="button" lay-submit lay-filter="layedit-link-yes" id="layedit-link-yes" class="layui-btn" style="display: none;"> 确定 </button>', "</ul>"].join(""),
                        btn: ["确定", "取消"],
                        btnAlign: "c",
                        yes: function(t, i) {
                            e("#layedit-link-yes").click()
                        },
                        success: function(e, l) {
                            n.render("radio"),
                                e.find(".layui-btn-primary").on("click",
                                    function() {
                                        i.close(l),
                                            setTimeout(function() {
                                                    o.focus()
                                                },
                                                10)
                                    }),
                                n.on("submit(layedit-link-yes)",
                                    function(e) {
                                        i.close(t.index),
                                        a && a(e.field)
                                    })
                        }
                    });
                t.index = s
            },
            x = function t(n) {
                return /mobile/i.test(navigator.userAgent) ? t.index = i.open({
                    type: 1,
                    title: !1,
                    closeBtn: 0,
                    shade: .05,
                    shadeClose: !0,
                    content: '<div style="padding: 5px;border: 1px solid #e6e6e6;"><span id="laytable_label" class="layui-label">0列 x 0行</span><table class="layui-table" lay-size="sm"><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr></table ></div></div > ',
                    skin: "layui-box layui-util-face",
                    success: function(t, l) {
                        t.find("td").on("mouseover",
                            function() {
                                t.find("#laytable_label")[0].innerText = this.cellIndex + 1 + "列X" + (this.parentElement.rowIndex + 1) + "行",
                                    t.find("td").removeAttr("style"),
                                    e(this).attr("style", "background-color:linen;"),
                                    e(this).prevAll().attr("style", "background-color:linen;");
                                for (var i = 0; i < e(this.parentElement).prevAll().length; i++) for (var n = 0; n < e(this.parentElement).prevAll()[i].childNodes.length; n++) n <= this.cellIndex && (e(this.parentElement).prevAll()[i].children[n].style = "background-color:linen;")
                            }),
                            t.find("td").on("click",
                                function() {
                                    n && n({
                                        cells: this.cellIndex + 1,
                                        rows: this.parentElement.rowIndex
                                    }),
                                        i.close(l)
                                })
                    }
                }) : t.index = i.tips('<div style="padding: 5px;border: 1px solid #e6e6e6;"><span id="laytable_label" class="layui-label">0列 x 0行</span><table class="layui-table" lay-size="sm"><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr></table ></div></div > ', this, {
                    tips: 1,
                    time: 0,
                    skin: "layui-box layui-util-face",
                    maxWidth: 500,
                    success: function(l, a) {
                        l.find("td").on("mouseover",
                            function() {
                                l.find("#laytable_label")[0].innerText = this.cellIndex + 1 + "列X" + (this.parentElement.rowIndex + 1) + "行",
                                    l.find("td").removeAttr("style"),
                                    e(this).attr("style", "background-color:linen;"),
                                    e(this).prevAll().attr("style", "background-color:linen;");
                                for (var t = 0; t < e(this.parentElement).prevAll().length; t++) for (var i = 0; i < e(this.parentElement).prevAll()[t].childNodes.length; i++) i <= this.cellIndex && (e(this.parentElement).prevAll()[t].children[i].style = "background-color:linen;")
                            }),
                            l.find("td").on("click",
                                function() {
                                    n && n({
                                        cells: this.cellIndex + 1,
                                        rows: this.parentElement.rowIndex
                                    }),
                                        i.close(a)
                                }),
                            e(document).off("click", t.hide).on("click", t.hide)
                    }
                })
            },
            b = function t(n) {
                var l = function() {
                    var t = ["[微笑]", "[嘻嘻]", "[哈哈]", "[可爱]", "[可怜]", "[挖鼻]", "[吃惊]", "[害羞]", "[挤眼]", "[闭嘴]", "[鄙视]", "[爱你]", "[泪]", "[偷笑]", "[亲亲]", "[生病]", "[太开心]", "[白眼]", "[右哼哼]", "[左哼哼]", "[嘘]", "[衰]", "[委屈]", "[吐]", "[哈欠]", "[抱抱]", "[怒]", "[疑问]", "[馋嘴]", "[拜拜]", "[思考]", "[汗]", "[困]", "[睡]", "[钱]", "[失望]", "[酷]", "[色]", "[哼]", "[鼓掌]", "[晕]", "[悲伤]", "[抓狂]", "[黑线]", "[阴险]", "[怒骂]", "[互粉]", "[心]", "[伤心]", "[猪头]", "[熊猫]", "[兔子]", "[ok]", "[耶]", "[good]", "[NO]", "[赞]", "[来]", "[弱]", "[草泥马]", "[神马]", "[囧]", "[浮云]", "[给力]", "[围观]", "[威武]", "[奥特曼]", "[礼物]", "[钟]", "[话筒]", "[蜡烛]", "[蛋糕]"],
                        e = {};
                    return layui.each(t,
                        function(t, i) {
                            e[i] = layui.cache.dir + "images/face/" + t + ".gif"
                        }),
                        e
                } ();
                return t.hide = t.hide ||
                    function(n) {
                        "face" !== e(n.target).attr("layedit-event") && i.close(t.index)
                    },
                    /mobile/i.test(navigator.userAgent) ? t.index = i.open({
                        type: 1,
                        title: !1,
                        closeBtn: 0,
                        shade: .05,
                        shadeClose: !0,
                        content: function() {
                            var t = [];
                            return layui.each(l,
                                function(e, i) {
                                    t.push('<li title="' + e + '"><img src="' + i + '" alt="' + e + '"/></li>')
                                }),
                            '<ul class="layui-clear" style="width: 279px;">' + t.join("") + "</ul>"
                        } (),
                        skin: "layui-box layui-util-face",
                        success: function(t, e) {
                            t.find(".layui-clear>li").on("click",
                                function() {
                                    n && n({
                                        src: l[this.title],
                                        alt: this.title
                                    }),
                                        i.close(e)
                                })
                        }
                    }) : t.index = i.tips(function() {
                        var t = [];
                        return layui.each(l,
                            function(e, i) {
                                t.push('<li title="' + e + '"><img src="' + i + '" alt="' + e + '"/></li>')
                            }),
                        '<ul class="layui-clear" style="width: 279px;">' + t.join("") + "</ul>"
                    } (), this, {
                        tips: 1,
                        time: 0,
                        skin: "layui-box layui-util-face",
                        maxWidth: 500,
                        success: function(a, o) {
                            a.css({
                                marginTop: -4,
                                marginLeft: -10
                            }).find(".layui-clear>li").on("click",
                                function() {
                                    n && n({
                                        src: l[this.title],
                                        alt: this.title
                                    }),
                                        i.close(o)
                                }),
                                e(document).off("click", t.hide).on("click", t.hide)
                        }
                    })
            },
            _ = function t(n) {
                var l = function() {
                    var t = ["#fff", "#000", "#800000", "#ffb800", "#1e9fff", "#5fb878", "#ff5722", "#999999", "#01aaed", "#cc0000", "#ff8c00", "#ffd700", "#90ee90", "#00ced1", "#1e90ff", "#c71585", "#00babd", "#ff7800"],
                        e = {};
                    return layui.each(t,
                        function(t, i) {
                            e[i] = i
                        }),
                        e
                } ();
                return t.hide = t.hide ||
                    function(n) {
                        "colorpicker" == e(n.target).attr("layedit-event") || "fontBackColor" == e(n.target).attr("layedit-event") || i.close(t.index)
                    },
                    /mobile/i.test(navigator.userAgent) ? t.index = i.open({
                        type: 1,
                        title: !1,
                        closeBtn: 0,
                        shade: .05,
                        shadeClose: !0,
                        area: ["auto"],
                        content: function() {
                            var t = [];
                            return layui.each(l,
                                function(e, i) {
                                    t.push('<li title="' + i + '" style="background-color:' + i + '"><span style="background-' + i + '" alt="' + e + '"/></li>')
                                }),
                            '<ul class="layui-clear" style="width: 279px;">' + t.join("") + "</ul>"
                        } (),
                        skin: "layui-box layui-util-face",
                        success: function(t, e) {
                            t.find(".layui-clear>li").on("click",
                                function() {
                                    n && n(this.title),
                                        i.close(e)
                                })
                        }
                    }) : t.index = i.tips(function() {
                        var t = [];
                        return layui.each(l,
                            function(e, i) {
                                t.push('<li title="' + i + '" style="background-color:' + i + '"><span style="background-' + i + '" alt="' + e + '"/></li>')
                            }),
                        '<ul class="layui-clear" style="width: 279px;">' + t.join("") + "</ul>"
                    } (), this, {
                        tips: 1,
                        time: 0,
                        skin: "layui-box layui-util-face",
                        area: ["auto"],
                        success: function(l, a) {
                            l.css({
                                marginTop: -4,
                                marginLeft: -10
                            }).find(".layui-clear>li").on("click",
                                function() {
                                    n && n(this.title),
                                        i.close(a)
                                }),
                                e(document).off("click", t.hide).on("click", t.hide)
                        }
                    })
            },
            T = function t(n, l) {
                t.hide = t.hide ||
                    function(n) {
                        "fontFomatt" !== e(n.target).attr("layedit-event") && i.close(t.index)
                    },
                    t.index = i.tips(function() {
                        var t = [];
                        return layui.each(n.fonts,
                            function(e, i) {
                                t.push('<li title="' + n.fonts[e] + '" style="float: initial;width:100%;"><' + n.fonts[e] + ">" + n.texts[e] + "</" + n.fonts[e] + "></li>")
                            }),
                        '<ul class="layui-clear" style="width: max-content;">' + t.join("") + "</ul>"
                    } (), this, {
                        tips: 1,
                        time: 0,
                        skin: "layui-box layui-util-face",
                        success: function(a, o) {
                            a.css({
                                marginTop: -4,
                                marginLeft: -10
                            }).find(".layui-clear>li").on("click",
                                function() {
                                    l && l(this.title, n.fonts),
                                        i.close(o)
                                }),
                                e(document).off("click", t.hide).on("click", t.hide)
                        }
                    })
            },
            k = function t(l, a) {
                var o = ['<li class="layui-form-item objSel">', '<label class="layui-form-label">请选择语言</label>', "<style>#selectCodeLanguage ~ .layui-form-select > dl {max-height: 192px} </style>", '<div class="layui-input-block">', '<select name="lang" id="selectCodeLanguage">', '<option value="JavaScript">JavaScript</option>', '<option value="HTML">HTML</option>', '<option value="CSS">CSS</option>', '<option value="Java">Java</option>', '<option value="PHP">PHP</option>', '<option value="C#">C#</option>', '<option value="Python">Python</option>', '<option value="Ruby">Ruby</option>', '<option value="Go">Go</option>', "</select>", "</div>", "</li>"].join("");
                l.hide && (o = ['<li class="layui-form-item" style="display:none">', '<label class="layui-form-label">请选择语言</label>', '<div class="layui-input-block">', '<select name="lang">', '<option value="' + l.
                    default + '" selected="selected">', l.
                    default, "</option>", "</select>", "</div>", "</li>"].join(""));
                var s = this,
                    d = i.open({
                        type: 1,
                        id: "LAY_layedit_code",
                        area: function() {
                            return /mobile/i.test(navigator.userAgent) || e(window).width() <= 650 ? ["90%"] : ["650px"]
                        } (),
                        offset: function() {
                            return /mobile/i.test(navigator.userAgent) ? "auto": "100px"
                        } (),
                        shade: .05,
                        shadeClose: !0,
                        moveType: 1,
                        title: "插入代码",
                        skin: "layui-layer-msg",
                        content: ['<ul class="layui-form layui-form-pane" style="margin: 15px;">', o, '<li class="layui-form-item layui-form-text">', '<label class="layui-form-label">代码</label>', '<div class="layui-input-block">', '<textarea name="code" lay-verify="required" autofocus="true" class="layui-textarea" style="height: 200px;"></textarea>', "</div>", "</li>", '<button type="button" id="layedit-code-yes" lay-submit lay-filter="layedit-code-yes" class="layui-btn" style="display: none"> 确定 </button>', "</ul>"].join(""),
                        btn: ["确定", "取消"],
                        btnAlign: "c",
                        yes: function(t, i) {
                            e("#layedit-code-yes").click()
                        },
                        btn1: function(t, e) {
                            i.close(t),
                                s.focus()
                        },
                        success: function(e, o) {
                            n.render("select"),
                                n.on("submit(layedit-code-yes)",
                                    function(e) {
                                        i.close(t.index),
                                        a && a(e.field, l.hide, l.
                                            default)
                                    })
                        }
                    });
                t.index = d
            },
            w = {
                html: '<i class="layui-icon layedit-tool-html" title="HTML源代码"  layedit-event="html"">&#xe64b;</i><span class="layedit-tool-mid"></span>',
                strong: '<i class="layui-icon layedit-tool-b" title="加粗" lay-command="Bold" layedit-event="b"">&#xe62b;</i>',
                italic: '<i class="layui-icon layedit-tool-i" title="斜体" lay-command="italic" layedit-event="i"">&#xe644;</i>',
                underline: '<i class="layui-icon layedit-tool-u" title="下划线" lay-command="underline" layedit-event="u"">&#xe646;</i>',
                del: '<i class="layui-icon layedit-tool-d" title="删除线" lay-command="strikeThrough" layedit-event="d"">&#xe64f;</i>',
                "|": '<span class="layedit-tool-mid"></span>',
                left: '<i class="layui-icon layedit-tool-left" title="左对齐" lay-command="justifyLeft" layedit-event="left"">&#xe649;</i>',
                center: '<i class="layui-icon layedit-tool-center" title="居中对齐" lay-command="justifyCenter" layedit-event="center"">&#xe647;</i>',
                right: '<i class="layui-icon layedit-tool-right" title="右对齐" lay-command="justifyRight" layedit-event="right"">&#xe648;</i>',
                link: '<i class="layui-icon layedit-tool-link" title="插入链接" layedit-event="link"">&#xe64c;</i>',
                unlink: '<i class="layui-icon layedit-tool-unlink layui-disabled" title="清除链接" lay-command="unlink" layedit-event="unlink"" style="font-size:18px">&#xe64d;</i>',
                face: '<i class="layui-icon layedit-tool-face" title="表情" layedit-event="face"" style="font-size:18px">&#xe650;</i>',
                image: '<i class="layui-icon layedit-tool-image" title="图片" layedit-event="image" style="font-size:18px">&#xe64a;<input type="file" name="file"></i>',
                code: '<i class="layui-icon layedit-tool-code" title="插入代码" layedit-event="code" style="font-size:18px">&#xe64e;</i>',
                images: '<i class="layui-icon layedit-tool-images" title="多图上传" layedit-event="images" style="font-size:18px">&#xe634;</i>',
                image_alt: '<i class="layui-icon layedit-tool-image_alt" title="图片" layedit-event="image_alt" style="font-size:18px">&#xe64a;</i>',
                video: '<i class="layui-icon layedit-tool-video" title="插入视频" layedit-event="video" style="font-size:18px">&#xe6ed;</i>',
                fullScreen: '<i class="layui-icon layedit-tool-fullScreen" title="全屏" layedit-event="fullScreen"style="font-size:18px">&#xe638;</i>',
                colorpicker: '<i class="layui-icon layedit-tool-colorpicker" title="字体颜色选择" layedit-event="colorpicker" style="font-size:18px">&#xe66a;</i>',
                fontBackColor: '<i class="layui-icon layedit-tool-fontBackColor" title="字体背景色选择" layedit-event="fontBackColor" style="font-size:18px;">&#xe60f;</i>',
                fontFomatt: '<i class="layui-icon layedit-tool-fontFomatt" title="段落格式" layedit-event="fontFomatt" style="font-size:18px">&#xe639;</i>',
                fontFamily: '<i class="layui-icon layedit-tool-fontFamily" title="字体" layedit-event="fontFamily" style="font-size:18px">&#xe702;</i>',
                addhr: '<i class="layui-icon layui-icon-chart layedit-tool-addhr" title="添加水平线" layedit-event="addhr" style="font-size:18px"></i>',
                anchors: '<i class="layui-icon layedit-tool-anchors" title="添加锚点" layedit-event="anchors" style="font-size:18px">&#xe60b;</i>',
                table: '<i class="layui-icon layedit-tool-table" title="插入表格" layedit-event="table" style="font-size:18px">&#xe62d;</i>',
                help: '<i class="layui-icon layedit-tool-help" title="帮助" layedit-event="help">&#xe607;</i>'
            },
            E = new o;
        n.render(),
            t("layedit", E)
    });