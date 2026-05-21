create table gallery_site_db.photos
(
    id        int auto_increment
        primary key,
    file_path varchar(255) charset utf8 not null comment 'calea catre poza pe disk'
);

create table gallery_site_db.users
(
    id            int auto_increment
        primary key,
    username      varchar(40) charset utf8                            not null,
    password_hash varchar(255) charset utf8                           not null,
    role          enum ('admin', 'normal_user') default 'normal_user' not null,
    constraint username
        unique (username)
);

create table gallery_site_db.blogs
(
    id             int auto_increment
        primary key,
    author_user_id int                      not null,
    name           varchar(50) charset utf8 not null,
    constraint blogs_users_id_fk
        foreign key (author_user_id) references gallery_site_db.users (id)
            on update cascade on delete cascade
);

create table gallery_site_db.articles
(
    id      int auto_increment
        primary key,
    blog_id int                      not null,
    content text                     not null comment 'Reprezinta continutul, textual al articolului care va fi afisat in corpul efectiv al articolului',
    title   varchar(30) charset utf8 not null,
    constraint articles_blogs_id_fk
        foreign key (blog_id) references gallery_site_db.blogs (id)
            on update cascade on delete cascade
);

create table gallery_site_db.article_comments
(
    id         int auto_increment
        primary key,
    article_id int      not null,
    user_id    int      not null,
    content    tinytext not null comment 'Continutul textual al comentariului.',
    constraint article_comments_articles_id_fk
        foreign key (article_id) references gallery_site_db.articles (id)
            on update cascade on delete cascade,
    constraint article_comments_users_id_fk
        foreign key (user_id) references gallery_site_db.users (id)
            on update cascade on delete cascade
);

create table gallery_site_db.galleries
(
    id             int auto_increment
        primary key,
    author_user_id int                      not null,
    name           varchar(60) charset utf8 not null,
    constraint galleries_users_id_fk
        foreign key (author_user_id) references gallery_site_db.users (id)
            on update cascade on delete cascade
);

create table gallery_site_db.gallery_comments
(
    id         int auto_increment
        primary key,
    gallery_id int      not null,
    content    tinytext not null,
    constraint gallery_comments_galleries_id_fk
        foreign key (gallery_id) references gallery_site_db.galleries (id)
            on update cascade on delete cascade
);

