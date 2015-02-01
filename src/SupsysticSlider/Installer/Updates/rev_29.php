<?php return array(
    "ALTER TABLE  `{prefix}rs_resources` CHANGE  `resource_type`  `resource_type` ENUM(  'folder',  'image',  'video' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL",
    "UPDATE `{prefix}rs_resources` SET  `resource_type` =  'image' WHERE `resource_type` != 'video' AND `resource_type` != 'folder'"
);
