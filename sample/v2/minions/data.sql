DROP TABLE IF EXISTS min_user;
CREATE TABLE min_user(
  uid int(10) unsigned not null auto_increment COMMENT '用户ID',
  username varchar(32) not null DEFAULT '' comment '用户名',
  password varchar(128) not null DEFAULT '' comment '密码',
  nickname varchar(32) not null DEFAULT '' comment '昵称',
  PRIMARY KEY (uid),
  UNIQUE KEY (username)
)ENGINE = INNODB DEFAULT CHARSET UTF8;