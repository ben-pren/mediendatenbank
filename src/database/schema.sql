SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema mediendb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mediendb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mediendb` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `mediendb` ;

-- -----------------------------------------------------
-- Table `mediendb`.`nutzer`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mediendb`.`nutzer` (
  `NutzerID` INT(11) NOT NULL AUTO_INCREMENT,
  `Email` VARCHAR(100) NOT NULL,
  `Benutzername` VARCHAR(45) NOT NULL,
  `Passwort` VARCHAR(100) NOT NULL,
  `Rolle` ENUM('user', 'Admin') NOT NULL,
  PRIMARY KEY (`NutzerID`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `mediendb`.`medium`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mediendb`.`medium` (
  `MediumID` INT(11) NOT NULL AUTO_INCREMENT,
  `Titel` VARCHAR(100) NOT NULL,
  `Medienart` ENUM('Bild', 'Video', 'Hoerbuch', 'eBook') NOT NULL,
  `Datentyp` VARCHAR(45) NOT NULL,
  `Groesse` VARCHAR(45) NOT NULL,
  `Path` VARCHAR(300) NOT NULL,
  `NutzerID` INT(11) NOT NULL,
  PRIMARY KEY (`MediumID`),
  INDEX `fk_Medium_Nutzer1_idx` (`NutzerID` ASC),
  CONSTRAINT `fk_Medium_Nutzer1`
    FOREIGN KEY (`NutzerID`)
    REFERENCES `mediendb`.`nutzer` (`NutzerID`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 14
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `mediendb`.`tag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mediendb`.`tag` (
  `TagID` INT(11) NOT NULL AUTO_INCREMENT,
  `TagName` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`TagID`))
ENGINE = InnoDB
AUTO_INCREMENT = 86
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `mediendb`.`medium_has_tag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mediendb`.`medium_has_tag` (
  `MediumID` INT(11) NOT NULL,
  `TagID` INT(11) NOT NULL,
  PRIMARY KEY (`MediumID`, `TagID`),
  INDEX `fk_Medium_has_Tag_Tag1_idx` (`TagID` ASC),
  INDEX `fk_Medium_has_Tag_Medium1_idx` (`MediumID` ASC),
  CONSTRAINT `fk_Medium_has_Tag_Medium`
    FOREIGN KEY (`MediumID`)
    REFERENCES `mediendb`.`medium` (`MediumID`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Medium_has_Tag_Tag`
    FOREIGN KEY (`TagID`)
    REFERENCES `mediendb`.`tag` (`TagID`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `mediendb`.`tag_request`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mediendb`.`tag_request` (
  `RequestID` INT(11) NOT NULL AUTO_INCREMENT,
  `NutzerID` INT(11) NOT NULL,
  `TagID` INT(11) NULL DEFAULT NULL,
  `RequestedTagName` VARCHAR(50) NOT NULL,
  `Kommentar` TEXT NULL DEFAULT NULL,
  `Kommentar_Admin` TEXT NULL DEFAULT NULL,
  `Status` ENUM('offen', 'genehmigt', 'abgelehnt') NULL DEFAULT 'offen',
  `ErstelltAm` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`RequestID`),
  INDEX `fk_TagRequest_Nutzer_idx` (`NutzerID` ASC),
  INDEX `fk_TagRequest_Tag_idx` (`TagID` ASC) ,
  CONSTRAINT `fk_TagRequest_Nutzer`
    FOREIGN KEY (`NutzerID`)
    REFERENCES `mediendb`.`nutzer` (`NutzerID`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_TagRequest_Tag`
    FOREIGN KEY (`TagID`)
    REFERENCES `mediendb`.`tag` (`TagID`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 27
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
