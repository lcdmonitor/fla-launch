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

--changeset dave:9-create-gallerycategory-table
CREATE TABLE `GalleryCategory` (
    `CategoryID` INT NOT NULL AUTO_INCREMENT,
    `Name` VARCHAR(255) NOT NULL,
    `SortOrder` INT NOT NULL DEFAULT 0,
    `Hidden` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`CategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `GalleryCategory`;

--changeset dave:10-create-galleryphoto-table
CREATE TABLE `GalleryPhoto` (
    `PhotoID` INT NOT NULL AUTO_INCREMENT,
    `CategoryID` INT NOT NULL,
    `FileName` VARCHAR(255) NOT NULL,
    `ThumbFileName` VARCHAR(255) NOT NULL,
    `SortOrder` INT NOT NULL DEFAULT 0,
    `CreatedDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`PhotoID`),
    KEY `IDX_GalleryPhoto_Category_Sort` (`CategoryID`, `SortOrder`),
    CONSTRAINT `FK_GalleryPhoto_Category` FOREIGN KEY (`CategoryID`) REFERENCES `GalleryCategory` (`CategoryID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `GalleryPhoto`;

--changeset dave:11-create-newsalert-table
CREATE TABLE `NewsAlert` (
    `NewsAlertID` INT NOT NULL AUTO_INCREMENT,
    `Content` LONGTEXT NOT NULL,
    `UpdatedDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`NewsAlertID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `NewsAlert`;

--changeset dave:12-seed-newsalert-content
INSERT INTO `NewsAlert` (`NewsAlertID`, `Content`) VALUES (1, 'Exciting news from Florida Launch Alliance as we gear up to kickstart launch activities this summer. With a strategic focus on space exploration and satellite deployment, the alliance promises to usher in a new era of innovation and discovery. Leveraging Florida''s prime location for space launches, the alliance is poised to deliver cutting-edge missions and propel scientific advancements to new heights. Stay tuned as Florida Launch Alliance prepares to ignite the skies and inspire the world with their upcoming launch endeavors.');
--rollback DELETE FROM `NewsAlert` WHERE `NewsAlertID` = 1;

--changeset dave:13-update-newsalert-content
UPDATE `NewsAlert` SET `Content` = 'Stay tuned for news from FLA' WHERE `NewsAlertID` = 1;
--rollback UPDATE `NewsAlert` SET `Content` = 'Exciting news from Florida Launch Alliance as we gear up to kickstart launch activities this summer. With a strategic focus on space exploration and satellite deployment, the alliance promises to usher in a new era of innovation and discovery. Leveraging Florida''s prime location for space launches, the alliance is poised to deliver cutting-edge missions and propel scientific advancements to new heights. Stay tuned as Florida Launch Alliance prepares to ignite the skies and inspire the world with their upcoming launch endeavors.' WHERE `NewsAlertID` = 1;

--changeset dave:14-create-pagehit-table
CREATE TABLE `PageHit` (
    `HitID` INT NOT NULL AUTO_INCREMENT,
    `HitTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `UserID` INT NULL,
    `Username` VARCHAR(255) NULL,
    `RequestUrl` VARCHAR(1024) NOT NULL,
    `Referrer` VARCHAR(1024) NULL,
    `IPAddress` VARCHAR(45) NOT NULL,
    `UserAgent` VARCHAR(512) NULL,
    PRIMARY KEY (`HitID`),
    KEY `IDX_PageHit_HitTime` (`HitTime`),
    KEY `IDX_PageHit_UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `PageHit`;

--changeset dave:15-create-passwordreset-table
CREATE TABLE `PasswordReset` (
    `ResetID` INT NOT NULL AUTO_INCREMENT,
    `UserID` INT NOT NULL,
    `TokenHash` CHAR(64) NOT NULL,
    `CreatedDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ExpiresDate` DATETIME NOT NULL,
    `UsedDate` DATETIME NULL,
    PRIMARY KEY (`ResetID`),
    KEY `IDX_PasswordReset_TokenHash` (`TokenHash`),
    CONSTRAINT `FK_PasswordReset_User` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `PasswordReset`;

--changeset dave:16-create-passwordresetattempt-table
CREATE TABLE `PasswordResetAttempt` (
    `AttemptID` INT NOT NULL AUTO_INCREMENT,
    `Email` VARCHAR(255) NOT NULL,
    `IPAddress` VARCHAR(45) NOT NULL,
    `AttemptTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`AttemptID`),
    KEY `IDX_PasswordResetAttempt_Email_AttemptTime` (`Email`, `AttemptTime`),
    KEY `IDX_PasswordResetAttempt_IPAddress_AttemptTime` (`IPAddress`, `AttemptTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `PasswordResetAttempt`;
