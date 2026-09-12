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

--changeset dave:5-seed-admin-user
INSERT INTO `User` (`UserID`, `Username`, `FullName`, `Email`, `PasswordHash`, `RoleID`) VALUES (1, 'admin', 'Administrator', 'support@flalaunch.com', '$2y$10$68lg9T5SR799.xeULJRN4etmuwmdZIWvd8qsblrNn4WmtZbBI6t0y', 1);
--rollback DELETE FROM `User` WHERE `UserID` = 1;

--changeset dave:6-create-loginattempt-table
CREATE TABLE `LoginAttempt` (
    `AttemptID` INT NOT NULL AUTO_INCREMENT,
    `Username` VARCHAR(255) NOT NULL,
    `IPAddress` VARCHAR(45) NOT NULL,
    `AttemptTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Successful` TINYINT(1) NOT NULL,
    PRIMARY KEY (`AttemptID`),
    KEY `IDX_LoginAttempt_Username_AttemptTime` (`Username`, `AttemptTime`),
    KEY `IDX_LoginAttempt_IPAddress_AttemptTime` (`IPAddress`, `AttemptTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `LoginAttempt`;

--changeset dave:7-update-admin-password
UPDATE `User` SET `PasswordHash` = '$2y$10$ysnq4hHyBHFbeFI44gXp7OLXaAZHkp26AklicXOKON8p0UxNQ72.6' WHERE `Username` = 'admin';
--rollback UPDATE `User` SET `PasswordHash` = '$2y$10$68lg9T5SR799.xeULJRN4etmuwmdZIWvd8qsblrNn4WmtZbBI6t0y' WHERE `Username` = 'admin';

--changeset dave:8-seed-sample-page
INSERT INTO `Page` (`Title`, `PageKey`, `Summary`, `Content`, `MemberOnly`) VALUES ('Sample Page', 'sample_page', 'A sample page used for testing content rendering.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 0);
--rollback DELETE FROM `Page` WHERE `PageKey` = 'sample_page';
