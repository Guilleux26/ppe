use ppe;

set default_storage_engine = InnoDB;
create table if not exists forum_question
(
    id          int auto_increment,
    idMembre    int          not null,
    titre       varchar(150) not null,
    contenu     text         not null,
    dateCreation datetime    not null default current_timestamp,
    archive     tinyint      not null default 0,
    satisfaite  tinyint      not null default 0,
    archiveLe   datetime     null,
    archivePar  int          null,

    constraint pk_forum_question primary key (id),
    constraint fk_forum_question_membre
        foreign key (idMembre) references membre (id)
            on delete cascade on update cascade,
    constraint fk_forum_question_archiveur
        foreign key (archivePar) references membre (id)
            on delete set null on update cascade,
    constraint ck_forum_question_archive check (archive in (0, 1)),
    constraint ck_forum_question_satisfaite check (satisfaite in (0, 1)),
    index ix_forum_question_archive_date (archive, dateCreation)
)
    engine = InnoDB
    default character set = utf8mb4
    collate = utf8mb4_unicode_ci;

create table if not exists forum_reponse
(
    id           int auto_increment,
    idQuestion   int      not null,
    idMembre     int      not null,
    contenu      text     not null,
    dateCreation datetime not null default current_timestamp,

    constraint pk_forum_reponse primary key (id),
    constraint fk_forum_reponse_question
        foreign key (idQuestion) references forum_question (id)
            on delete cascade on update cascade,
    constraint fk_forum_reponse_membre
        foreign key (idMembre) references membre (id)
            on delete cascade on update cascade,
    index ix_forum_reponse_question_date (idQuestion, dateCreation)
)
    engine = InnoDB
    default character set = utf8mb4
    collate = utf8mb4_unicode_ci;

grant select, insert, update (archive, satisfaite, archiveLe, archivePar), delete
    on ppe.forum_question
    to 'ppe'@'localhost';
grant select, insert, delete
    on ppe.forum_reponse
    to 'ppe'@'localhost';
