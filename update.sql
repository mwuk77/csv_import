-- MW - DROP statement for ease of use.

DROP DATABASE IF EXISTS wrenTest;

-- Create database

CREATE DATABASE wrenTest;

-- and use...

USE wrenTest;

-- MW - DROP statement for ease of use.

DROP TABLE IF EXISTS tblProductData;

-- Create table for data

CREATE TABLE tblProductData (
  intProductDataId int(10) unsigned NOT NULL AUTO_INCREMENT,
  strProductName varchar(50) NOT NULL,
  strProductDesc varchar(255) NOT NULL,
  strProductCode varchar(10) NOT NULL,
  dtmAdded datetime DEFAULT NULL,
  dtmDiscontinued datetime DEFAULT NULL,
  stmTimestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (intProductDataId),
  UNIQUE KEY (strProductCode)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stores product data';


-- MW - Alter as per spec

ALTER TABLE tblProductData
    ADD decPriceGBP decimal(10,2) NOT NULL,
    ADD intStockLevel int(10) unsigned NOT NULL DEFAULT 0;