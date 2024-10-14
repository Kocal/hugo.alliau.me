/**
 * Built from https://github.com/ssddanbrown/codemirror-lang-twig
 * Added mixed language support with help of https://codemirror.net/examples/mixed-language/
 */

import {LRParser} from "@lezer/lr";
import {foldInside, foldNodeProp, indentNodeProp, LanguageSupport, LRLanguage} from "@codemirror/language";
import * as highlight from "@lezer/highlight";
import {htmlLanguage} from "@codemirror/lang-html"
import {parseMixed} from "@lezer/common";

// This file was generated by lezer-generator. You probably shouldn't edit it.
const parser = LRParser.deserialize({
    version: 14,
    states: "%^QYQPOOO!YQQO'#CaO#RQQO'#CrOOQO'#Ct'#CtQYQPOOOOQO'#C}'#C}O#]QQO'#CzO$ZQQO'#CoOOQO'#Cz'#CzOOQO'#Cu'#CuO$lQQO,58{OOQO,58{,58{O$sQQO,59^O$sQQO,59^OOQO,59^,59^OOQO-E6r-E6rO$zQQO'#CfOOQO,58|,58|OOQO'#C|'#C|O%]QSO'#C{O%hQQO,59ZOOQO-E6s-E6sOOQO1G.g1G.gO%mQQO1G.xOOQO1G.x1G.xO%tQQO,59QO%yQSO'#CvO&bQSO,59gOOQO1G.u1G.uOOQO7+$d7+$dOOQO1G.l1G.lOOQO,59b,59bOOQO-E6t-E6t",
    stateData: "&{~OmOSPOS~OSPOeQOgRO~OVUOZTO[TO]TO^WO_WO`WOaWObWOtVOuWO~ORZO~PeOVUOZTO[TO]TO^WO_WO`WObWOtVOuWO~Oa[Od^O~P!aOX`ORnXVnXZnX[nX]nX^nX_nX`nXanXbnXtnXunXdnX~OVbOZTO[TO]TOsoP~ORfO~PeOdhO~PeOVbOZTO[TO]TOWoP~OrjOsoXWoX~OslO~OdmO~PeOWnO~OVbOZTO[TO]TOrjXsjXWjX~OrjOsoaWoa~Og[]a_^`bVmPZ`~",
    goto: "#brPPPPPswPPP!PPPPPPPPPwPPsP!S!Y!hPPP!n!v!|#TTROS]WPQY[]gRaUQSOR_SQYPQ]QUeY]gRg[QkcRpk]XPQY[]gQdVRi`ScV`Roj[WPQY[]gVbV`j",
    nodeNames: "⚠ BlockComment Template }} {{ InsertBlock Function Identifier ) ( FunctionParamList String Boolean Number ChainedIdentifier Comparison Operator CodeTag Math Array %} {% CodeBlock PlainText",
    maxTerm: 37,
    nodeProps: [
        ["openedBy", 3, "{{", 8, "(", 20, "{%"],
        ["closedBy", 4, "}}", 9, ")", 21, "%}"]
    ],
    skippedNodes: [0, 1],
    repeatNodeCount: 3,
    tokenData: "#Jr~R!^OX$}XY&ZYZ&ZZ]$}]^&Z^p$}pq&Zqr'Wrs(Ysu$}uv-cvw$}wx.gxy3Wyz3nz{4U{|4r|}5Y}!O5r!O!P>u!P!Q?]!Q!R:[!R![=m![!]?y!]!^$}!^!_@a!_!`@}!`!a@a!a!c$}!c!}6}!}#OAk#O#P$}#P#QBR#Q#R$}#R#S6}#S#T$}#T#UBi#U#V!'f#V#X6}#X#Y!,h#Y#Z# Y#Z#]6}#]#^#'g#^#a6}#a#b#.T#b#c#5r#c#d#8[#d#g6}#g#h#9h#h#i#AV#i#j!.Q#j#k!@V#k#l#Co#l#o6}#o#p#Ge#p#q>u#q#r#In#r;'S$};'S;=`&O<%lO$}P%STgPO#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}P%fWOs$}tu$}v#o$}#p;'S$};'S;=`&O<%l~$}~O$}~~&UP&RP;=`<%l$}P&ZOgP~&b[gPm~OX$}XY&ZYZ&ZZ]$}]^&Z^p$}pq&Zq#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~']VgPO!_$}!_!`'r!`#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~'yTgP_~O#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~(aXgPZ~Or(Yrs(|s#O(Y#O#P)d#P#o(Y#o#p){#p;'S(Y;'S;=`,n<%lO(Y~)TTgPZ~O#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~)iUgPO#o(Y#o#p){#p;'S(Y;'S;=`,t;=`<%l*|<%lO(Y~*Q^Z~Or(Yrs(|st*|tu(Yuv*|v#O(Y#O#P)d#P#o(Y#o#p*|#p;'S(Y;'S;=`,n<%l~(Y~O(Y~~&U~+RVZ~Or*|rs+hs#O*|#O#P+m#P;'S*|;'S;=`,h<%lO*|~+mOZ~~+pRO;'S*|;'S;=`+y;=`O*|~,OWZ~Or*|rs+hs#O*|#O#P+m#P;'S*|;'S;=`,h;=`<%l*|<%lO*|~,kP;=`<%l*|~,qP;=`<%l(Y~,yWZ~Or*|rs+hs#O*|#O#P+m#P;'S*|;'S;=`,h;=`<%l(Y<%lO*|~-jVgPb~O#o$}#o#p%c#p#q$}#q#r.P#r;'S$};'S;=`&O<%lO$}R.WTdQgPO#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~.nXgPZ~Ow.gwx(|x#O.g#O#P/Z#P#o.g#o#p/r#p;'S.g;'S;=`2c<%lO.g~/`UgPO#o.g#o#p/r#p;'S.g;'S;=`2i;=`<%l0v<%lO.g~/w_Z~Os.gst0vtu.guv0vvw.gwx(|x#O.g#O#P/Z#P#o.g#o#p0v#p;'S.g;'S;=`2c<%l~.g~O.g~~&U~0{VZ~Ow0vwx+hx#O0v#O#P1b#P;'S0v;'S;=`2]<%lO0v~1eRO;'S0v;'S;=`1n;=`O0v~1sWZ~Ow0vwx+hx#O0v#O#P1b#P;'S0v;'S;=`2];=`<%l0v<%lO0v~2`P;=`<%l0v~2fP;=`<%l.g~2nWZ~Ow0vwx+hx#O0v#O#P1b#P;'S0v;'S;=`2];=`<%l.g<%lO0vR3_TXQgPO#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}V3uTWUgPO#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~4]VgPb~Oz$}z{4r{#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~4yTgPb~O#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}V5cTrSuQgPO#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~5{`gPb~V~O}$}}!O6}!O!P8T!P!Q$}!Q!R:[!R![=m![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~7U_gPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~8Y^gPO}$}}!O9U!O!Q$}!Q![9U![!c$}!c!}9U!}#R$}#R#S9U#S#T$}#T#o9U#o#p%c#p;'S$};'S;=`&O<%lO$}~9]_gP^~O}$}}!O9U!O!P8T!P!Q$}!Q![9U![!c$}!c!}9U!}#R$}#R#S9U#S#T$}#T#o9U#o#p%c#p;'S$};'S;=`&O<%lO$}~:e_gP]~V~O}$}}!O6}!O!P;d!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~;i^gPO}$}}!O9U!O!Q$}!Q![<e![!c$}!c!}9U!}#R$}#R#S9U#S#T$}#T#o9U#o#p%c#p;'S$};'S;=`&O<%lO$}~<n_gP]~^~O}$}}!O9U!O!P8T!P!Q$}!Q![<e![!c$}!c!}9U!}#R$}#R#S9U#S#T$}#T#o9U#o#p%c#p;'S$};'S;=`&O<%lO$}~=v_gP]~V~O}$}}!O6}!O!P;d!P!Q$}!Q![=m![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~>|TgP`~O#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~?dVgPb~O!P$}!P!Q4r!Q#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}R@QTuQgPO#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~@hVgP_~O!_$}!_!`'r!`#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~AUVgP`~O!_$}!_!`'r!`#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}RArTtQgPO#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}VBYTsUgPO#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~BpegPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#b6}#b#cDR#c#d6}#d#eFg#e#i6}#i#jKe#j#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~DYagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#W6}#W#XE_#X#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~Eh_gP_~V~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~FnagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#d6}#d#eGs#e#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~GzagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#`6}#`#aIP#a#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~IWagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#m6}#m#nJ]#n#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~Jf_gPa~V~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~KlagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#h6}#h#iLq#i#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~LxagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#c6}#c#dM}#d#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~NUagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#X6}#X#Y! Z#Y#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~! bagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#g6}#g#h!!g#h#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!!nagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#V6}#V#W!#s#W#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!#z`gPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#U!$|#U#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!%TagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#d6}#d#e!&Y#e#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!&aagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#X6}#X#YJ]#Y#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!'magPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#`6}#`#a!(r#a#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!(yagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#c6}#c#d!*O#d#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!*VagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#V6}#V#W!+[#W#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!+cagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#_6}#_#`J]#`#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!,oegPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#`6}#`#a!.Q#a#b6}#b#c!/^#c#l6}#l#m!Hz#m#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!.XagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#g6}#g#h!&Y#h#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!/eagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#W6}#W#X!0j#X#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!0qkgPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#U!2f#U#V!'f#V#Y6}#Y#Z!3x#Z#]6}#]#^!6b#^#a6}#a#b!7n#b#g6}#g#h!<m#h#j6}#j#k!@V#k#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!2mcgPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#d6}#d#eFg#e#i6}#i#jKe#j#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!4PagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#c6}#c#d!5U#d#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!5]agPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#f6}#f#gJ]#g#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!6iagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#Y6}#Y#ZJ]#Z#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!7u`gPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#U!8w#U#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!9OagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#V6}#V#W!:T#W#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!:[agPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#f6}#f#g!;a#g#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!;hagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#c6}#c#dJ]#d#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!<tagPV~Op$}pq!=yq}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!>OVgPO#k$}#k#l!>e#l#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~!>jVgPO#]$}#]#^!?P#^#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~!?UVgPO#h$}#h#i!?k#i#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~!?pVgPO#[$}#[#]'r#]#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}~!@^agPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#X6}#X#Y!Ac#Y#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!AjagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#f6}#f#g!Bo#g#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!BvagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#U6}#U#V!C{#V#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!DS`gPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#U!EU#U#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!E]agPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#h6}#h#i!Fb#i#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!FiagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#]6}#]#^!Gn#^#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!GuagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#a6}#a#bJ]#b#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!IRagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#h6}#h#i!JW#i#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!J_agPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#X6}#X#Y!Kd#Y#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!KkagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#b6}#b#c!Lp#c#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!LwagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#W6}#W#X!M|#X#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~!NTagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#g6}#g#hJ]#h#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~# abgPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#U#!i#U#c6}#c#d!5U#d#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#!pagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#`6}#`#a##u#a#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~##|agPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#g6}#g#h#%R#h#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#%YagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#X6}#X#Y#&_#Y#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#&h_gP[~V~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#'negPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#Y6}#Y#ZJ]#Z#b6}#b#c#)P#c#g6}#g#hE_#h#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#)YagP_~V~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#V6}#V#W#*_#W#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#*fagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#`6}#`#a#+k#a#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#+ragPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#i6}#i#j#,w#j#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#-OagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#W6}#W#X!&Y#X#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#.[`gPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#U#/^#U#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#/ecgPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#V6}#V#W!:T#W#h6}#h#i#0p#i#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#0wagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#V6}#V#W#1|#W#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#2TagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#[6}#[#]#3Y#]#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#3aagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#X6}#X#Y#4f#Y#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#4magPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#g6}#g#hE_#h#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#5yagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#c6}#c#d#7O#d#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#7VagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#h6}#h#iE_#i#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#8cagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#f6}#f#gE_#g#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#9ocgPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#X6}#X#Y#:z#Y#h6}#h#i#<W#i#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#;RagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#h6}#h#iJ]#i#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#<_`gPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#U#=a#U#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#=hagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#f6}#f#g#>m#g#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#>tagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#h6}#h#i#?y#i#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#@QagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#g6}#g#h!<m#h#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#A^agPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#f6}#f#g#Bc#g#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#BjagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#i6}#i#j#%R#j#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#CvagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#]6}#]#^#D{#^#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#ESagPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#h6}#h#i#FX#i#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#F`agPV~O}$}}!O6}!O!P8T!P!Q$}!Q![6}![!c$}!c!}6}!}#R$}#R#S6}#S#T$}#T#[6}#[#]J]#]#o6}#o#p%c#p;'S$};'S;=`&O<%lO$}~#GjZuQOs$}st#H]tu$}uv#Idv#o$}#o#p#Ii#p;'S$};'S;=`&O<%l~$}~O$}~~&U~#H`TOs#H]st#Hot;'S#H];'S;=`#I^<%lO#H]~#HrVOs#H]st#Hot#q#H]#q#r#IX#r;'S#H];'S;=`#I^<%lO#H]~#I^OP~~#IaP;=`<%l#H]P#IiOePP#InOSPR#IuVuQgPO#o$}#o#p%c#p#q$}#q#r#J[#r;'S$};'S;=`&O<%lO$}R#JcTRQgPO#o$}#o#p%c#p;'S$};'S;=`&O<%lO$}",
    tokenizers: [0, 1, 2],
    topRules: {"Template": [0, 2]},
    tokenPrec: 259
});

