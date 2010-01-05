<?php

require_once(HTML2PS_DIR.'error.php');

// Non-tailorable Line Breaking Classes
define('UC_LINE_BREAK_BK', 1);
define('UC_LINE_BREAK_CR', 2);
define('UC_LINE_BREAK_LF', 3);
define('UC_LINE_BREAK_CM', 4);
define('UC_LINE_BREAK_NL', 5);
define('UC_LINE_BREAK_SG', 6);
define('UC_LINE_BREAK_WJ', 7);
define('UC_LINE_BREAK_ZW', 8);
define('UC_LINE_BREAK_GL', 9);
define('UC_LINE_BREAK_SP', 10);

// Break opportunities
define('UC_LINE_BREAK_B2', 11);
define('UC_LINE_BREAK_BA', 12);
define('UC_LINE_BREAK_BB', 13);
define('UC_LINE_BREAK_HY', 14);
define('UC_LINE_BREAK_CB', 15);

// Characters Prohibiting Certain Breaks
define('UC_LINE_BREAK_CL', 16);
define('UC_LINE_BREAK_EX', 17);
define('UC_LINE_BREAK_IN', 18);
define('UC_LINE_BREAK_NS', 19);
define('UC_LINE_BREAK_OP', 20);
define('UC_LINE_BREAK_QU', 21);

// Numeric Context
define('UC_LINE_BREAK_IS', 22);
define('UC_LINE_BREAK_NU', 23);
define('UC_LINE_BREAK_PO', 24);
define('UC_LINE_BREAK_PR', 25);
define('UC_LINE_BREAK_SY', 26);

// Other Characters
define('UC_LINE_BREAK_AI', 27);
define('UC_LINE_BREAK_AL', 28);
define('UC_LINE_BREAK_H2', 29);
define('UC_LINE_BREAK_H3', 30);
define('UC_LINE_BREAK_ID', 31);
define('UC_LINE_BREAK_JL', 32);
define('UC_LINE_BREAK_JV', 33);
define('UC_LINE_BREAK_JT', 34);
define('UC_LINE_BREAK_SA', 35);
define('UC_LINE_BREAK_XX', 36);

// Break modes
define('LB_PROHIBITED', 1);
define('LB_INDIRECT', 2);
define('LB_PROHIBITED_CM', 3);
define('LB_INDIRECT_CM', 4);
define('LB_DIRECT', 5);
define('LB_EXPLICIT', 6);

