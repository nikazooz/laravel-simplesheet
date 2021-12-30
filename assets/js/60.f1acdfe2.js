(window.webpackJsonp=window.webpackJsonp||[]).push([[60],{363:function(e,s,a){"use strict";a.r(s);var t=a(49),n=Object(t.a)({},function(){var e=this,s=e.$createElement,a=e._self._c||s;return a("ContentSlotsDistributor",{attrs:{"slot-key":e.$parent.slotKey}},[a("h1",{attrs:{id:"installation"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#installation","aria-hidden":"true"}},[e._v("#")]),e._v(" Installation")]),e._v(" "),a("p"),a("div",{staticClass:"table-of-contents"},[a("ul",[a("li",[a("a",{attrs:{href:"#requirements"}},[e._v("Requirements")])]),a("li",[a("a",{attrs:{href:"#installation"}},[e._v("Installation")])])])]),a("p"),e._v(" "),a("h2",{attrs:{id:"requirements"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#requirements","aria-hidden":"true"}},[e._v("#")]),e._v(" Requirements")]),e._v(" "),a("ul",[a("li",[e._v("PHP: "),a("code",[e._v(">=7.2")])]),e._v(" "),a("li",[e._v("Laravel: "),a("code",[e._v(">=5.5")])]),e._v(" "),a("li",[e._v("Box Spout: "),a("code",[e._v("^3.2")])]),e._v(" "),a("li",[e._v("PHP extension "),a("code",[e._v("php_zip")]),e._v(" enabled")]),e._v(" "),a("li",[e._v("PHP extension "),a("code",[e._v("php_xml")]),e._v(" enabled")]),e._v(" "),a("li",[e._v("PHP extension "),a("code",[e._v("php_gd2")]),e._v(" enabled")])]),e._v(" "),a("h2",{attrs:{id:"installation-2"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#installation-2","aria-hidden":"true"}},[e._v("#")]),e._v(" Installation")]),e._v(" "),a("p",[e._v("Require this package in the "),a("code",[e._v("composer.json")]),e._v(" of your Laravel project. This will download the package and Box Spout.")]),e._v(" "),a("div",{staticClass:"language- line-numbers-mode"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[e._v("composer require nikazooz/laravel-simplesheet\n")])]),e._v(" "),a("div",{staticClass:"line-numbers-wrapper"},[a("span",{staticClass:"line-number"},[e._v("1")]),a("br")])]),a("p",[e._v("The "),a("code",[e._v("Nikazooz\\Simplesheet\\SimplesheetServiceProvider")]),e._v(" is "),a("strong",[e._v("auto-discovered")]),e._v(" and registered by default, but if you want to register it yourself:")]),e._v(" "),a("p",[e._v("Add the ServiceProvider in "),a("code",[e._v("config/app.php")])]),e._v(" "),a("div",{staticClass:"language-php line-numbers-mode"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[e._v("'providers'")]),e._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[e._v("=>")]),e._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("[")]),e._v("\n    "),a("span",{pre:!0,attrs:{class:"token comment"}},[e._v("/*\n     * Package Service Providers...\n     */")]),e._v("\n    "),a("span",{pre:!0,attrs:{class:"token class-name class-name-fully-qualified static-context"}},[e._v("Nikazooz"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("\\")]),e._v("Simplesheet"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("\\")]),e._v("SimplesheetServiceProvider")]),a("span",{pre:!0,attrs:{class:"token operator"}},[e._v("::")]),a("span",{pre:!0,attrs:{class:"token keyword"}},[e._v("class")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v(",")]),e._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("]")]),e._v("\n")])]),e._v(" "),a("div",{staticClass:"line-numbers-wrapper"},[a("span",{staticClass:"line-number"},[e._v("1")]),a("br"),a("span",{staticClass:"line-number"},[e._v("2")]),a("br"),a("span",{staticClass:"line-number"},[e._v("3")]),a("br"),a("span",{staticClass:"line-number"},[e._v("4")]),a("br"),a("span",{staticClass:"line-number"},[e._v("5")]),a("br"),a("span",{staticClass:"line-number"},[e._v("6")]),a("br")])]),a("p",[e._v("The "),a("code",[e._v("Simplesheet")]),e._v(" facade is also "),a("strong",[e._v("auto-discovered")]),e._v(", but if you want to add it manually:")]),e._v(" "),a("p",[e._v("Add the Facade in "),a("code",[e._v("config/app.php")])]),e._v(" "),a("div",{staticClass:"language-php line-numbers-mode"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[e._v("'aliases'")]),e._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[e._v("=>")]),e._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("[")]),e._v("\n    "),a("span",{pre:!0,attrs:{class:"token operator"}},[e._v("...")]),e._v("\n    "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[e._v("'Simplesheet'")]),e._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[e._v("=>")]),e._v(" "),a("span",{pre:!0,attrs:{class:"token class-name class-name-fully-qualified static-context"}},[e._v("Nikazooz"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("\\")]),e._v("Simplesheet"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("\\")]),e._v("Facades"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("\\")]),e._v("Simplesheet")]),a("span",{pre:!0,attrs:{class:"token operator"}},[e._v("::")]),a("span",{pre:!0,attrs:{class:"token keyword"}},[e._v("class")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v(",")]),e._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("]")]),e._v("\n")])]),e._v(" "),a("div",{staticClass:"line-numbers-wrapper"},[a("span",{staticClass:"line-number"},[e._v("1")]),a("br"),a("span",{staticClass:"line-number"},[e._v("2")]),a("br"),a("span",{staticClass:"line-number"},[e._v("3")]),a("br"),a("span",{staticClass:"line-number"},[e._v("4")]),a("br")])]),a("p",[e._v("To publish the config, run the vendor publish command:")]),e._v(" "),a("div",{staticClass:"language- line-numbers-mode"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[e._v('php artisan vendor:publish --provider="Nikazooz\\Simplesheet\\SimplesheetServiceProvider"\n')])]),e._v(" "),a("div",{staticClass:"line-numbers-wrapper"},[a("span",{staticClass:"line-number"},[e._v("1")]),a("br")])]),a("p",[e._v("This will create a new config file named "),a("code",[e._v("config/simplesheet.php")]),e._v(".")])])},[],!1,null,null,null);s.default=n.exports}}]);