const mixedTwigParser = parser.configure({
    props: [
        foldNodeProp.add({Conditional: foldInside}),
        indentNodeProp.add({
            Conditional: cx => {
                let closed = /^\s*\{% endif/.test(cx.textAfter)
                return cx.lineIndent(cx.node.from) + (closed ? 0 : cx.unit)
            }
        }),
        highlight.styleTags({
            Identifier: highlight.tags.variableName,
            Boolean: highlight.tags.bool,
            String: highlight.tags.string,
            Number: highlight.tags.number,
            BlockComment: highlight.tags.blockComment,
            CodeTag: highlight.tags.keyword,
            Comparison: highlight.tags.compareOperator,
            Operator: highlight.tags.operator,
            Math: highlight.tags.arithmeticOperator,
            "Function/Identifier": highlight.tags.function(highlight.tags.definition(highlight.tags.variableName)),
            "( )": highlight.tags.paren,
            "{ }": highlight.tags.brace,
            "{{ }} {% %}": highlight.tags.meta,
        }),
    ],
    wrap: parseMixed(node => {
        return node.type.isTop ? {
            parser: htmlLanguage.parser,
            overlay: node => node.type.name === "Text" || node.type.name === "PlainText"
        } : null
    }),
    languageData: {
        commentTokens: {block: {open: "{#", close: "#}"}}
    },
})

const twigLanguage = LRLanguage.define({parser: mixedTwigParser})

function twig() {
    return new LanguageSupport(twigLanguage);
}

export {twig, twigLanguage};
