--liquibase formatted sql

--changeset dave:1-create-role-table
CREATE TABLE `Role` (
    `RoleID` INT NOT NULL AUTO_INCREMENT,
    `Rolename` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`RoleID`),
    UNIQUE KEY `UK_Role_Rolename` (`Rolename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `Role`;

--changeset dave:2-seed-default-roles
INSERT INTO `Role` (`RoleID`, `Rolename`) VALUES (1, 'Admin');
INSERT INTO `Role` (`RoleID`, `Rolename`) VALUES (2, 'Member');
--rollback DELETE FROM `Role` WHERE `RoleID` IN (1, 2);

--changeset dave:3-create-user-table
CREATE TABLE `User` (
    `UserID` INT NOT NULL AUTO_INCREMENT,
    `Username` VARCHAR(255) NOT NULL,
    `FullName` VARCHAR(255) NOT NULL,
    `Email` VARCHAR(255) NOT NULL,
    `PasswordHash` VARCHAR(255) NOT NULL,
    `RoleID` INT NOT NULL DEFAULT 2,
    PRIMARY KEY (`UserID`),
    UNIQUE KEY `UK_User_Username` (`Username`),
    UNIQUE KEY `UK_User_Email` (`Email`),
    CONSTRAINT `FK_User_Role` FOREIGN KEY (`RoleID`) REFERENCES `Role` (`RoleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `User`;

--changeset dave:4-create-page-table
CREATE TABLE `Page` (
    `PageID` INT NOT NULL AUTO_INCREMENT,
    `Title` VARCHAR(255) NOT NULL,
    `PageKey` VARCHAR(255) NOT NULL,
    `Summary` VARCHAR(255) NOT NULL,
    `Content` LONGTEXT NOT NULL,
    `MemberOnly` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`PageID`),
    UNIQUE KEY `UK_Page_PageKey` (`PageKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `Page`;
