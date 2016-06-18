<?php

// char from x80 to x9F
$charset_89 = array(
    chr(128), chr(129), chr(130), chr(131), chr(132), chr(133), chr(134), chr(135), chr(136), chr(137), chr(138), chr(139), chr(140), chr(141), chr(142), chr(143),
    chr(144), chr(145), chr(146), chr(147), chr(148), chr(149), chr(150), chr(151), chr(152), chr(153), chr(154), chr(155), chr(156), chr(157), chr(158), chr(159),
);

// char from xA0 to xFF
$charset_AF = array(
    chr(160), chr(161), chr(162), chr(163), chr(164), chr(165), chr(166), chr(167), chr(168), chr(169), chr(170), chr(171), chr(172), chr(173), chr(174), chr(175),
    chr(176), chr(177), chr(178), chr(179), chr(180), chr(181), chr(182), chr(183), chr(184), chr(185), chr(186), chr(187), chr(188), chr(189), chr(190), chr(191),
    chr(192), chr(193), chr(194), chr(195), chr(196), chr(197), chr(198), chr(199), chr(200), chr(201), chr(202), chr(203), chr(204), chr(205), chr(206), chr(207),
    chr(208), chr(209), chr(210), chr(211), chr(212), chr(213), chr(214), chr(215), chr(216), chr(217), chr(218), chr(219), chr(220), chr(221), chr(222), chr(223),
    chr(224), chr(225), chr(226), chr(227), chr(228), chr(229), chr(230), chr(231), chr(232), chr(233), chr(234), chr(235), chr(236), chr(237), chr(238), chr(239),
    chr(240), chr(241), chr(242), chr(243), chr(244), chr(245), chr(246), chr(247), chr(248), chr(249), chr(250), chr(251), chr(252), chr(253), chr(254), chr(255),
);

// char from x80 to xFF
$charset_8F = array_merge($charset_89, $charset_AF);


