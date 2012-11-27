SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `lk_active` (
  `id` int(10) unsigned NOT NULL,
  `wordid` bigint(10) unsigned NOT NULL,
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `correct` tinyint(1) NOT NULL DEFAULT '0',
  `answer` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  KEY `id` (`id`),
  KEY `done` (`done`),
  KEY `correct` (`correct`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_activelist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `registerid` int(10) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `mode` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_forms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `registerid` int(10) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `info` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `userid` (`userid`,`registerid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_help` (
  `language` varchar(2) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `title` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `titletext` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `valuetext` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `language` (`language`,`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_persons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `registerid` int(10) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `order` smallint(3) unsigned NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `userid` (`userid`,`registerid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_registers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `groupcount` tinyint(2) unsigned NOT NULL DEFAULT '5',
  `grouplock` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '75?150?300?600?',
  `language` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`userid`),
  KEY `name` (`name`(3))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_save` (
  `saveid` int(10) unsigned NOT NULL,
  `wordid` bigint(20) unsigned NOT NULL,
  UNIQUE KEY `id_2` (`saveid`,`wordid`),
  KEY `id` (`saveid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_savelist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `registerid` int(10) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_2` (`userid`,`registerid`,`name`),
  KEY `userid` (`userid`,`registerid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_taglist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `registerid` int(10) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_2` (`userid`,`registerid`,`name`),
  KEY `userid` (`userid`,`registerid`),
  KEY `tag` (`name`(3))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_tags` (
  `wordid` bigint(20) unsigned NOT NULL,
  `tagid` int(10) unsigned NOT NULL,
  UNIQUE KEY `wordid_2` (`wordid`,`tagid`),
  KEY `wordid` (`wordid`),
  KEY `tags` (`tagid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `passw` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `forgot` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0',
  `email` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `hints` tinyint(1) NOT NULL DEFAULT '1',
  `gui` varchar(24) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'default',
  `theme` varchar(24) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'modern',
  `language` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'de',
  `hpic` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_verbs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wordid` bigint(20) unsigned NOT NULL,
  `personid` int(10) unsigned NOT NULL,
  `formid` int(10) unsigned NOT NULL,
  `kword` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `regular` tinyint(1) NOT NULL DEFAULT '1',
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `person` (`personid`,`formid`),
  KEY `wordid` (`wordid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `lk_words` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `registerid` int(10) unsigned NOT NULL,
  `wordfirst` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `wordfore` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `groupid` char(2) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sentence` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `wordclassid` tinyint(3) unsigned NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `regid` (`userid`,`registerid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `lk_help` (`language`, `title`, `titletext`, `valuetext`, `id`) VALUES
('de', 'menu', 'Menu', 'Das Menu ist der Navigationspunkt der Seite. Sie befindet sich ganz oben. An ihr kann man ablesen, wo man sich gerade befindet. Falls man eingeloggt findet man an erster Stelle stets den Benutzernamen, darauf folgt der Name der Kartei, in welcher man sich befindet usw. Jeder dieser Menüpunkte läst sich anklicken, wodurch man eine oder mehrere Ebenen nach oben gelangt. mit den Pfeilen neben den Einträgen kann man schnell zu anderen Karteien/Tags/Fächern navigieren. Zudem sind einige Optionen über diese Drop-Downs erreichbar. So auch die Benutzereinstellungen.\r\nDas Menu bietet zudem die Möglichkeit, über Eingaben zu navigieren. Wenn man links neben dem letzten Schrägstrich klickt erscheint ein Eingabefeld. Über dieses Feld kann man einerseits nach Einträgen suchen, oder auch direkt zu Tags, Karteien usw. navigieren.', 1),
('de', 'wordedit', 'Wort bearbeiten', 'Es gibt mehrere Wege ein Wort zu bearbeiten. Der schnellste ist der <b>Doppelklick</b>. Fast immer, wenn man ein Wort sieht kann man es per Doppelklick bearbeiten, selbst während der Abfrage. Auch Sätze, Fächer und Wortarten können so geändert werden. Um die Änderungen zu speichern muss man sie jeweils nur mit der <b>Eingabetaste</b> bestätigen. Anstelle des Doppelklicks, kann man ein Wort über den <b>Editierbutton</b> bearbeiten, welches erscheint, wenn man über das Wort fährt.', 3),
('de', 'register', 'Kartei', 'Eine Kartei kann man sich physisch als eine Box vorstellen, welche in mehrere Fächer eingeteilt ist. Es ist genau genommen eine <a href="http://de.wikipedia.org/wiki/Lernkartei">Lernkartei</a>. Sinnvoll ist es unterschiedliche Karteien für unterschiedliche <b>Sprachen</b> zu verwenden. Jede Kartei hat ihre eigenen Eigenschaften. Neben der Sprache der Kartei kann man bestimmen, wieviele Fächer sie hat und welche Limite diese Fächer haben.', 4),
('de', 'show', 'Wörter anzeigen', 'Wörter können am selben Ort bearbeitet werden, wo sie auch angezeigt werden. Um mehrere Wörter zu bearbeiten kann man sie auswählen und über die <b>Optionen</b> oben verschieben, löschen und weitere Aktionen ausführen. Um einen Eintrag zu bearbeiten, führt man einen <b>Doppelklick</b> auf den Eintrag aus. Mit Enter kann die Änderung bestätigt werden. <b>Tags</b> kann man über die + und x Buttons hinzufügen oder entfernen.\r\nFür Verben können <b>Verbtabellen</b> erstellt werden. Dazu klickt man ganz rechts in einer Zeile auf den Pfeil und dort auf Verbtabelle.', 5),
('de', 'syntax', 'Wortsyntax', 'Wörter werden bei der Abfrage automatisch auf Richtigkeit geprüft. Richtig ist aber nicht nur, was 1:1 dem original entspricht. Mit einigen Zeichen kann man mehrere Lösungen zulassen, so wie es oft gebraucht wird in Wörterbüchern.\r\n\r\n<b>Das Komma ","</b>: Mit dem Komma können die Antwortmöglichkeiten direkt voneinander abgetrennt werden.\r\nBsp: "eins, zwei" lässt "eins", "zwei" und "eins, zwei" als richtige Antworten gelten. Leerzeichen um das Komma spielen keine Rolle.\r\n\r\n<b>Runde Klammern "()"</b>: Mit runden Klammern umfasste Teile werden optional.\r\nBsp: "viel(leicht)" lässt "viel", "vielleicht" oder "viel(leicht) als richtig gelten.\r\n\r\n<b>Der Schrägstrich "/"</b>: Der Schrägstrich trennt Antwortmöglichkeiten voneinander. Im Gegensatz zum Komma trennt der Schrägstrich nur Teile der Antwort. Damit ersichtlich ist, für welchen Bereich der Schrägstrich gilt muss dieser in eckige Klammern gefasst werden.\r\nBsp: "einfa[ll/ch]" lässt "einfach", "einfall", "einfa[ll/ch]", aber auch "einfall/ch" als Antwort gelten.<br>Die Auswahl lässt sich beliebig erweitern: "fa[llen/lsch/hren]"\r\n"([1/2])" bewirkt das Selbe, wie "(1/2)". Runde Klammern erfüllen also die selbe Aufgabe wie eckige, jedoch kann der Inhalt so auch ganz weggelassen werden.\r\n\r\n<b>Der Bindestrich "-"</b>: Ein Bindestrich vor einer geschlossenen Klammer bewirkt, dass das erste Zeichen nach der Klammer klein geschrieben werden muss, wenn der Inhalt der Klammer dabei steht.\r\nBsp: "die (Gross-)Macht" lässt "die Macht", "die Grossmacht" und "die (Gross-)Macht" als Antworten gelten.\r\n\r\n<b>Das Sternchen "*"</b>: Das Sternchen ermöglicht Kommentare im Ausdruck. Alles was nach dem Sternchen steht wird nicht auf übereinstimmung geprüft.\r\nBsp: "Wort *kommentar" lässt "Wort" oder "Wort *kommentar" als Antwort zu.', 6),
('de', 'tag', 'Tags', 'Tags (engl.) dienen dazu, die Wörter einer Kartei zu ordnen. Ein Wort kann beliebig viele tags haben. Um mehrere Tags hinzuzufügen kann man diese durch Kommas getrennt eingeben.', 7),
('de', 'verb', 'Verbtabellen', 'In Verbtabellen werden Konjugationen eines Verbs gespeichert. Jede Kartei hat seine eigenen Personen und Formen. Verbtabellen können für alle Wörter erstellt werden, die als Verb deklariert wurden.\r\n\r\n<b>Personen</b> können über das Menu hinzugefügt werden. Mehrere Personen können mit einem <b>Komma</b> getrennt gleichzeitig hinzugefügt werden.\r\nBsp: Ich, Du, Er, Wir, Ihr, Sie\r\n\r\nDie <b>Form</b> kann gleich hinzugefügt werden wie die Personen.\r\nBsp: Gegenwart, Vergangenheit\r\n\r\nAnschliessend können die <b>Konjugationen hinzugefügt</b> werden. Fügt man ein neues Verb hinzu, sind noch alle Eingabefelder leer. Später kann ein Eintrag per <b>Doppelklick</b> bearbeitet oder hinzugefügt werden.\r\nMit der <b>Tabulatortaste</b> kann man das aktuelle Feld abspeichern, und zum folgenden wechseln. Mit den <b>Pfeiltasten</b> kann man zu den Einträgen ober- und unterhalb wechseln. Mit <b>Esc</b> wird die Bearbeitung abgebrochen.', 8),
('de', 'grouplock', 'Fachsperre', '<b>Fachsperren</b> bestimmen die Mindestanzahl der Wörter, die in einem Fach nötig sind, bevor dieses abgefragt werden kann. Man kann es sich als die <b>Grösse des des Fachs</b> vorstellen. Erst wenn ein Fach voll ist, darf man es wieder abfragen.\r\nFalls die Zahl noch nicht erreicht ist, können die Wörter darin zwar abgefragt werden, bleiben aber im gleichen Fach.\r\nWie Hoch die Fachsperre ist, sieht man neben der Anzahl der Wörter.\r\nUm diese zu <b>bearbeiten</b> klickt man rechts neben dem Fach auf den Editierbutton.', 9),
('de', 'querysave', 'Abfrage speichern', 'Nachdem eine Abfrage beendet wurde, kann man diese abspeichern, um die gleichen Wörter später noch einmal abzufragen.\r\nDie gespeicherte Abfrage ist anschliessend in der Übersicht unter dem gewählten Namen als Speicherplatz zu finden.\r\nEs ist auch möglich nur jene Wörter zu speichern, die man falsch beantwortet hat.', 10),
('en', 'grouplock', 'Group lock', 'A <b>group lock</b> defines how many words need to be in a group before it can be queried. It''s how "big" the group is. Only if it''s full, it can be queried.\r\nIf the words are queried anyway, they will stay in the same group.\r\nTo <b>edit</b> the group lock, klick on the edit button next to the group.', 11),
('en', 'menu', 'Menu', 'In the menu on top you can see, where you are. By clicking on an element you get right there. With the dropdown next to an element you can navigate to different places and also edit the specific element.\r\nClicking on the X next to some elements, will remove this element from the path.\r\n\r\nAt the end of the path is the searchbar. It can search words but also you can navigate by typing the name of a register or group etc.', 12),
('en', 'querysave', 'Save query', 'After finishing a query you can save it to a storage, to query it later again.\r\nThe words of the query will be saved to a storage, which can then be seen in the overview of the register.\r\nYou can also save wrong answered words only.', 13),
('en', 'register', 'Register', 'You can imagine the register physicaly as a box, which is divided into some parts (the groups).\r\nIt''s usefull to use different registers for different <b>languages</b>. Every register has it''s own properties. Beside the language you can choose how many groups it has and what the verb tables look like.', 14),
('en', 'show', 'Show words', 'Words can be edited in the same place where they are shown. To edit multiple words just select them and chose an <b>option</b> from the toolbar above.\r\nTo edit something just <b>doubleclick</b> on it. you can also click on the edit button next to it.\r\n<b>Tags</b> can be added and remove via the + and X Buttons.\r\nIf a word ist declared as verb (wordclass), you can add a <b>verb table</b> to it by clicking on the dropdown on the right hand side.', 15),
('en', 'syntax', 'Word syntax', 'In a query, words will automaticaly be checked for correctness. The validation is depending on some syntax, which you can use to let all possible answers be correct.\r\n\r\n<b>The comma ","</b>: Different results are seperated by a comma.\r\ne.g. "one, two" can be "one", "two" or "one, two".\r\n\r\n<b>Round brackets "()"</b>: Parts in round brackets become optional.\r\ne.g. "(may)be" can be "maybe", "be" or "(may)be".\r\n\r\n<b>The slash "/"</b>: The slash divides possible answers. In contrast to the comma it also divides parts of words. This parts must be in square brackets.\r\ne.g. "fl[y/ew/own]" can be "fly", "flew", "flown" or "fl[y/ew/own]".\r\n"(1/2)" does the same as "([1/2])"\r\n\r\n<b>The hyphen "-"</b>: The hyphen is most usefull in languages, where nouns begin with a capital letter, like german. Letters following a "-)" must be lowercase if the content of the brakets is also written:\r\ne.g. "die (Gross-)Macht" can be "die Macht", "die Grossmacht" or "die (Gross-)Macht".\r\n\r\n<b>The asterisk "*"</b>: This will cut of everything that comes after it. This is however usefull to make a comment.\r\ne.g. "word *comment" can be "word" or "word *comment".', 16),
('en', 'tag', 'Tags', 'tags are usefull to organise words. Words that belong together in some kind should have the same tag.\r\nMultiple tags can be added comma separated.', 17),
('en', 'verb', 'Verb tables', 'A verb table contains conjugations of verbs. Every register has it''s own persons and forms.\r\nEvery wird declared as a verb can have a table.\r\n\r\n<b>Persons</b> can be added via the toolbar. Multiple persons can be added at once, separated by commas.\r\ne.g. I, you, he, we, you, they\r\n\r\nThe <b>Form</b> can be added similar to the person.\r\ne.g. Present, Past\r\n\r\nAfter that the conjugated verbs can be <b>added</b>, by clicking on the edit button. later they can be edited the same way or by <b>doubleclicking</b>.\r\nTo add multiple verbs faster, you can navigate the fields with the <b>tabulator</b> and the <b>arrow keys</b> (up and down).\r\nWith <b>Esc</b> the changes will be canceled.', 18),
('en', 'wordedit', 'Edit words', 'There are two ways to edit a word. The fastest is to <b>doubleclick</b> on it. You can edit words like that mostly everywhere (even during a query).\r\nAlso other entries can be edited like this.\r\nWith the <b>Enter key</b> the changes will be saved.\r\nAn other way to edit words is via the <b>edit button</b> next to it.', 19);

INSERT INTO `lk_user` (`id`, `name`, `passw`, `forgot`, `email`, `hints`, `gui`, `theme`, `language`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', '0', 'admin@projectlk.ch', 1, '', '', ''),
(0, 'Gast', '81dc9bdb52d04dc20036dbd8313ed055', '0', '', 1, '', '', '');
