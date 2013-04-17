INSERT INTO `person` (`id`, `typ`, `name`, `email`, `email_bestaetigt`, `telefon`, `auth`, `angelegt_datum`, `admin`, `status`, `pwd_enc`, `benachrichtigungs_typ`) VALUES
(1, 'person', 'Admin', 'admin@test.de', 1, NULL, 'email:admin@test.de', '2013-04-14 20:33:49', 0, 0, 'sha256:1000:N9YHmSiI2PWORbyzrNAxmglojAPU0Nm6:6IzbgeTbSSjS7xONciqdEc5RxLv+gJRn', 'sofort');

INSERT INTO `veranstaltung` (`id`, `veranstaltungsreihe_id`, `name`, `name_kurz`, `datum_von`, `datum_bis`, `antragsschluss`, `policy_antraege`, `policy_aenderungsantraege`, `policy_kommentare`, `policy_unterstuetzen`, `typ`, `url_verzeichnis`, `admin_email`, `einstellungen`) VALUES
(1, 1, 'Test-Veranstaltung', '', NULL, NULL, '2017-04-14 20:37:02', 'Alle', 'Alle', '0', 'Niemand', 1, 'programm', NULL, NULL);

INSERT INTO `veranstaltungsreihe` (`id`, `subdomain`, `name`, `name_kurz`, `offiziell`, `einstellungen`, `aktuelle_veranstaltung_id`, `oeffentlich`, `kontakt_intern`) VALUES
(1, 'default', 'Testveranstaltung', NULL, 1, NULL, 1, 1, NULL);

INSERT INTO `veranstaltungsreihen_admins` (`veranstaltungsreihe_id`, `person_id`) VALUES
(1, 1);