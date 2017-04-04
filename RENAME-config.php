<?php
// 1. Rename file
// Dev/Local 	= local-config.php
// Stage 		= stage-config.php
// Production 	= prod-config.php

// 2. Save it one folder up (../)
// Example of file structure
// - stage-config.php
// - wordpress/
// ---- wp-content
// ---- wp-includes
// ---- wp-admin
// ---- ... etc

define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

define('AUTH_KEY',         'f|d,ze7.+0acj{|!gj/<C$cE.Ng{GFFpu -[K(CJ0JL)H)Br@5?di qI5jl|<yC`');
define('SECURE_AUTH_KEY',  'Dmja|W=-Kb3VCc-7BLSAe7~0]]VJ#2wL!Jn{8o+qLhnsc_D@J+hNt*hxke#[T7aP');
define('LOGGED_IN_KEY',    'ziE6{.&bw]%qYfKsa_Va^aSmj`jT.(p5t%a&]wEkVzKmrWU_?Fb|8Q9Ny{+ip>(S');
define('NONCE_KEY',        '&ER+Yt0cW]uB$VJU+itacrYNFqFDuaZGD~E)]X=zT1kSkC&}/6Z8s0WN)0lBKcz]');
define('AUTH_SALT',        '{JrV0@0Y)?GG##4E[Ig_Skxs<42LPB:>~MMV>o_fzQ4L1ypmj6B^@w=i>;T`Rl6L');
define('SECURE_AUTH_SALT', 'Ds`3PMmCnTL# Ld.OHM#+4uB`Q|>IN/[3+lpjR4_&}iXV2JR k?;1a(iJN<LEHa9');
define('LOGGED_IN_SALT',   '4qR;+R 4g?Eg_,kvap`[--3{&e#?];.rGT(H3l`@W!Gy,P`K~XpATu{1cd!Iugsg');
define('NONCE_SALT',       'Qzv9{{ [p@/J:NyWdlT{i1?aoR1IC-RF<S-?4Cc%xBLM|UmMx~`Tteip*YUN^[|U');