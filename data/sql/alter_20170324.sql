alter table install_log add column device_info_id int(11) default 0 comment 'iOSデバイス情報のid' after package_id;