// char 128/160~255 in html entity
// char 173(&shy;) may not supported in most browser.
$charset_html = array(
    
    // =============================================
    // BELOW IS ISO-8859-* CHARSET HTML ENCODE MAP
    // =============================================
    
    // ISO-8859-1 (Western Europe)
    'ISO-8859-1' => array(
        '&nbsp;',   '&iexcl;',  '&cent;',   '&pound;',  '&curren;', '&yen;',    '&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&ordf;',   '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&macr;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&sup1;',   '&ordm;',   '&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&iquest;',
        '&Agrave;', '&Aacute;', '&Acirc;',  '&Atilde;', '&Auml;',   '&Aring;',  '&AElig;',  '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;',  '&Euml;',   '&Igrave;', '&Iacute;', '&Icirc;',  '&Iuml;',
        '&ETH;',    '&Ntilde;', '&Ograve;', '&Oacute;', '&Ocirc;',  '&Otilde;', '&Ouml;',   '&times;',  '&Oslash;', '&Ugrave;', '&Uacute;', '&Ucirc;',  '&Uuml;',   '&Yacute;', '&THORN;',  '&szlig;',
        '&agrave;', '&aacute;', '&acirc;',  '&atilde;', '&auml;',   '&aring;',  '&aelig;',  '&ccedil;', '&egrave;', '&eacute;', '&ecirc;',  '&euml;',   '&igrave;', '&iacute;', '&icirc;',  '&iuml;',
        '&eth;',    '&ntilde;', '&ograve;', '&oacute;', '&ocirc;',  '&otilde;', '&ouml;',   '&divide;', '&oslash;', '&ugrave;', '&uacute;', '&ucirc;',  '&uuml;',   '&yacute;', '&thorn;',  '&yuml;',
    ),
    
    // ISO-8859-9 (Turkish)
    // it only has 6 different chars vs iso-8859-1. They are &#286;, &#287; &#304;, &#305;, &#350;, &#351;
    'ISO-8859-9' => array(
        '&nbsp;',   '&iexcl;',  '&cent;',   '&pound;',  '&curren;', '&yen;',    '&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&ordf;',   '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&macr;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&sup1;',   '&ordm;',   '&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&iquest;',
        '&Agrave;', '&Aacute;', '&Acirc;',  '&Atilde;', '&Auml;',   '&Aring;',  '&AElig;',  '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;',  '&Euml;',   '&Igrave;', '&Iacute;', '&Icirc;',  '&Iuml;',
        '&#286;',   '&Ntilde;', '&Ograve;', '&Oacute;', '&Ocirc;',  '&Otilde;', '&Ouml;',   '&times;',  '&Oslash;', '&Ugrave;', '&Uacute;', '&Ucirc;',  '&Uuml;',   '&#304;',   '&#350;',   '&szlig;',
        '&agrave;', '&aacute;', '&acirc;',  '&atilde;', '&auml;',   '&aring;',  '&aelig;',  '&ccedil;', '&egrave;', '&eacute;', '&ecirc;',  '&euml;',   '&igrave;', '&iacute;', '&icirc;',  '&iuml;',
        '&#287;',   '&ntilde;', '&ograve;', '&oacute;', '&ocirc;',  '&otilde;', '&ouml;',   '&divide;', '&oslash;', '&ugrave;', '&uacute;', '&ucirc;',  '&uuml;',   '&#305;',   '&#351;',   '&yuml;',
    ),
    
    // ISO-8859-2 (Central Europe)
    'ISO-8859-2' => array(
        '&nbsp;',   '&#260;',   '&#728;',   '&#321;',   '&curren;', '&#317;',   '&#346;',   '&sect;',   '&uml;',    '&Scaron;', '&#350;',   '&#356;',   '&#377;',   '&shy;',    '&#381;',   '&#379;',
        '&deg;',    '&#261;',   '&#731;',   '&#322;',   '&acute;',  '&#318;',   '&#347;',   '&#711;',   '&cedil;',  '&scaron;', '&#351;',   '&#357;',   '&#378;',   '&#733;',   '&#382;',   '&#380;',
        '&#340;',   '&Aacute;', '&Acirc;',  '&#258;',   '&Auml;',   '&#313;',   '&#262;',   '&Ccedil;', '&#268;',   '&Eacute;', '&#280;',   '&Euml;',   '&#282;',   '&Iacute;', '&Icirc;',  '&#270;',
        '&#272;',   '&#323;',   '&#327;',   '&Oacute;', '&Ocirc;',  '&#336;',   '&Ouml;',   '&times;',  '&#344;',   '&#366;',   '&Uacute;', '&#368;',   '&Uuml;',   '&Yacute;', '&#354;',   '&szlig;',
        '&#341;',   '&aacute;', '&acirc;',  '&#259;',   '&auml;',   '&#314;',   '&#263;',   '&ccedil;', '&#269;',   '&eacute;', '&#281;',   '&euml;',   '&#283;',   '&iacute;', '&icirc;',  '&#271;',
        '&#273;',   '&#324;',   '&#328;',   '&oacute;', '&ocirc;',  '&#337;',   '&ouml;',   '&divide;', '&#345;',   '&#367;',   '&uacute;', '&#369;',   '&uuml;',   '&yacute;', '&#355;',   '&#729;',
    ),
    
    // ISO-8859-3 (Southern Europe)
    // it has 7 undefined chars at 165, 174, 190, 195, 208, 227, 240
    'ISO-8859-3' => array(
        '&nbsp;',   '&#294;',   '&#728;',   '&pound;',  '&curren;', chr(165),   '&#292;',   '&sect;',   '&uml;',    '&#304;',   '&#350;',   '&#286;',   '&#308;',   '&shy;',    chr(174),   '&#379;',
        '&deg;',    '&#295;',   '&sup2;',   '&sup3;',   '&acute;',  '&micro;',  '&#293;',   '&middot;', '&cedil;',  '&#305;',   '&#351;',   '&#287;',   '&#309;',   '&frac12;', chr(190),   '&#380;',
        '&Agrave;', '&Aacute;', '&Acirc;',  chr(195),   '&Auml;',   '&#266;',   '&#264;',   '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;',  '&Euml;',   '&Igrave;', '&Iacute;', '&Icirc;',  '&Iuml;',
        chr(208),   '&Ntilde;', '&Ograve;', '&Oacute;', '&Ocirc;',  '&#288;',   '&Ouml;',   '&times;',  '&#284;',   '&Ugrave;', '&Uacute;', '&Ucirc;',  '&Uuml;',   '&#364;',   '&#348;',   '&szlig;',
        '&agrave;', '&aacute;', '&acirc;',  chr(227),   '&auml;',   '&#267;',   '&#265;',   '&ccedil;', '&egrave;', '&eacute;', '&ecirc;',  '&euml;',   '&igrave;', '&iacute;', '&icirc;',  '&iuml;',
        chr(240),   '&ntilde;', '&ograve;', '&oacute;', '&ocirc;',  '&#289;',   '&ouml;',   '&divide;', '&#285;',   '&ugrave;', '&uacute;', '&ucirc;',  '&uuml;',   '&#365;',   '&#349;',   '&#729;',
    ),
    
    // ISO-8859-4 (Baltic)
    'ISO-8859-4' => array(
        '&nbsp;',   '&#260;',   '&#312;',   '&#342;',   '&curren;', '&#296;',   '&#315;',   '&sect;',   '&uml;',    '&Scaron;', '&#274;',   '&#290;',   '&#358;',   '&shy;',    '&#381;',   '&macr;',
        '&deg;',    '&#261;',   '&#731;',   '&#343;',   '&acute;',  '&#297;',   '&#316;',   '&#711;',   '&cedil;',  '&scaron;', '&#275;',   '&#291;',   '&#359;',   '&#330;',   '&#382;',   '&#331;',
        '&#256;',   '&Aacute;', '&Acirc;',  '&Atilde;', '&Auml;',   '&Aring;',  '&AElig;',  '&#302;',   '&#268;',   '&Eacute;', '&#280;',   '&Euml;',   '&#278;',   '&Iacute;', '&Icirc;',  '&#298;',
        '&#272;',   '&#325;',   '&#332;',   '&#310;',   '&Ocirc;',  '&Otilde;', '&Ouml;',   '&times;',  '&Oslash;', '&#370;',   '&Uacute;', '&Ucirc;',  '&Uuml;',   '&#360;',   '&#362;',   '&szlig;',
        '&#257;',   '&aacute;', '&acirc;',  '&atilde;', '&auml;',   '&aring;',  '&aelig;',  '&#303;',   '&#269;',   '&eacute;', '&#281;',   '&euml;',   '&#279;',   '&iacute;', '&icirc;',  '&#299;',
        '&#273;',   '&#326;',   '&#333;',   '&#311;',   '&ocirc;',  '&otilde;', '&ouml;',   '&divide;', '&oslash;', '&#371;',   '&uacute;', '&ucirc;',  '&uuml;',   '&#361;',   '&#363;',   '&#729;',
    ),
    
    // ISO-8859-5 (Cyrillic)
    'ISO-8859-5' => array(
        '&nbsp;',   '&#1025;',  '&#1026;',  '&#1027;',  '&#1028;',  '&#1029;',  '&#1030;',  '&#1031;',  '&#1032;',  '&#1033;',  '&#1034;',  '&#1035;',  '&#1036;',  '&shy;',    '&#1038;',  '&#1039;',
        '&#1040;',  '&#1041;',  '&#1042;',  '&#1043;',  '&#1044;',  '&#1045;',  '&#1046;',  '&#1047;',  '&#1048;',  '&#1049;',  '&#1050;',  '&#1051;',  '&#1052;',  '&#1053;',  '&#1054;',  '&#1055;',
        '&#1056;',  '&#1057;',  '&#1058;',  '&#1059;',  '&#1060;',  '&#1061;',  '&#1062;',  '&#1063;',  '&#1064;',  '&#1065;',  '&#1066;',  '&#1067;',  '&#1068;',  '&#1069;',  '&#1070;',  '&#1071;',
        '&#1072;',  '&#1073;',  '&#1074;',  '&#1075;',  '&#1076;',  '&#1077;',  '&#1078;',  '&#1079;',  '&#1080;',  '&#1081;',  '&#1082;',  '&#1083;',  '&#1084;',  '&#1085;',  '&#1086;',  '&#1087;',
        '&#1088;',  '&#1089;',  '&#1090;',  '&#1091;',  '&#1092;',  '&#1093;',  '&#1094;',  '&#1095;',  '&#1096;',  '&#1097;',  '&#1098;',  '&#1099;',  '&#1100;',  '&#1101;',  '&#1102;',  '&#1103;',
        '&#8470;',  '&#1105;',  '&#1106;',  '&#1107;',  '&#1108;',  '&#1109;',  '&#1110;',  '&#1111;',  '&#1112;',  '&#1113;',  '&#1114;',  '&#1115;',  '&#1116;',  '&sect;',   '&#1118;',  '&#1119;',
    ),
    
    // ISO-8859-6 (Arabic)
    'ISO-8859-6' => array(
        '&nbsp;',   chr(161),   chr(162),   chr(163),   '&curren;', chr(165),   chr(166),   chr(167),   chr(168),   chr(169),   chr(170),   chr(171),   '&#1548;',  '&shy;',    chr(174),   chr(175),
        chr(176),   chr(177),   chr(178),   chr(179),   chr(180),   chr(181),   chr(182),   chr(183),   chr(184),   chr(185),   chr(186),   '&#1563;',  chr(188),   chr(189),   chr(190),   '&#1567;',
        chr(192),   '&#1569;',  '&#1570;',  '&#1571;',  '&#1572;',  '&#1573;',  '&#1574;',  '&#1575;',  '&#1576;',  '&#1577;',  '&#1578;',  '&#1579;',  '&#1580;',  '&#1581;',  '&#1582;',  '&#1583;',
        '&#1584;',  '&#1585;',  '&#1586;',  '&#1587;',  '&#1588;',  '&#1589;',  '&#1590;',  '&#1591;',  '&#1592;',  '&#1593;',  '&#1594;',  chr(219),   chr(220),   chr(221),   chr(222),   chr(223),
        '&#1600;',  '&#1601;',  '&#1602;',  '&#1603;',  '&#1604;',  '&#1605;',  '&#1606;',  '&#1607;',  '&#1608;',  '&#1609;',  '&#1610;',  '&#1611;',  '&#1612;',  '&#1613;',  '&#1614;',  '&#1615;',
        '&#1616;',  '&#1617;',  '&#1618;',  chr(243),   chr(244),   chr(245),   chr(246),   chr(247),   chr(248),   chr(249),   chr(250),   chr(251),   chr(252),   chr(253),   chr(254),   chr(255),
    ),
    
    // ISO-8859-7 (Greek)
    'ISO-8859-7' => array(
        '&nbsp;',   '&lsquo;',  '&rsquo;',  '&pound;',  '&euro;',   '&#8367;',  '&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&#890;',   '&laquo;',  '&not;',    '&shy;',    chr(174),   '&#8213;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&#900;',   '&#901;',   '&#902;',   '&middot;', '&#904;',   '&#905;',   '&#906;',   '&raquo;',  '&#908;',   '&frac12;', '&#910;',   '&#911;',
        '&#912;',   '&Alpha;',  '&Beta;',   '&Gamma;',  '&Delta;',  '&Epsilon;','&Zeta;',   '&Eta;',    '&Theta;',  '&Iota;',   '&Kappa;',  '&Lambda;', '&Mu;',     '&Nu;',     '&Xi;',     '&Omicron;',
        '&Pi;',     '&Rho;',    chr(210),   '&Sigma;',  '&Tau;',    '&Upsilon;','&Phi;',    '&Chi;',    '&Psi;',    '&Omega;',  '&#938;',   '&#939;',   '&#940;',   '&#941;',   '&#942;',   '&#943;',
        '&#944;',   '&alpha;',  '&beta;',   '&gamma;',  '&delta;',  '&epsilon;','&zeta;',   '&eta;',    '&theta;',  '&iota;',   '&kappa;',  '&lambda;', '&mu;',     '&nu;',     '&xi;',     '&omicron;',
        '&pi;',     '&rho;',    '&sigmaf;', '&sigma;',  '&tau;',    '&upsilon;','&phi;',    '&chi;',    '&psi;',    '&omega;',  '&#970;',   '&#971;',   '&#972;',   '&#973;',   '&#974;',   chr(255),
    ),
    
    // ISO-8859-8 (Hebrew)
    'ISO-8859-8' => array(
        '&nbsp;',   chr(161),   '&cent;',   '&pound;',  '&curren;', '&yen;',    '&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&times;',  '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&macr;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&sup1;',   '&divide;', '&raquo;',  '&frac14;', '&frac12;', '&frac34;', chr(191),
        chr(192),   chr(193),   chr(194),   chr(195),   chr(196),   chr(197),   chr(198),   chr(199),   chr(200),   chr(201),   chr(202),   chr(203),   chr(204),   chr(205),   chr(206),   chr(207),
        chr(208),   chr(209),   chr(210),   chr(211),   chr(212),   chr(213),   chr(214),   chr(215),   chr(216),   chr(217),   chr(218),   chr(219),   chr(220),   chr(221),   chr(222),   '&#8215;',
        '&#1488;',  '&#1489;',  '&#1490;',  '&#1491;',  '&#1492;',  '&#1493;',  '&#1494;',  '&#1495;',  '&#1496;',  '&#1497;',  '&#1498;',  '&#1499;',  '&#1500;',  '&#1501;',  '&#1502;',  '&#1503;',
        '&#1504;',  '&#1505;',  '&#1506;',  '&#1507;',  '&#1508;',  '&#1509;',  '&#1510;',  '&#1511;',  '&#1512;',  '&#1513;',  '&#1514;',  chr(251),   chr(252),   '&lrm;',    '&rlm;',    chr(255),
    ),
    
    // ISO-8859-10 (Latin 6)
    'ISO-8859-10' => array(
        '&nbsp;',   '&#260;',   '&#274;',   '&#290;',   '&#298;',   '&#296;',   '&#310;',   '&sect;',   '&#315;',   '&#272;',   '&Scaron;', '&#358;',   '&#381;',   '&shy;',    '&#362;',   '&#330;',
        '&deg;',    '&#261;',   '&#275;',   '&#291;',   '&#299;',   '&#297;',   '&#311;',   '&middot;', '&#316;',   '&#273;',   '&scaron;', '&#359;',   '&#382;',   '&#8213;',  '&#363;',   '&#331;',
        '&#256;',   '&Aacute;', '&Acirc;',  '&Atilde;', '&Auml;',   '&Aring;',  '&AElig;',  '&#302;',   '&#268;',   '&Eacute;', '&#280;',   '&Euml;',   '&#278;',   '&Iacute;', '&Icirc;',  '&Iuml;',
        '&ETH;',    '&#325;',   '&#332;',   '&Oacute;', '&Ocirc;',  '&Otilde;', '&Ouml;',   '&#360;',   '&Oslash;', '&#370;',   '&Uacute;', '&Ucirc;',  '&Uuml;',   '&Yacute;', '&THORN;',  '&szlig;',
        '&#257;',   '&aacute;', '&acirc;',  '&atilde;', '&auml;',   '&aring;',  '&aelig;',  '&#303;',   '&#269;',   '&eacute;', '&#281;',   '&euml;',   '&#279;',   '&iacute;', '&icirc;',  '&iuml;',
        '&eth;',    '&#326;',   '&#333;',   '&oacute;', '&ocirc;',  '&otilde;', '&ouml;',   '&#361;',   '&oslash;', '&#371;',   '&uacute;', '&ucirc;',  '&uuml;',   '&yacute;', '&thorn;',  '&#312;',
    ),
    
    // ISO-8859-11 (Thai)
    'ISO-8859-11' => array(
        '&nbsp;',   '&#3585;',  '&#3586;',  '&#3587;',  '&#3588;',  '&#3589;',  '&#3590;',  '&#3591;',  '&#3592;',  '&#3593;',  '&#3594;',  '&#3595;',  '&#3596;',  '&#3597;',  '&#3598;',  '&#3599;',
        '&#3600;',  '&#3601;',  '&#3602;',  '&#3603;',  '&#3604;',  '&#3605;',  '&#3606;',  '&#3607;',  '&#3608;',  '&#3609;',  '&#3610;',  '&#3611;',  '&#3612;',  '&#3613;',  '&#3614;',  '&#3615;',
        '&#3616;',  '&#3617;',  '&#3618;',  '&#3619;',  '&#3620;',  '&#3621;',  '&#3622;',  '&#3623;',  '&#3624;',  '&#3625;',  '&#3626;',  '&#3627;',  '&#3628;',  '&#3629;',  '&#3630;',  '&#3631;',
        '&#3632;',  '&#3633;',  '&#3634;',  '&#3635;',  '&#3636;',  '&#3637;',  '&#3638;',  '&#3639;',  '&#3640;',  '&#3641;',  '&#3642;',  chr(219),   chr(220),   chr(221),   chr(222),   '&#3647;',
        '&#3648;',  '&#3649;',  '&#3650;',  '&#3651;',  '&#3652;',  '&#3653;',  '&#3654;',  '&#3655;',  '&#3656;',  '&#3657;',  '&#3658;',  '&#3659;',  '&#3660;',  '&#3661;',  '&#3662;',  '&#3663;',
        '&#3664;',  '&#3665;',  '&#3666;',  '&#3667;',  '&#3668;',  '&#3669;',  '&#3670;',  '&#3671;',  '&#3672;',  '&#3673;',  '&#3674;',  '&#3675;',   chr(252),   chr(253),   chr(254),   chr(255),
    ),
    
    // ISO-8859-13 (Latin 7)(Baltic Rim)
    'ISO-8859-13' => array(
        '&nbsp;',   '&rdquo;',  '&cent;',   '&pound;',  '&curren;', '&bdquo;',  '&brvbar;', '&sect;',   '&Oslash;', '&copy;',   '&#342;',   '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&AElig;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&ldquo;',  '&micro;',  '&para;',   '&middot;', '&oslash;', '&sup1;',   '&#343;',   '&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&aelig;',
        '&#260;',   '&#302;',   '&#256;',   '&#262;',   '&Auml;',   '&Aring;',  '&#280;',   '&#274;',   '&#268;',   '&Eacute;', '&#377;',   '&#278;',   '&#290;',   '&#310;',   '&#298;',   '&#315;',
        '&Scaron;', '&#323;',   '&#325;',   '&Oacute;', '&#332;',   '&Otilde;', '&Ouml;',   '&times;',  '&#370;',   '&#321;',   '&#346;',   '&#362;',   '&Uuml;',   '&#379;',   '&#381;',   '&szlig;',
        '&#261;',   '&#303;',   '&#257;',   '&#263;',   '&auml;',   '&aring;',  '&#281;',   '&#275;',   '&#269;',   '&eacute;', '&#378;',   '&#279;',   '&#291;',   '&#311;',   '&#299;',   '&#316;',
        '&scaron;', '&#324;',   '&#326;',   '&oacute;', '&#333;',   '&otilde;', '&ouml;',   '&divide;', '&#371;',   '&#322;',   '&#347;',   '&#363;',   '&uuml;',   '&#380;',   '&#382;',   '&rsquo;',
    ),
    
    // ISO-8859-14 (Latin 8)
    'ISO-8859-14' => array(
        '&nbsp;',   '&#7682;',  '&#7683;',  '&pound;',  '&#266;',   '&#267;',   '&#7690;',  '&sect;',   '&#7808;',  '&copy;',   '&#7810;',  '&#7691;',  '&#7922;',  '&shy;',    '&reg;',    '&Yuml;',
        '&#7710;',  '&#7711;',  '&#288;',   '&#289;',   '&#7744;',  '&#7745;',  '&para;',   '&#7766;',  '&#7809;',  '&#7767;',  '&#7811;',  '&#7776;',  '&#7923;',  '&#7812;',  '&#7813;',  '&#7777;',
        '&Agrave;', '&Aacute;', '&Acirc;',  '&Atilde;', '&Auml;',   '&Aring;',  '&AElig;',  '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;',  '&Euml;',   '&Igrave;', '&Iacute;', '&Icirc;',  '&Iuml;',
        '&#372;',   '&Ntilde;', '&Ograve;', '&Oacute;', '&Ocirc;',  '&Otilde;', '&Ouml;',   '&#7786;',  '&Oslash;', '&Ugrave;', '&Uacute;', '&Ucirc;',  '&Uuml;',   '&Yacute;', '&#374;',   '&szlig;',
        '&agrave;', '&aacute;', '&acirc;',  '&atilde;', '&auml;',   '&aring;',  '&aelig;',  '&ccedil;', '&egrave;', '&eacute;', '&ecirc;',  '&euml;',   '&igrave;', '&iacute;', '&icirc;',  '&iuml;',
        '&#373;',   '&ntilde;', '&ograve;', '&oacute;', '&ocirc;',  '&otilde;', '&ouml;',   '&#7787;',  '&oslash;', '&ugrave;', '&uacute;', '&ucirc;',  '&uuml;',   '&yacute;', '&#375;',   '&yuml;',
    ),
    
    // ISO-8859-15 (Latin 9)
    'ISO-8859-15' => array(
        '&nbsp;',   '&iexcl;',  '&cent;',   '&pound;',  '&euro;',   '&yen;',    '&Scaron;', '&sect;',   '&scaron;', '&copy;',   '&ordf;',   '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&macr;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&#381;',   '&micro;',  '&para;',   '&middot;', '&#382;',   '&sup1;',   '&ordm;',   '&raquo;',  '&OElig;',  '&oelig;',  '&Yuml;',   '&iquest;',
        '&Agrave;', '&Aacute;', '&Acirc;',  '&Atilde;', '&Auml;',   '&Aring;',  '&AElig;',  '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;',  '&Euml;',   '&Igrave;', '&Iacute;', '&Icirc;',  '&Iuml;',
        '&ETH;',    '&Ntilde;', '&Ograve;', '&Oacute;', '&Ocirc;',  '&Otilde;', '&Ouml;',   '&times;',  '&Oslash;', '&Ugrave;', '&Uacute;', '&Ucirc;',  '&Uuml;',   '&Yacute;', '&THORN;',  '&szlig;',
        '&agrave;', '&aacute;', '&acirc;',  '&atilde;', '&auml;',   '&aring;',  '&aelig;',  '&ccedil;', '&egrave;', '&eacute;', '&ecirc;',  '&euml;',   '&igrave;', '&iacute;', '&icirc;',  '&iuml;',
        '&eth;',    '&ntilde;', '&ograve;', '&oacute;', '&ocirc;',  '&otilde;', '&ouml;',   '&divide;', '&oslash;', '&ugrave;', '&uacute;', '&ucirc;',  '&uuml;',   '&yacute;', '&thorn;',  '&yuml;',
    ),
    
    // ISO-8859-16 (Central, Eastern and Southern European languages (Albanian, Croatian, Hungarian, Polish, Romanian, Serbian and Slovenian, but also French, German, Italian and Irish Gaelic)
    'ISO-8859-16' => array(
        '&nbsp;',   '&#260;',   '&#261;',   '&#321;',   '&euro;',   '&bdquo;',  '&Scaron;', '&sect;',   '&scaron;', '&copy;',   '&#536;',   '&laquo;',  '&#377;',   '&shy;',    '&#378;',   '&#379;',
        '&deg;',    '&plusmn;', '&#268;',   '&#322;',   '&#381;',   '&rdquo;',  '&para;',   '&middot;', '&#382;',   '&#269;',   '&#537;',   '&raquo;',  '&OElig;',  '&oelig;',  '&Yuml;',   '&#380;',
        '&Agrave;', '&Aacute;', '&Acirc;',  '&#258;',   '&Auml;',   '&#262;',   '&AElig;',  '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;',  '&Euml;',   '&Igrave;', '&Iacute;', '&Icirc;',  '&Iuml;',
        '&#272;',   '&#323;',   '&Ograve;', '&Oacute;', '&Ocirc;',  '&#336;',   '&Ouml;',   '&#346;',   '&#368;',   '&Ugrave;', '&Uacute;', '&Ucirc;',  '&Uuml;',   '&#280;',   '&#538;',   '&szlig;',
        '&agrave;', '&aacute;', '&acirc;',  '&#259;',   '&auml;',   '&#263;',   '&aelig;',  '&ccedil;', '&egrave;', '&eacute;', '&ecirc;',  '&euml;',   '&igrave;', '&iacute;', '&icirc;',  '&iuml;',
        '&#273;',   '&#324;',   '&ograve;', '&oacute;', '&ocirc;',  '&#337;',   '&ouml;',   '&#347;',   '&#369;',   '&ugrave;', '&uacute;', '&ucirc;',  '&uuml;',   '&#281;',   '&#539;',   '&yuml;',
    ),
    
    // =============================================
    // BELOW IS WINDOW-125* CHARSET HTML ENCODE MAP
    // =============================================
    
    // Windows-1250 (Central Europe)
    'Windows-1250' => array(
        '&euro;',   chr(129),   '&sbquo;',  chr(131),   '&bdquo;',  '&hellip;', '&dagger;', '&Dagger;', chr(136),   '&permil;', '&Scaron;', '&lsaquo;', '&#346;',   '&#356;',   '&#381;',   '&#377;',
        chr(144),   '&lsquo;',  '&rsquo;',  '&ldquo;',  '&rdquo;',  '&bull;',   '&ndash;',  '&mdash;',  chr(152),   '&trade;',  '&scaron;', '&rsaquo;', '&#347;',   '&#357;',   '&#382;',   '&#378;',
        '&nbsp;',   '&#711;',   '&#728;',   '&#321;',   '&curren;', '&#260;',   '&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&#350;',   '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&#379;',
        '&deg;',    '&plusmn;', '&#731;',   '&#322;',   '&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&#261;',   '&#351;',   '&raquo;',  '&#317;',   '&#733;',   '&#318;',   '&#380;',
        '&#340;',   '&Aacute;', '&Acirc;',  '&#258;',   '&Auml;',   '&#313;',   '&#262;',   '&Ccedil;', '&#268;',   '&Eacute;', '&#280;',   '&Euml;',   '&#282;',   '&Iacute;', '&Icirc;',  '&#270;',
        '&#272;',   '&#323;',   '&#327;',   '&Oacute;', '&Ocirc;',  '&#336;',   '&Ouml;',   '&times;',  '&#344;',   '&#366;',   '&Uacute;', '&#368;',   '&Uuml;',   '&Yacute;', '&#354;',   '&szlig;',
        '&#341;',   '&aacute;', '&acirc;',  '&#259;',   '&auml;',   '&#314;',   '&#263;',   '&ccedil;', '&#269;',   '&eacute;', '&#281;',   '&euml;',   '&#283;',   '&iacute;', '&icirc;',  '&#271;',
        '&#273;',   '&#324;',   '&#328;',   '&oacute;', '&ocirc;',  '&#337;',   '&ouml;',   '&divide;', '&#345;',   '&#367;',   '&uacute;', '&#369;',   '&uuml;',   '&yacute;', '&#355;',   '&#729;',
    ),
    
    // Windows-1251 (Cyrillic)
    'Windows-1251' => array(
        '&#1026;',  '&#1027;',  '&sbquo;',  '&#1107;',  '&bdquo;',  '&hellip;', '&dagger;', '&Dagger;', '&euro;',   '&permil;', '&#1033;',  '&lsaquo;', '&#1034;',  '&#1036;',  '&#1035;',  '&#1039;',
        '&#1106;',  '&lsquo;',  '&rsquo;',  '&ldquo;',  '&rdquo;',  '&bull;',   '&ndash;',  '&mdash;',  chr(152),   '&trade;',  '&#1113;',  '&rsaquo;', '&#1114;',  '&#1116;',  '&#1115;',  '&#1119;',
        '&nbsp;',   '&#1038;',  '&#1118;',  '&#1032;',  '&curren;', '&#1168;',  '&brvbar;', '&sect;',   '&#1025;',  '&copy;',   '&#1028;',  '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&#1031;',
        '&deg;',    '&plusmn;', '&#1030;',  '&#1110;',  '&#1169;',  '&micro;',  '&para;',   '&middot;', '&#1105;',  '&#8470;',  '&#1108;',  '&raquo;',  '&#1112;',  '&#1029;',  '&#1109;',  '&#1111;',
        '&#1040;',  '&#1041;',  '&#1042;',  '&#1043;',  '&#1044;',  '&#1045;',  '&#1046;',  '&#1047;',  '&#1048;',  '&#1049;',  '&#1050;',  '&#1051;',  '&#1052;',  '&#1053;',  '&#1054;',  '&#1055;',
        '&#1056;',  '&#1057;',  '&#1058;',  '&#1059;',  '&#1060;',  '&#1061;',  '&#1062;',  '&#1063;',  '&#1064;',  '&#1065;',  '&#1066;',  '&#1067;',  '&#1068;',  '&#1069;',  '&#1070;',  '&#1071;',
        '&#1072;',  '&#1073;',  '&#1074;',  '&#1075;',  '&#1076;',  '&#1077;',  '&#1078;',  '&#1079;',  '&#1080;',  '&#1081;',  '&#1082;',  '&#1083;',  '&#1084;',  '&#1085;',  '&#1086;',  '&#1087;',
        '&#1088;',  '&#1089;',  '&#1090;',  '&#1091;',  '&#1092;',  '&#1093;',  '&#1094;',  '&#1095;',  '&#1096;',  '&#1097;',  '&#1098;',  '&#1099;',  '&#1100;',  '&#1101;',  '&#1102;',  '&#1103;',
    ),
    
    // Windows-1252 (Western Europe)
    'Windows-1252' => array(
        '&euro;',   '&#129;',   '&sbquo;',  '&fnof;',   '&bdquo;',  '&hellip;', '&dagger;', '&Dagger;', '&circ;',   '&permil;', '&Scaron;', '&lsaquo;', '&OElig;',  '&#141;',   '&#381;',   '&#143;',
        '&#144;',   '&lsquo;',  '&rsquo;',  '&ldquo;',  '&rdquo;',  '&bull;',   '&ndash;',  '&mdash;',  '&tilde;',  '&trade;',  '&scaron;', '&rsaquo;', '&oelig;',  '&#157;',   '&#382;',   '&Yuml;',
        // below is totally the same as iso-8859-1
        '&nbsp;',   '&iexcl;',  '&cent;',   '&pound;',  '&curren;', '&yen;',    '&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&ordf;',   '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&macr;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&sup1;',   '&ordm;',   '&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&iquest;',
        '&Agrave;', '&Aacute;', '&Acirc;',  '&Atilde;', '&Auml;',   '&Aring;',  '&AElig;',  '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;',  '&Euml;',   '&Igrave;', '&Iacute;', '&Icirc;',  '&Iuml;',
        '&ETH;',    '&Ntilde;', '&Ograve;', '&Oacute;', '&Ocirc;',  '&Otilde;', '&Ouml;',   '&times;',  '&Oslash;', '&Ugrave;', '&Uacute;', '&Ucirc;',  '&Uuml;',   '&Yacute;', '&THORN;',  '&szlig;',
        '&agrave;', '&aacute;', '&acirc;',  '&atilde;', '&auml;',   '&aring;',  '&aelig;',  '&ccedil;', '&egrave;', '&eacute;', '&ecirc;',  '&euml;',   '&igrave;', '&iacute;', '&icirc;',  '&iuml;',
        '&eth;',    '&ntilde;', '&ograve;', '&oacute;', '&ocirc;',  '&otilde;', '&ouml;',   '&divide;', '&oslash;', '&ugrave;', '&uacute;', '&ucirc;',  '&uuml;',   '&yacute;', '&thorn;',  '&yuml;',
    ),
    
    // Windows-1253 (Greek)
    'Windows-1253' => array(
        '&euro;',   chr(129),   '&sbquo;',  '&fnof;',   '&bdquo;',  '&hellip;', '&dagger;', '&Dagger;', chr(136),   '&permil;', chr(138),   '&lsaquo;', chr(140),   chr(141),   chr(142),   chr(143),
        chr(144),   '&lsquo;',  '&rsquo;',  '&ldquo;',  '&rdquo;',  '&bull;',   '&ndash;',  '&mdash;',  chr(152),   '&trade;',  chr(154),   '&rsaquo;', chr(156),   chr(157),   chr(158),   chr(159),
        '&nbsp;',   '&#901;',   '&#902;',   '&pound;',  '&curren;', '&yen;',    '&brvbar;', '&sect;',   '&uml;',    '&copy;',   chr(170),   '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&#8213;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&#900;',   '&micro;',  '&para;',   '&middot;', '&#904;',   '&#905;',   '&#906;',   '&raquo;',  '&#908;',   '&frac12;', '&#910;',   '&#911;',
        '&#912;',   '&Alpha;',  '&Beta;',   '&Gamma;',  '&Delta;',  '&Epsilon;','&Zeta;',   '&Eta;',    '&Theta;',  '&Iota;',   '&Kappa;',  '&Lambda;', '&Mu;',     '&Nu;',     '&Xi;',     '&Omicron;',
        '&Pi;',     '&Rho;',    chr(210),   '&Sigma;',  '&Tau;',    '&Upsilon;','&Phi;',    '&Chi;',    '&Psi;',    '&Omega;',  '&#938;',   '&#939;',   '&#940;',   '&#941;',   '&#942;',   '&#943;',
        '&#944;',   '&alpha;',  '&beta;',   '&gamma;',  '&delta;',  '&epsilon;','&zeta;',   '&eta;',    '&theta;',  '&iota;',   '&kappa;',  '&lambda;', '&mu;',     '&nu;',     '&xi;',     '&omicron;',
        '&pi;',     '&rho;',    '&sigmaf;', '&sigma;',  '&tau;',    '&upsilon;','&phi;',    '&chi;',    '&psi;',    '&omega;',  '&#970;',   '&#971;',   '&#972;',   '&#973;',   '&#974;',   chr(255),    
    ),
    
    // Windows-1254 (Greek)
    'Windows-1254' => array(
        '&euro;',   chr(129),   '&sbquo;',  '&fnof;',   '&bdquo;',  '&hellip;', '&dagger;', '&Dagger;', '&circ;',   '&permil;', '&Scaron;', '&lsaquo;', '&OElig;',  chr(141),   chr(142),   chr(143),
        chr(144),   '&lsquo;',  '&rsquo;',  '&ldquo;',  '&rdquo;',  '&bull;',   '&ndash;',  '&mdash;',  '&tilde;',  '&trade;',  '&scaron;', '&rsaquo;', '&oelig;',  chr(157),   chr(158),   '&Yuml;',
        '&nbsp;',   '&iexcl;',  '&cent;',   '&pound;',  '&curren;', '&yen;',    '&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&ordf;',   '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&macr;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&sup1;',   '&ordm;',   '&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&iquest;',
        '&Agrave;', '&Aacute;', '&Acirc;',  '&Atilde;', '&Auml;',   '&Aring;',  '&AElig;',  '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;',  '&Euml;',   '&Igrave;', '&Iacute;', '&Icirc;',  '&Iuml;',
        '&#286;',   '&Ntilde;', '&Ograve;', '&Oacute;', '&Ocirc;',  '&Otilde;', '&Ouml;',   '&times;',  '&Oslash;', '&Ugrave;', '&Uacute;', '&Ucirc;',  '&Uuml;',   '&#304;',   '&#350;',   '&szlig;',
        '&agrave;', '&aacute;', '&acirc;',  '&atilde;', '&auml;',   '&aring;',  '&aelig;',  '&ccedil;', '&egrave;', '&eacute;', '&ecirc;',  '&euml;',   '&igrave;', '&iacute;', '&icirc;',  '&iuml;',
        '&#287;',   '&ntilde;', '&ograve;', '&oacute;', '&ocirc;',  '&otilde;', '&ouml;',   '&divide;', '&oslash;', '&ugrave;', '&uacute;', '&ucirc;',  '&uuml;',   '&#305;',   '&#351;',   '&yuml;',
    ),
    
    // Windows-1255 (Hebrew)
    'Windows-1255' => array(
        '&euro;',   chr(129),   '&sbquo;',  '&fnof;',   '&bdquo;',  '&hellip;', '&dagger;', '&Dagger;', '&circ;',   '&permil;', chr(138),   '&lsaquo;', chr(140),   chr(141),   chr(142),   chr(143),
        chr(144),   '&lsquo;',  '&rsquo;',  '&ldquo;',  '&rdquo;',  '&bull;',   '&ndash;',  '&mdash;',  '&tilde;',  '&trade;',  chr(154),   '&rsaquo;', chr(156),   chr(157),   chr(158),   chr(159),
        '&nbsp;',   '&iexcl;',  '&cent;',   '&pound;',  '&#8362;',  '&yen;',    '&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&times;',  '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&macr;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&sup1;',   '&divide;', '&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&iquest;',
        '&#1456;',  '&#1457;',  '&#1458;',  '&#1459;',  '&#1460;',  '&#1461;',  '&#1462;',  '&#1463;',  '&#1464;',  '&#1465;',  chr(202),   '&#1467;',  '&#1468;',  '&#1469;',  '&#1470;',  '&#1471;',
        '&#1472;',  '&#1473;',  '&#1474;',  '&#1475;',  '&#1520;',  '&#1521;',  '&#1522;',  '&#1523;',  '&#1524;',  chr(217),   chr(218),   chr(219),   chr(220),   chr(221),   chr(222),   chr(223),
        '&#1488;',  '&#1489;',  '&#1490;',  '&#1491;',  '&#1492;',  '&#1493;',  '&#1494;',  '&#1495;',  '&#1496;',  '&#1497;',  '&#1498;',  '&#1499;',  '&#1500;',  '&#1501;',  '&#1502;',  '&#1503;',
        '&#1504;',  '&#1505;',  '&#1506;',  '&#1507;',  '&#1508;',  '&#1509;',  '&#1510;',  '&#1511;',  '&#1512;',  '&#1513;',  '&#1514;',  chr(251),   chr(252),   '&lrm;',    '&rlm;',    chr(255),
    ),
    
    // Windows-1256 (Arabic)
    'Windows-1256' => array(
        '&euro;',   '&#1662;',  '&sbquo;',  '&fnof;',   '&bdquo;',  '&hellip;', '&dagger;', '&Dagger;', '&circ;',   '&permil;', '&#1657;',  '&lsaquo;', '&OElig;',  '&#1670;',  '&#1688;',  '&#1672;',
        '&#1711;',  '&lsquo;',  '&rsquo;',  '&ldquo;',  '&rdquo;',  '&bull;',   '&ndash;',  '&mdash;',  '&#1705;',  '&trade;',  '&#1681;',  '&rsaquo;', '&oelig;',  '&zwnj;',   '&zwj;',    '&#1722;',
        '&nbsp;',   '&#1548;',  '&cent;',   '&pound;',  '&curren;', '&yen;',    '&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&#1726;',  '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&macr;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&sup1;',   '&#1563;',  '&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&#1567;',
        '&#1729;',  '&#1569;',  '&#1570;',  '&#1571;',  '&#1572;',  '&#1573;',  '&#1574;',  '&#1575;',  '&#1576;',  '&#1577;',  '&#1578;',  '&#1579;',  '&#1580;',  '&#1581;',  '&#1582;',  '&#1583;',
        '&#1584;',  '&#1585;',  '&#1586;',  '&#1587;',  '&#1588;',  '&#1589;',  '&#1590;',  '&times;',  '&#1591;',  '&#1592;',  '&#1593;',  '&#1594;',  '&#1600;',  '&#1601;',  '&#1602;',  '&#1603;',
        '&agrave;', '&#1604;',  '&acirc;',  '&#1605;',  '&#1606;',  '&#1607;',  '&#1608;',  '&ccedil;', '&egrave;', '&eacute;', '&ecirc;',  '&euml;',   '&#1609;',  '&#1610;',  '&icirc;',  '&iuml;',
        '&#1611;',  '&#1612;',  '&#1613;',  '&#1614;',  '&ocirc;',  '&#1615;',  '&#1616;',  '&divide;', '&#1617;',  '&ugrave;', '&#1618;',  '&ucirc;',  '&uuml;',   '&lrm;',    '&rlm;',    '&#1746;',
    ),
    
    // Windows-1257 (Baltic Rim)
    'Windows-1257' => array(
        '&euro;',   chr(129),   '&sbquo;',  chr(131),   '&bdquo;',  '&hellip;', '&dagger;', '&Dagger;', chr(136),   '&permil;', chr(138),   '&lsaquo;', chr(140),   '&uml;',    '&#711;',   '&cedil;',
        chr(144),   '&lsquo;',  '&rsquo;',  '&ldquo;',  '&rdquo;',  '&bull;',   '&ndash;',  '&mdash;',  chr(152),   '&trade;',  chr(154),   '&rsaquo;', chr(156),   '&macr;',   '&#731;',   chr(159),
        '&nbsp;',   chr(161),   '&cent;',   '&pound;',  '&curren;', chr(165),   '&brvbar;', '&sect;',   '&Oslash;', '&copy;',   '&#342;',   '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&AElig;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&acute;',  '&micro;',  '&para;',   '&middot;', '&oslash;', '&sup1;',   '&#343;',   '&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&aelig;',
        '&#260;',   '&#302;',   '&#256;',   '&#262;',   '&Auml;',   '&Aring;',  '&#280;',   '&#274;',   '&#268;',   '&Eacute;', '&#377;',   '&#278;',   '&#290;',   '&#310;',   '&#298;',   '&#315;',
        '&Scaron;', '&#323;',   '&#325;',   '&Oacute;', '&#332;',   '&Otilde;', '&Ouml;',   '&times;',  '&#370;',   '&#321;',   '&#346;',   '&#362;',   '&Uuml;',   '&#379;',   '&#381;',   '&szlig;',
        '&#261;',   '&#303;',   '&#257;',   '&#263;',   '&auml;',   '&aring;',  '&#281;',   '&#275;',   '&#269;',   '&eacute;', '&#378;',   '&#279;',   '&#291;',   '&#311;',   '&#299;',   '&#316;',
        '&scaron;', '&#324;',   '&#326;',   '&oacute;', '&#333;',   '&otilde;', '&ouml;',   '&divide;', '&#371;',   '&#322;',   '&#347;',   '&#363;',   '&uuml;',   '&#380;',   '&#382;',   '&#729;',
    ),
    
    // Windows-1258 (Vietnam)
    'Windows-1258' => array(
        '&euro;',   chr(129),   '&sbquo;',  '&fnof;',   '&bdquo;',  '&hellip;', '&dagger;', '&Dagger;', '&circ;',   '&permil;', chr(138),   '&lsaquo;', '&OElig;',  chr(141),   chr(142),   chr(143),
        chr(144),   '&lsquo;',  '&rsquo;',  '&ldquo;',  '&rdquo;',  '&bull;',   '&ndash;',  '&mdash;',  '&tilde;',  '&trade;',  chr(154),   '&rsaquo;', '&oelig;',  chr(157),   chr(158),   '&Yuml;',
        '&nbsp;',   '&iexcl;',  '&cent;',   '&pound;',  '&curren;', '&yen;',    '&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&ordf;',   '&laquo;',  '&not;',    '&shy;',    '&reg;',    '&macr;',
        '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',   '&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&sup1;',   '&ordm;',   '&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&iquest;',
        '&Agrave;', '&Aacute;', '&Acirc;',  '&#258;',   '&Auml;',   '&Aring;',  '&AElig;',  '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;',  '&Euml;',   '&#768;',   '&Iacute;', '&Icirc;',  '&Iuml;',
        '&#272;',   '&Ntilde;', '&#777;',   '&Oacute;', '&Ocirc;',  '&#416;',   '&Ouml;',   '&times;',  '&Oslash;', '&Ugrave;', '&Uacute;', '&Ucirc;',  '&Uuml;',   '&#431;',   '&#771;',   '&szlig;',
        '&agrave;', '&aacute;', '&acirc;',  '&#259;',   '&auml;',   '&aring;',  '&aelig;',  '&ccedil;', '&egrave;', '&eacute;', '&ecirc;',  '&euml;',   '&#769;',   '&iacute;', '&icirc;',  '&iuml;',
        '&#273;',   '&ntilde;', '&#803;',   '&oacute;', '&ocirc;',  '&#417;',   '&ouml;',   '&divide;', '&oslash;', '&ugrave;', '&uacute;', '&ucirc;',  '&uuml;',   '&#432;',   '&#8363;',  '&yuml;',
    ),
);