$GLOBALS['_g_line_break_class_table'] = 
array(UC_LINE_BREAK_OP => array(UC_LINE_BREAK_OP => LB_PROHIBITED,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_PROHIBITED,
                                UC_LINE_BREAK_GL => LB_PROHIBITED,
                                UC_LINE_BREAK_NS => LB_PROHIBITED,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_PROHIBITED,
                                UC_LINE_BREAK_PO => LB_PROHIBITED,
                                UC_LINE_BREAK_NU => LB_PROHIBITED,
                                UC_LINE_BREAK_AL => LB_PROHIBITED,
                                UC_LINE_BREAK_ID => LB_PROHIBITED,
                                UC_LINE_BREAK_IN => LB_PROHIBITED,
                                UC_LINE_BREAK_HY => LB_PROHIBITED,
                                UC_LINE_BREAK_BA => LB_PROHIBITED,
                                UC_LINE_BREAK_BB => LB_PROHIBITED,
                                UC_LINE_BREAK_B2 => LB_PROHIBITED,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_PROHIBITED_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_PROHIBITED,
                                UC_LINE_BREAK_H3 => LB_PROHIBITED,
                                UC_LINE_BREAK_JL => LB_PROHIBITED,
                                UC_LINE_BREAK_JV => LB_PROHIBITED,
                                UC_LINE_BREAK_JT => LB_PROHIBITED),
      UC_LINE_BREAK_CL => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_PROHIBITED,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_INDIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_QU => array(UC_LINE_BREAK_OP => LB_PROHIBITED,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_INDIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_INDIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_INDIRECT,
                                UC_LINE_BREAK_B2 => LB_INDIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_INDIRECT,
                                UC_LINE_BREAK_H3 => LB_INDIRECT,
                                UC_LINE_BREAK_JL => LB_INDIRECT,
                                UC_LINE_BREAK_JV => LB_INDIRECT,
                                UC_LINE_BREAK_JT => LB_INDIRECT),
      UC_LINE_BREAK_GL => array(UC_LINE_BREAK_OP => LB_INDIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_INDIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_INDIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_INDIRECT,
                                UC_LINE_BREAK_B2 => LB_INDIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_INDIRECT,
                                UC_LINE_BREAK_H3 => LB_INDIRECT,
                                UC_LINE_BREAK_JL => LB_INDIRECT,
                                UC_LINE_BREAK_JV => LB_INDIRECT,
                                UC_LINE_BREAK_JT => LB_INDIRECT),
      UC_LINE_BREAK_NS => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_EX => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_SY => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_IS => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_PR => array(UC_LINE_BREAK_OP => LB_INDIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_INDIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_INDIRECT,
                                UC_LINE_BREAK_H3 => LB_INDIRECT,
                                UC_LINE_BREAK_JL => LB_INDIRECT,
                                UC_LINE_BREAK_JV => LB_INDIRECT,
                                UC_LINE_BREAK_JT => LB_INDIRECT),
      UC_LINE_BREAK_PO => array(UC_LINE_BREAK_OP => LB_INDIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_NU => array(UC_LINE_BREAK_OP => LB_INDIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_INDIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_AL => array(UC_LINE_BREAK_OP => LB_INDIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_ID => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_IN => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_HY => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_BA => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_BB => array(UC_LINE_BREAK_OP => LB_INDIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_INDIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_INDIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_INDIRECT,
                                UC_LINE_BREAK_B2 => LB_INDIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_INDIRECT,
                                UC_LINE_BREAK_H3 => LB_INDIRECT,
                                UC_LINE_BREAK_JL => LB_INDIRECT,
                                UC_LINE_BREAK_JV => LB_INDIRECT,
                                UC_LINE_BREAK_JT => LB_INDIRECT),
      UC_LINE_BREAK_B2 => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_PROHIBITED,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_ZW => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_DIRECT,
                                UC_LINE_BREAK_QU => LB_DIRECT,
                                UC_LINE_BREAK_GL => LB_DIRECT,
                                UC_LINE_BREAK_NS => LB_DIRECT,
                                UC_LINE_BREAK_EX => LB_DIRECT,
                                UC_LINE_BREAK_SY => LB_DIRECT,
                                UC_LINE_BREAK_IS => LB_DIRECT,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_DIRECT,
                                UC_LINE_BREAK_HY => LB_DIRECT,
                                UC_LINE_BREAK_BA => LB_DIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_DIRECT,
                                UC_LINE_BREAK_WJ => LB_DIRECT,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_CM => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_DIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_WJ => array(UC_LINE_BREAK_OP => LB_INDIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_INDIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_INDIRECT,
                                UC_LINE_BREAK_AL => LB_INDIRECT,
                                UC_LINE_BREAK_ID => LB_INDIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_INDIRECT,
                                UC_LINE_BREAK_B2 => LB_INDIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_INDIRECT,
                                UC_LINE_BREAK_H3 => LB_INDIRECT,
                                UC_LINE_BREAK_JL => LB_INDIRECT,
                                UC_LINE_BREAK_JV => LB_INDIRECT,
                                UC_LINE_BREAK_JT => LB_INDIRECT),
      UC_LINE_BREAK_H2 => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_INDIRECT,
                                UC_LINE_BREAK_JT => LB_INDIRECT),
      UC_LINE_BREAK_H3 => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_INDIRECT),
      UC_LINE_BREAK_JL => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_INDIRECT,
                                UC_LINE_BREAK_H3 => LB_INDIRECT,
                                UC_LINE_BREAK_JL => LB_INDIRECT,
                                UC_LINE_BREAK_JV => LB_INDIRECT,
                                UC_LINE_BREAK_JT => LB_DIRECT),
      UC_LINE_BREAK_JV => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_INDIRECT,
                                UC_LINE_BREAK_JT => LB_INDIRECT),
      UC_LINE_BREAK_JT => array(UC_LINE_BREAK_OP => LB_DIRECT,
                                UC_LINE_BREAK_CL => LB_PROHIBITED,
                                UC_LINE_BREAK_QU => LB_INDIRECT,
                                UC_LINE_BREAK_GL => LB_INDIRECT,
                                UC_LINE_BREAK_NS => LB_INDIRECT,
                                UC_LINE_BREAK_EX => LB_PROHIBITED,
                                UC_LINE_BREAK_SY => LB_PROHIBITED,
                                UC_LINE_BREAK_IS => LB_PROHIBITED,
                                UC_LINE_BREAK_PR => LB_DIRECT,
                                UC_LINE_BREAK_PO => LB_INDIRECT,
                                UC_LINE_BREAK_NU => LB_DIRECT,
                                UC_LINE_BREAK_AL => LB_DIRECT,
                                UC_LINE_BREAK_ID => LB_DIRECT,
                                UC_LINE_BREAK_IN => LB_INDIRECT,
                                UC_LINE_BREAK_HY => LB_INDIRECT,
                                UC_LINE_BREAK_BA => LB_INDIRECT,
                                UC_LINE_BREAK_BB => LB_DIRECT,
                                UC_LINE_BREAK_B2 => LB_DIRECT,
                                UC_LINE_BREAK_ZW => LB_PROHIBITED,
                                UC_LINE_BREAK_CM => LB_INDIRECT_CM,
                                UC_LINE_BREAK_WJ => LB_PROHIBITED,
                                UC_LINE_BREAK_H2 => LB_DIRECT,
                                UC_LINE_BREAK_H3 => LB_DIRECT,
                                UC_LINE_BREAK_JL => LB_DIRECT,
                                UC_LINE_BREAK_JV => LB_DIRECT,
                                UC_LINE_BREAK_JT => LB_INDIRECT));

