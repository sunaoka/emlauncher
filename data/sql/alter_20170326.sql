alter table application add column last_rquested datetime default null comment '最終リクエスト時刻' after last_commented;
alter table application modify column date_to_sort datetime not null comment 'last_upload,last_comment,last_erquested,createdのうち最新のもの';