$charset_entity = array(
    '&acute;'   => '&#180;',    '&ograve;'  => '&#242;',    '&real;'    => '&#8476;',   '&lang;'    => '&#9001;',   '&Mu;'      => '&#924;', 
    '&cedil;'   => '&#184;',    '&Oslash;'  => '&#216;',    '&reg;'     => '&#174;',    '&lceil;'   => '&#8968;',   '&mu;'      => '&#956;', 
    '&circ;'    => '&#710;',    '&oslash;'  => '&#248;',    '&rlm;'     => '&#8207;',   '&lfloor;'  => '&#8970;',   '&Nu;'      => '&#925;', 
    '&macr;'    => '&#175;',    '&Otilde;'  => '&#213;',    '&sect;'    => '&#167;',    '&lowast;'  => '&#8727;',   '&nu;'      => '&#957;', 
    '&middot;'  => '&#183;',    '&otilde;'  => '&#245;',    '&shy;'     => '&#173;',    '&micro;'   => '&#181;',    '&Omega;'   => '&#937;', 
    '&tilde;'   => '&#732;',    '&Ouml;'    => '&#214;',    '&sup1;'    => '&#185;',    '&nabla;'   => '&#8711;',   '&omega;'   => '&#969;', 
    '&uml;'     => '&#168;',    '&ouml;'    => '&#246;',    '&trade;'   => '&#8482;',   '&ne;'      => '&#8800;',   '&Omicron'  => '&#927;', 
    '&Aacute;'  => '&#193;',    '&Scaron;'  => '&#352;',    '&weierp;'  => '&#8472;',   '&ni;'      => '&#8715;',   '&omicron'  => '&#959;', 
    '&aacute;'  => '&#225;',    '&scaron;'  => '&#353;',    '&bdquo;'   => '&#8222;',   '&notin;'   => '&#8713;',   '&Phi;'     => '&#934;', 
    '&Acirc;'   => '&#194;',    '&szlig;'   => '&#223;',    '&laquo;'   => '&#171;',    '&nsub;'    => '&#8836;',   '&phi;'     => '&#966;', 
    '&acirc;'   => '&#226;',    '&THORN;'   => '&#222;',    '&ldquo;'   => '&#8220;',   '&oplus;'   => '&#8853;',   '&Pi;'      => '&#928;', 
    '&AElig;'   => '&#198;',    '&thorn;'   => '&#254;',    '&lsaquo;'  => '&#8249;',   '&or;'      => '&#8744;',   '&pi;'      => '&#960;', 
    '&aelig;'   => '&#230;',    '&Uacute;'  => '&#218;',    '&lsquo;'   => '&#8216;',   '&otimes;'  => '&#8855;',   '&piv;'     => '&#982;', 
    '&Agrave;'  => '&#192;',    '&uacute;'  => '&#250;',    '&raquo;'   => '&#187;',    '&part;'    => '&#8706;',   '&Psi;'     => '&#936;', 
    '&agrave;'  => '&#224;',    '&Ucirc;'   => '&#219;',    '&rdquo;'   => '&#8221;',   '&perp;'    => '&#8869;',   '&psi;'     => '&#968;', 
    '&Aring;'   => '&#197;',    '&ucirc;'   => '&#251;',    '&rsaquo;'  => '&#8250;',   '&plusmn;'  => '&#177;',    '&Rho;'     => '&#929;', 
    '&aring;'   => '&#229;',    '&Ugrave;'  => '&#217;',    '&rsquo;'   => '&#8217;',   '&prod;'    => '&#8719;',   '&rho;'     => '&#961;', 
    '&Atilde;'  => '&#195;',    '&ugrave;'  => '&#249;',    '&sbquo;'   => '&#8218;',   '&prop;'    => '&#8733;',   '&Sigma;'   => '&#931;', 
    '&atilde;'  => '&#227;',    '&Uuml;'    => '&#220;',    '&emsp;'    => '&#8195;',   '&radic;'   => '&#8730;',   '&sigma;'   => '&#963;', 
    '&Auml;'    => '&#196;',    '&uuml;'    => '&#252;',    '&ensp;'    => '&#8194;',   '&rang;'    => '&#9002;',   '&sigmaf;'  => '&#962;', 
    '&auml;'    => '&#228;',    '&Yacute;'  => '&#221;',    '&nbsp;'    => '&#160;',    '&rceil;'   => '&#8969;',   '&Tau;'     => '&#932;', 
    '&Ccedil;'  => '&#199;',    '&yacute;'  => '&#253;',    '&thinsp;'  => '&#8201;',   '&rfloor;'  => '&#8971;',   '&tau;'     => '&#964;', 
    '&ccedil;'  => '&#231;',    '&yuml;'    => '&#255;',    '&zwj;'     => '&#8205;',   '&sdot;'    => '&#8901;',   '&Theta;'   => '&#920;', 
    '&Eacute;'  => '&#201;',    '&Yuml;'    => '&#376;',    '&zwnj;'    => '&#8204;',   '&sim;'     => '&#8764;',   '&theta;'   => '&#952;', 
    '&eacute;'  => '&#233;',    '&cent;'    => '&#162;',    '&deg;'     => '&#176;',    '&sub;'     => '&#8834;',   '&thetasy'  => '&#977;', 
    '&Ecirc;'   => '&#202;',    '&curren;'  => '&#164;',    '&divide;'  => '&#247;',    '&sube;'    => '&#8838;',   '&upsih;'   => '&#978;', 
    '&ecirc;'   => '&#234;',    '&euro;'    => '&#8364;',   '&frac12;'  => '&#189;',    '&sum;'     => '&#8721;',   '&Upsilon'  => '&#933;', 
    '&Egrave;'  => '&#200;',    '&pound;'   => '&#163;',    '&frac14;'  => '&#188;',    '&sup;'     => '&#8835;',   '&upsilon'  => '&#965;', 
    '&egrave;'  => '&#232;',    '&yen;'     => '&#165;',    '&frac34;'  => '&#190;',    '&supe;'    => '&#8839;',   '&Xi;'      => '&#926;', 
    '&ETH;'     => '&#208;',    '&brvbar;'  => '&#166;',    '&ge;'      => '&#8805;',   '&there4;'  => '&#8756;',   '&xi;'      => '&#958;', 
    '&eth;'     => '&#240;',    '&bull;'    => '&#8226;',   '&le;'      => '&#8804;',   '&Alpha;'   => '&#913;',    '&Zeta;'    => '&#918;', 
    '&Euml;'    => '&#203;',    '&copy;'    => '&#169;',    '&minus;'   => '&#8722;',   '&alpha;'   => '&#945;',    '&zeta;'    => '&#950;', 
    '&euml;'    => '&#235;',    '&dagger;'  => '&#8224;',   '&sup2;'    => '&#178;',    '&Beta;'    => '&#914;',    '&crarr;'   => '&#8629;',
    '&Iacute;'  => '&#205;',    '&Dagger;'  => '&#8225;',   '&sup3;'    => '&#179;',    '&beta;'    => '&#946;',    '&darr;'    => '&#8595;',
    '&iacute;'  => '&#237;',    '&frasl;'   => '&#8260;',   '&times;'   => '&#215;',    '&Chi;'     => '&#935;',    '&dArr;'    => '&#8659;',
    '&Icirc;'   => '&#206;',    '&hellip;'  => '&#8230;',   '&alefsym'  => '&#8501;',   '&chi;'     => '&#967;',    '&harr;'    => '&#8596;',
    '&icirc;'   => '&#238;',    '&iexcl;'   => '&#161;',    '&and;'     => '&#8743;',   '&Delta;'   => '&#916;',    '&hArr;'    => '&#8660;',
    '&Igrave;'  => '&#204;',    '&image;'   => '&#8465;',   '&ang;'     => '&#8736;',   '&delta;'   => '&#948;',    '&larr;'    => '&#8592;',
    '&igrave;'  => '&#236;',    '&iquest;'  => '&#191;',    '&asymp;'   => '&#8776;',   '&Epsilon'  => '&#917;',    '&lArr;'    => '&#8656;',
    '&Iuml;'    => '&#207;',    '&lrm;'     => '&#8206;',   '&cap;'     => '&#8745;',   '&epsilon'  => '&#949;',    '&rarr;'    => '&#8594;',
    '&iuml;'    => '&#239;',    '&mdash;'   => '&#8212;',   '&cong;'    => '&#8773;',   '&Eta;'     => '&#919;',    '&rArr;'    => '&#8658;',
    '&Ntilde;'  => '&#209;',    '&ndash;'   => '&#8211;',   '&cup;'     => '&#8746;',   '&eta;'     => '&#951;',    '&uarr;'    => '&#8593;',
    '&ntilde;'  => '&#241;',    '&not;'     => '&#172;',    '&empty;'   => '&#8709;',   '&Gamma;'   => '&#915;',    '&uArr;'    => '&#8657;',
    '&Oacute;'  => '&#211;',    '&oline;'   => '&#8254;',   '&equiv;'   => '&#8801;',   '&gamma;'   => '&#947;',    '&clubs;'   => '&#9827;',
    '&oacute;'  => '&#243;',    '&ordf;'    => '&#170;',    '&exist;'   => '&#8707;',   '&Iota;'    => '&#921;',    '&diams;'   => '&#9830;',
    '&Ocirc;'   => '&#212;',    '&ordm;'    => '&#186;',    '&fnof;'    => '&#402;',    '&iota;'    => '&#953;',    '&hearts;'  => '&#9829;',
    '&ocirc;'   => '&#244;',    '&para;'    => '&#182;',    '&forall;'  => '&#8704;',   '&Kappa;'   => '&#922;',    '&spades;'  => '&#9824;',
    '&OElig;'   => '&#338;',    '&permil;'  => '&#8240;',   '&infin;'   => '&#8734;',   '&kappa;'   => '&#954;',    '&loz;'     => '&#9674;',
    '&oelig;'   => '&#339;',    '&prime;'   => '&#8242;',   '&int;'     => '&#8747;',   '&Lambda;'  => '&#923;',
    '&Ograve;'  => '&#210;',    '&Prime;'   => '&#8243;',   '&isin;'    => '&#8712;',   '&lambda;'  => '&#955;',
);