/**
 * See CSS 2.1 16.6.1 The 'white-space' processing model
 */
class InlineContentBuilder {
  function InlineContentBuilder() {
  }

  function add_line_break(&$box, &$pipeline) {
    $break_box =& new BRBox();
    $break_box->readCSS($pipeline->get_current_css_state());
    $box->add_child($break_box);
  }

  function build(&$box, $text, &$pipeline) {
    error_no_method('build', get_class($this));
  }

  function break_into_lines($content) {
    return preg_split('/[\r\n]/u', $content);
  }

  function break_into_words($content) {
    $content = trim($content);
    if ($content == '') {
      return array();
    };

    // Extract Unicode characters from the raw content data
    $ptr = 0;
    $utf8_chars = array();
    $ucs2_chars = array();
    $size = strlen($content);
    while ($ptr < $size) {
      $utf8_char = ManagerEncoding::get_next_utf8_char($content, $ptr);
      $utf8_chars[] = $utf8_char;
      $ucs2_chars[] = utf8_to_code($utf8_char);
    };
      
    // Get unicode line breaking classes
    $classes = array_map(array($this, 'get_line_break_class'), $ucs2_chars);
    $this->find_line_break($classes, $breaks, count($classes));

    // Make words array
    $words = array();
    $word = '';
    for ($i = 0, $size = count($breaks); $i < $size; $i++) {
      $word .= $utf8_chars[$i];

      $break = $breaks[$i];
      if ($break == LB_INDIRECT ||
          $break == LB_INDIRECT_CM ||
          $break == LB_DIRECT ||
          $break == LB_EXPLICIT) {
        $words[] = trim($word);
        $word = '';
      };
    };

    return $words;
  }

