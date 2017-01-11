-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.3' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.2' LIMIT 1;

-- broker option
DELETE FROM `options` WHERE `key` = 'broker';
INSERT INTO `options` (`key`, `value`) VALUES ('broker', 'broker');

-- Remove relations between contact templates and contactgroups
DELETE FROM contactgroup_contact_relation
WHERE contact_contact_id IN (
    SELECT contact_id FROM contact WHERE contact_register = '0'
);
