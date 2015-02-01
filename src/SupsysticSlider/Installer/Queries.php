<?php return array (
    '{prefix}rs_exclude' => 'CREATE TABLE `{prefix}rs_exclude` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` int(11) NOT NULL,
  `entity_type` varchar(255) NOT NULL,
  `slider_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0',
    '{prefix}rs_folders' => 'CREATE TABLE `{prefix}rs_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0',
    '{prefix}rs_photos' => 'CREATE TABLE `{prefix}rs_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_id` int(11) NOT NULL DEFAULT \'0\',
  `album_id` int(11) NOT NULL,
  `attachment_id` int(11) NOT NULL,
  `position` int(11) NOT NULL DEFAULT \'9000\',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0',
    '{prefix}rs_photos_pos' => 'CREATE TABLE `{prefix}rs_photos_pos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) NOT NULL,
  `scope` enum(\'main\',\'folder\',\'gallery\') NOT NULL,
  `scope_id` int(11) NOT NULL DEFAULT \'0\',
  `position` int(11) NOT NULL DEFAULT \'2147483647\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    '{prefix}rs_resources' => 'CREATE TABLE `{prefix}rs_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_type` enum(\'folder\',\'image\',\'video\') NOT NULL,
  `resource_id` int(11) NOT NULL,
  `slider_id` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0',
    '{prefix}rs_settings_presets' => 'CREATE TABLE `{prefix}rs_settings_presets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `settings_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    '{prefix}rs_settings_sets' => 'CREATE TABLE `{prefix}rs_settings_sets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    '{prefix}rs_sliders' => 'CREATE TABLE `{prefix}rs_sliders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `settings_id` int(11) NOT NULL,
  `plugin` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0',
    '{prefix}rs_sorting' => 'CREATE TABLE `{prefix}rs_sorting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slider_id` int(11) NOT NULL,
  `index` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0',
    '{prefix}rs_stats' => 'CREATE TABLE `{prefix}rs_stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `visits` int(11) NOT NULL,
  `modify_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    '{prefix}rs_tags' => 'CREATE TABLE `{prefix}rs_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `tags` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    '{prefix}rs_videos' => 'CREATE TABLE `{prefix}rs_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_id` int(11) NOT NULL,
  `attachment_id` int(11) NOT NULL,
  `video_id` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0',
);