CREATE TABLE IPAndPortSessions (
  IPAndPort    BIGINT            NOT NULL PRIMARY KEY,
  LoginCount   TINYINT UNSIGNED  NOT NULL,
  GenTime      DATETIME          NOT NULL,
  LastRequest  DATETIME          NOT NULL,
  RequestCount INT UNSIGNED      NOT NULL
);
CREATE TABLE Sessions (
  Sid          BINARY(20)        NOT NULL PRIMARY KEY,
  Uid          INT UNSIGNED      NOT NULL UNIQUE, 
  GenIP        INT UNSIGNED      NOT NULL,
  GenPort      SMALLINT UNSIGNED NOT NULL,
  GenTime      DATETIME          NOT NULL,
  LastRequest  DATETIME          NOT NULL,
  RequestCount INT UNSIGNED      NOT NULL
);
