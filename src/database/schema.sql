SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema MedienDB
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema MedienDB
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `MedienDB` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `MedienDB` ;

-- -----------------------------------------------------
-- Table `MedienDB`.`Nutzer`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `MedienDB`.`Nutzer` (
  `NutzerID` INT NOT NULL AUTO_INCREMENT,
  `Email` VARCHAR(100) NOT NULL,
  `Benutzername` VARCHAR(45) NOT NULL,
  `Passwort` VARCHAR(100) NOT NULL,
  `Rolle` ENUM('user', 'Admin') NOT NULL,
  PRIMARY KEY (`NutzerID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MedienDB`.`Tag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `MedienDB`.`Tag` (
  `TagID` INT NOT NULL AUTO_INCREMENT,
  `TagName` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`TagID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MedienDB`.`Medium`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `MedienDB`.`Medium` (
  `MediumID` INT NOT NULL AUTO_INCREMENT,
  `Titel` VARCHAR(100) NOT NULL,
  `Medienart` ENUM('Bild', 'Video', 'Hoerbuch', 'eBook') NOT NULL,
  `Datentyp` VARCHAR(45) NOT NULL,
  `Groesse` VARCHAR(45) NOT NULL,
  `Path` VARCHAR(100) NOT NULL,
  `NutzerID` INT NOT NULL,
  PRIMARY KEY (`MediumID`),
  INDEX `fk_Medium_Nutzer1_idx` (`NutzerID` ASC) ,
  CONSTRAINT `fk_Medium_Nutzer1`
    FOREIGN KEY (`NutzerID`)
    REFERENCES `MedienDB`.`Nutzer` (`NutzerID`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MedienDB`.`Medium_has_Tag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `MedienDB`.`Medium_has_Tag` (
  `MediumID` INT NOT NULL,
  `TagID` INT NOT NULL,
  PRIMARY KEY (`MediumID`, `TagID`),
  INDEX `fk_Medium_has_Tag_Tag1_idx` (`TagID` ASC) ,
  INDEX `fk_Medium_has_Tag_Medium1_idx` (`MediumID` ASC) ,
  CONSTRAINT `fk_Medium_has_Tag_Medium`
    FOREIGN KEY (`MediumID`)
    REFERENCES `MedienDB`.`Medium` (`MediumID`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Medium_has_Tag_Tag`
    FOREIGN KEY (`TagID`)
    REFERENCES `MedienDB`.`Tag` (`TagID`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
