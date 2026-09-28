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

--changeset dave:17-create-membershiptype-table
CREATE TABLE `MembershipType` (
    `MembershipTypeID` INT NOT NULL AUTO_INCREMENT,
    `Name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`MembershipTypeID`),
    UNIQUE KEY `UK_MembershipType_Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `MembershipType`;

--changeset dave:18-seed-membershiptypes
INSERT INTO `MembershipType` (`MembershipTypeID`, `Name`) VALUES (1, 'Guest');
INSERT INTO `MembershipType` (`MembershipTypeID`, `Name`) VALUES (2, 'Jr Member');
INSERT INTO `MembershipType` (`MembershipTypeID`, `Name`) VALUES (3, 'Sr Member');
INSERT INTO `MembershipType` (`MembershipTypeID`, `Name`) VALUES (4, 'Board Member');
--rollback DELETE FROM `MembershipType` WHERE `MembershipTypeID` IN (1,2,3,4);

--changeset dave:19-create-duesstatus-table
CREATE TABLE `DuesStatus` (
    `DuesStatusID` INT NOT NULL AUTO_INCREMENT,
    `Name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`DuesStatusID`),
    UNIQUE KEY `UK_DuesStatus_Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `DuesStatus`;

--changeset dave:20-seed-duesstatuses
INSERT INTO `DuesStatus` (`DuesStatusID`, `Name`) VALUES (1, 'Paid');
INSERT INTO `DuesStatus` (`DuesStatusID`, `Name`) VALUES (2, 'Unpaid');
INSERT INTO `DuesStatus` (`DuesStatusID`, `Name`) VALUES (3, 'Overdue');
--rollback DELETE FROM `DuesStatus` WHERE `DuesStatusID` IN (1,2,3);

--changeset dave:21-alter-user-add-profile-fields
ALTER TABLE `User`
    ADD COLUMN `AddressLine1` VARCHAR(255) NULL,
    ADD COLUMN `AddressLine2` VARCHAR(255) NULL,
    ADD COLUMN `City` VARCHAR(255) NULL,
    ADD COLUMN `State` VARCHAR(2) NULL,
    ADD COLUMN `Zip` VARCHAR(10) NULL,
    ADD COLUMN `Phone` VARCHAR(12) NULL,
    ADD COLUMN `MembershipTypeID` INT NULL,
    ADD COLUMN `DuesPaidDate` DATE NULL,
    ADD COLUMN `DuesStatusID` INT NULL,
    ADD CONSTRAINT `FK_User_MembershipType` FOREIGN KEY (`MembershipTypeID`) REFERENCES `MembershipType` (`MembershipTypeID`),
    ADD CONSTRAINT `FK_User_DuesStatus` FOREIGN KEY (`DuesStatusID`) REFERENCES `DuesStatus` (`DuesStatusID`);
--rollback ALTER TABLE `User` DROP FOREIGN KEY `FK_User_MembershipType`, DROP FOREIGN KEY `FK_User_DuesStatus`, DROP COLUMN `AddressLine1`, DROP COLUMN `AddressLine2`, DROP COLUMN `City`, DROP COLUMN `State`, DROP COLUMN `Zip`, DROP COLUMN `Phone`, DROP COLUMN `MembershipTypeID`, DROP COLUMN `DuesPaidDate`, DROP COLUMN `DuesStatusID`;

--changeset dave:22-create-sitecontent-table
CREATE TABLE `SiteContent` (
    `SiteContentID` INT NOT NULL AUTO_INCREMENT,
    `ContentKey` VARCHAR(255) NOT NULL,
    `Content` LONGTEXT NOT NULL,
    `UpdatedDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`SiteContentID`),
    UNIQUE KEY `UK_SiteContent_ContentKey` (`ContentKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--rollback DROP TABLE `SiteContent`;

--changeset dave:23-seed-sitecontent-fla
INSERT INTO `SiteContent` (`ContentKey`, `Content`) VALUES ('fla', '<p>Bringing North Florida one step closer to the stars...</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Inventore voluptatum earum possimus, ipsum atque sint, vel iusto impedit non architecto, tempora saepe quos quia id accusantium. Fugit nesciunt explicabo dolore!</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Distinctio dignissimos magni culpa labore dolor dolorum ipsam, totam eum debitis quo pariatur nihil omnis excepturi ullam magnam nostrum, reiciendis fugiat esse?</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim minima nesciunt provident quis quisquam modi asperiores sed eaque nobis quae impedit optio, quos adipisci hic. Sequi mollitia temporibus facere quia.</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quasi, earum doloremque? Non possimus aliquid accusantium eveniet pariatur rerum unde. Enim placeat perferendis voluptatum asperiores tenetur harum blanditiis accusamus delectus dolor.</p>
                <p>Quasi consequuntur quam perferendis ad, in dolore molestias minima ullam iste iure earum, magnam odio assumenda error, itaque corrupti nostrum! Accusamus maiores, quaerat quidem itaque similique repudiandae ducimus atque labore?</p>
                <p>Culpa at dignissimos perferendis maiores corporis, reiciendis error harum, optio fugiat dolor animi repellat itaque repellendus deleniti eos quidem esse ex rerum, qui eveniet laborum autem impedit quas! Non, numquam?</p>
                <p>Iste, excepturi placeat, vitae ab exercitationem veniam eaque incidunt distinctio illo aspernatur quod dolor odio totam. Sunt rerum doloribus minima aperiam voluptate possimus, debitis pariatur, ratione enim sequi assumenda vero?</p>
                <p>Voluptatibus fugiat maiores veritatis voluptas itaque! Commodi consequatur nobis aut perferendis ea vel facilis id non ipsam, quidem, laborum illum recusandae? Nesciunt ut dolores ea inventore sit suscipit laborum nam.</p>');
--rollback DELETE FROM `SiteContent` WHERE `ContentKey` = 'fla';

--changeset dave:24-seed-sitecontent-launch
INSERT INTO `SiteContent` (`ContentKey`, `Content`) VALUES ('launch', '<p>Bringing North Florida one step closer to the stars...</p>

                <p>Launches to commence this summer 2024, please stay tuned for information</p>
                <h2>NAR Safety Code</h2>
                <div class="text ">
					<dl>
<dt><a href="https://www.nar.org/wp-content/uploads/2018/08/Model-Rocket-Safety-Code.pdf" target="_blank" rel="noopener"><b>Download a .pdf version of the Model Rocket Safety Code here.&nbsp;</b></a></dt>
</dl>
<p>&nbsp;</p>
<dl>
<dt></dt>
<dt></dt>
<dt><b>1. Materials</b></dt>
<dd>I will use only lightweight, non-metal parts for the nose, body, and fins of my rocket.</dd>
</dl>
<dl>
<dt><b>2. Motors</b></dt>
<dd>I will use only certified, commercially made model rocket motors, and will not tamper with these motors or use them for any purposes except those recommended by the manufacturer.</dd>
</dl>
<dl>
<dt><b>3. Ignition System</b></dt>
<dd>I will launch my rockets with an electrical launch system and electrical motor igniters. My launch system will have a safety interlock in series with the launch switch, and will use a launch switch that returns to the “off” position when released.</dd>
</dl>
<dl>
<dt><b>4. Misfires</b></dt>
<dd>If my rocket does not launch when I press the button of my electrical launch system, I will remove the launcher’s safety interlock or disconnect its battery, and will wait 60 seconds after the last launch attempt before allowing anyone to approach the rocket.</dd>
</dl>
<dl>
<dt><b>5. Launch Safety</b></dt>
<dd>I will use a countdown before launch, and will ensure that everyone is paying attention and is a safe distance of at least 15 feet away when I launch rockets with D motors or smaller, and 30 feet when I launch larger rockets. If I am uncertain about the safety or stability of an untested rocket, I will check the stability before flight and will fly it only after warning spectators and clearing them away to a safe distance. When conducting a simultaneous launch of more than ten rockets, I will observe a safe distance of 1.5 times the maximum expected altitude of any launched rocket.</dd>
</dl>
<dl>
<dt><b>6. Launcher</b></dt>
<dd>I will launch my rocket from a launch rod, tower, or rail that is pointed to within 30 degrees of the vertical to ensure that the rocket flies nearly straight up, and I will use a blast deflector to prevent the motor’s exhaust from hitting the ground. To prevent accidental eye injury, I will place launchers so that the end of the launch rod is above eye level or will cap the end of the rod when it is not in use.</dd>
</dl>
<dl>
<dt><b>7. Size</b></dt>
<dd>My model rocket will not weigh more than 1,500 grams (53 ounces) at liftoff and will not contain more than 125 grams (4.4 ounces) of propellant or 320 N-sec (71.9 pound-seconds) of total impulse.</dd>
</dl>
<dl>
<dt><b>8. Flight Safety</b></dt>
<dd>I will not launch my rocket at targets, into clouds, or near airplanes, and will not put any flammable or explosive payload in my rocket.</dd>
</dl>
<dl>
<dt><b>9. Launch Site</b></dt>
<dd>I will launch my rocket outdoors, in an open area at least as large as shown in the accompanying table, and in safe weather conditions with wind speeds no greater than 20 miles per hour. I will ensure that there is no dry grass close to the launch pad, and that the launch site does not present risk of grass fires.</dd>
</dl>
<dl>
<dt><b>10. Recovery System</b></dt>
<dd>I will use a recovery system such as a streamer or parachute in my rocket so that it returns safely and undamaged and can be flown again, and I will use only flame-resistant or fireproof recovery system wadding in my rocket.</dd>
</dl>
<dl>
<dt><b>11. Recovery Safety</b></dt>
<dd>I will not attempt to recover my rocket from power lines, tall trees, or other dangerous places.</dd>
</dl>
<p style="text-align: center;"><strong>Launch Site Dimensions</strong></p>
<table border="1">
<tbody>
<tr>
<th>Installed Total Impulse (N-sec)</th>
<th>Equivalent Motor Type</th>
<th>Minimum Site Dimensions (ft)</th>
</tr>
<tr class="alt">
<td style="text-align: center;">0.00 – 1.25</td>
<td style="text-align: center;">1/4A, 1/2A</td>
<td style="text-align: center;">50</td>
</tr>
<tr>
<td style="text-align: center;">1.26 – 2.50</td>
<td style="text-align: center;">A</td>
<td style="text-align: center;">100</td>
</tr>
<tr class="alt">
<td style="text-align: center;">2.51 – 5.00</td>
<td style="text-align: center;">B</td>
<td style="text-align: center;">200</td>
</tr>
<tr>
<td style="text-align: center;">5.01 – 10.00</td>
<td style="text-align: center;">C</td>
<td style="text-align: center;">400</td>
</tr>
<tr class="alt">
<td style="text-align: center;">10.01 – 20.00</td>
<td style="text-align: center;">D</td>
<td style="text-align: center;">500</td>
</tr>
<tr>
<td style="text-align: center;">20.01 – 40.00</td>
<td style="text-align: center;">E</td>
<td style="text-align: center;">1,000</td>
</tr>
<tr class="alt">
<td style="text-align: center;">40.01 – 80.00</td>
<td style="text-align: center;">F</td>
<td style="text-align: center;">1,000</td>
</tr>
<tr>
<td style="text-align: center;">80.01 – 160.00</td>
<td style="text-align: center;">G</td>
<td style="text-align: center;">1,000</td>
</tr>
<tr class="alt">
<td style="text-align: center;">160.01 – 320.00</td>
<td style="text-align: center;">Two Gs</td>
<td style="text-align: center;">1,500</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
					<div class="clear"></div>
				</div>
');
--rollback DELETE FROM `SiteContent` WHERE `ContentKey` = 'launch';

--changeset dave:25-seed-sitecontent-education
INSERT INTO `SiteContent` (`ContentKey`, `Content`) VALUES ('education', '<p>Bringing North Florida one step closer to the stars...</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Inventore voluptatum earum possimus, ipsum atque sint, vel iusto impedit non architecto, tempora saepe quos quia id accusantium. Fugit nesciunt explicabo dolore!</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Distinctio dignissimos magni culpa labore dolor dolorum ipsam, totam eum debitis quo pariatur nihil omnis excepturi ullam magnam nostrum, reiciendis fugiat esse?</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim minima nesciunt provident quis quisquam modi asperiores sed eaque nobis quae impedit optio, quos adipisci hic. Sequi mollitia temporibus facere quia.</p>');
--rollback DELETE FROM `SiteContent` WHERE `ContentKey` = 'education';