  function find_complex_break($current_class, $classes, &$breaks, $offset, $length) {
    if ($offset >= $length) {
      return 0;
    };

    for ($i = $offset; $i < $length; $i++) {
      // TODO
      $breaks[$i - 1] = LB_PROHIBITED;
      if ($classes[$i] != UC_LINE_BREAK_SA) {
        break;
      };
    };

    return $i;
  }

  function find_line_break($classes, &$breaks, $length) {
    if (!$length) {
      return 0;
    };

    $class = $classes[0]; // class of 'before' character

    if ($class == UC_LINE_BREAK_LF ||
        $class == UC_LINE_BREAK_NL) {
      $class = UL_LINE_BREAK_BK;
    }

    // loop over all pairs in the string up to a hard break
    for ($i = 1; ($i < $length) && ($class != UC_LINE_BREAK_BK); $i++) {
      // handle explicit breaks here
      // handle BK, NL and LF explicitly
      if ($classes[$i] == UC_LINE_BREAK_BK || 
          $classes[$i] == UC_LINE_BREAK_NL ||  
          $classes[$i] == UC_LINE_BREAK_LF) {
        $breaks[$i-1] = LB_PROHIBITED;
        $class = UC_LINE_BREAK_BK;
        continue;
      }
    
      // handle CR explicitly
      if ($classes[$i] == UC_LINE_BREAK_CR) {
        $breaks[$i-1] = LB_PROHIBITED;
        $class = UC_LINE_BREAK_CR;
        continue;
      }      

      // handle spaces explicitly
      if ($classes[$i] == UC_LINE_BREAK_SP) {
        $breaks[$i-1] = LB_PROHIBITED;
        continue;
      };

      // handle complex scripts in a separate function
      if ($classes[$i] == UC_LINE_BREAK_SA) {
        $i += $this->find_complex_break($class, $classes, $breaks, $i, $length);

        if ($i < $length) {
          $class = $classes[$i];
          continue;
        };
      };

      // lookup pair table information 
      $current_class = $classes[$i];

      $break = $GLOBALS['_g_line_break_class_table'][$class][$current_class];
      $breaks[$i - 1] = $break;

      if ($break == LB_INDIRECT) {
        if ($classes[$i - 1] == UC_LINE_BREAK_SP) {
          $breaks[$i - 1] = LB_INDIRECT;
        } else {
          $breaks[$i - 1] = LB_PROHIBITED;
        };

      // handle breaks involving a combining mark
      } elseif ($break == LB_INDIRECT_CM) {
        $breaks[$i - 1]= LB_PROHIBITED;
        
        if ($classes[$i - 1] == UC_LINE_BREAK_SP) {
          $breaks[$i - 1] = LB_INDIRECT_CM;
        } else {
          continue; // do not update cls
        };
      } elseif ($break == LB_PROHIBITED_CM) {
        $breaks[$i - 1] = LB_PROHIBITED_CM;

        if ($classes[$i - 1] != UC_LINE_BREAK_SP) {
          continue;
        };
      };
      
      // save cls of 'before' character (unless bypassed by 'continue')
      $class = $classes[$i];
    };

    $breaks[$i-1] = LB_EXPLICIT;

    return $i;
  }

  function is_break_allowed($previous_class, $current_class) {
    return true;
  }

  function get_line_break_class($ucs2_char) {
    static $class_cache = array();

    if (!isset($class_cache[$ucs2_char])) {
      $table_handle = $this->get_line_break_class_table_handle();
      fseek($table_handle, $ucs2_char /* as integer */ , SEEK_SET);
      $class_cache[$ucs2_char] = ord(fread($table_handle, 1));
    };

    // Apply rule LB1 from the Unicode algorithm:
    //
    // Assign  a  line  breaking  class  to each  code  point  of  the
    // input. Resolve AI, CB, SA,  SG, and XX into other line breaking
    // classes  depending  on  criteria  outside  the  scope  of  this
    // algorithm.
    //
    // In the absence of such criteria, it is recommended that classes
    // AI, SA, SG, and XX be resolved to AL, except that characters of
    // class SA that have General_Category  Mn or Mc be resolved to CM
    // (see SA). Unresolved class CB is handled in rule LB20.

    // Resolve AI, SA, SG, and XX to AL
    if (in_array($class_cache[$ucs2_char],
                 array(UC_LINE_BREAK_AI,
                       UC_LINE_BREAK_SA,
                       UC_LINE_BREAK_SG,
                       UC_LINE_BREAK_XX))) {
      return UC_LINE_BREAK_AL;
    };

    return $class_cache[$ucs2_char];
  }

  function get_line_break_class_table_handle() {
	global $HTML2PS_CACHE_DIR;
    static $table_handle = null;

    if (is_null($table_handle)) {
#      $filename = CACHE_DIR.'unicode.lb.classes.dat';
      $filename = $HTML2PS_CACHE_DIR.'unicode.lb.classes.dat';
      if (!file_exists($filename)) {
        $this->generate_line_break_class_table($filename);
      };

      $table_handle = fopen($filename, 'rb');
      flock($table_handle, LOCK_SH);
    };

    return $table_handle;
  }

  function generate_line_break_class_table($output_filename) {
    $class_codes = array('BK' => 1,
                         'CR' => 2,
                         'LF' => 3,
                         'CM' => 4,
                         'NL' => 5,
                         'SG' => 6,
                         'WJ' => 7,
                         'ZW' => 8,
                         'GL' => 9,
                         'SP' => 10,
                         'B2' => 11,
                         'BA' => 12,
                         'BB' => 13,
                         'HY' => 14,
                         'CB' => 15,
                         'CL' => 16,
                         'EX' => 17,
                         'IN' => 18,
                         'NS' => 19,
                         'OP' => 20,
                         'QU' => 21,
                         'IS' => 22,
                         'NU' => 23,
                         'PO' => 24,
                         'PR' => 25,
                         'SY' => 26,
                         'AI' => 27,
                         'AL' => 28,
                         'H2' => 29,
                         'H3' => 30,
                         'ID' => 31,
                         'JL' => 32,
                         'JV' => 33,
                         'JT' => 34,
                         'SA' => 35,
                         'XX' => 36);

    $output_handle = fopen($output_filename, 'wb');
    flock($output_handle, LOCK_EX);

    $input_handle = fopen(HTML2PS_DIR.'/data/LineBreak.txt', 'r');
    $last_position = 0;
    while ($line = fgets($input_handle)) {
      $line = trim($line);

      if (strlen($line) == 0 || $line[0] == '#') {
        continue;
      };

      if (preg_match('/^([0-9a-f]+);(\w\w) #/i', $line, $matches)) {
        $unicode_position = hexdec($matches[1]);
        $class = $matches[2];
        
        if ($unicode_position > $last_position + 1) {
          fwrite($output_handle, str_repeat(chr(0), $unicode_position - $last_position - 1));
        };

        fwrite($output_handle, chr($class_codes[$class]));

        $last_position = $unicode_position;
      } elseif (preg_match('/^([0-9a-f]+)\.\.([0-9a-f]+);(\w\w) #/i', $line, $matches)) {
        $unicode_start_position = hexdec($matches[1]);
        $unicode_end_position = hexdec($matches[2]);
        $class = $matches[3];
        
        if ($unicode_start_position > $last_position + 1) {
          fwrite($output_handle, str_repeat(chr(0), $unicode_start_position - $last_position - 1));
        };

        fwrite($output_handle, str_repeat(chr($class_codes[$class]), $unicode_end_position - $unicode_start_position + 1));

        $last_position = $unicode_end_position;
      } else {
        var_dump($line); die();
      }
    };

    fclose($input_handle);

    flock($output_handle, LOCK_UN);
    fclose($output_handle);
  }

  function collapse_whitespace($content) {
    return preg_replace('/[\r\n\t ]+/u', ' ', $content);
  }

  function remove_leading_linefeeds($content) {
    return preg_replace('/^ *[\r\n]+/u', '', $content);
  }

  function remove_trailing_linefeeds($content) {
    return preg_replace('/[\r\n]+$/u', '', $content);
  }
}

?>
