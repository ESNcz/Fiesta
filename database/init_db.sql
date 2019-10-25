SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `buddy_match`;
CREATE TABLE `buddy_match` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `international` varchar(100) NOT NULL,
  `university` varchar(25) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_match_id_university` (`member`,`international`,`university`),
  UNIQUE KEY `international_university` (`international`,`university`),
  KEY `university` (`university`),
  CONSTRAINT `buddy_match_ibfk_1` FOREIGN KEY (`member`) REFERENCES `data_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `buddy_match_ibfk_2` FOREIGN KEY (`international`) REFERENCES `data_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `buddy_match_ibfk_3` FOREIGN KEY (`university`) REFERENCES `university` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `buddy_request`;
CREATE TABLE `buddy_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_user` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `take` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`data_user`),
  UNIQUE KEY `id` (`id`),
  KEY `data_user_take` (`data_user`,`take`),
  CONSTRAINT `buddy_request_ibfk_11` FOREIGN KEY (`data_user`) REFERENCES `data_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `buddy_settings`;
CREATE TABLE `buddy_settings` (
  `university_id` varchar(25) NOT NULL,
  `show_manual` tinyint(4) NOT NULL DEFAULT '0',
  `show_image` tinyint(4) NOT NULL DEFAULT '0',
  `show_university` tinyint(4) NOT NULL DEFAULT '0',
  `show_state` tinyint(4) NOT NULL DEFAULT '0',
  `show_faculty` tinyint(4) NOT NULL DEFAULT '0',
  `show_gender` tinyint(4) NOT NULL DEFAULT '0',
  `limit` int(11) NOT NULL DEFAULT '5',
  PRIMARY KEY (`university_id`),
  CONSTRAINT `buddy_settings_ibfk_2` FOREIGN KEY (`university_id`) REFERENCES `university` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `bug`;
CREATE TABLE `bug` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `status` enum('open','working','solved') NOT NULL DEFAULT 'open',
  `user_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `id` (`id`),
  CONSTRAINT `bug_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `country`;
CREATE TABLE `country` (
  `code_id` varchar(3) NOT NULL,
  `name` varchar(150) NOT NULL,
  `currency_name` varchar(20) NOT NULL,
  `currency_code` varchar(20) NOT NULL,
  PRIMARY KEY (`code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `country` (`code_id`, `name`, `currency_name`, `currency_code`) VALUES
('AD',	'Andorra',	'Euro',	'EUR'),
('AE',	'United Arab Emirates',	'United Arab Emirates',	'AED'),
('AF',	'Afghanistan',	'Afghan afghani',	'AFN'),
('AG',	'Antigua And Barbuda',	'East Caribbean dolla',	'XCD'),
('AI',	'Anguilla',	'East Caribbean dolla',	'XCD'),
('AL',	'Albania',	'Albanian lek',	'ALL'),
('AM',	'Armenia',	'Armenian dram',	'AMD'),
('AO',	'Angola',	'Angolan kwanza',	'AOA'),
('AR',	'Argentina',	'Argentine peso',	'ARS'),
('AT',	'Austria',	'Euro',	'EUR'),
('AU',	'Australia',	'Australian dollar',	'AUD'),
('AW',	'Aruba',	'Aruban florin',	'AWG'),
('AZ',	'Azerbaijan',	'Azerbaijani manat',	'AZN'),
('BA',	'Bosnia and Herzegovina',	'Bosnia and Herzegovi',	'BAM'),
('BB',	'Barbados',	'Barbadian dollar',	'BBD'),
('BD',	'Bangladesh',	'Bangladeshi taka',	'BDT'),
('BE',	'Belgium',	'Euro',	'EUR'),
('BF',	'Burkina Faso',	'West African CFA fra',	'XOF'),
('BG',	'Bulgaria',	'Bulgarian lev',	'BGN'),
('BH',	'Bahrain',	'Bahraini dinar',	'BHD'),
('BI',	'Burundi',	'Burundian franc',	'BIF'),
('BM',	'Bermuda',	'Bermudian dollar',	'BMD'),
('BN',	'Brunei',	'Brunei dollar',	'BND'),
('BO',	'Bolivia',	'Bolivian boliviano',	'BOB'),
('BR',	'Brazil',	'Brazilian real',	'BRL'),
('BT',	'Bhutan',	'Bhutanese ngultrum',	'BTN'),
('BY',	'Belarus',	'Belarusian ruble',	'BYR'),
('BZ',	'Belize',	'Belize dollar',	'BZD'),
('CA',	'Canada',	'Canadian dollar',	'CAD'),
('CC',	'Cocos (Keeling) Islands',	'Australian dollar',	'AUD'),
('CF',	'Central African Republic',	'Central African CFA ',	'XAF'),
('CH',	'Switzerland',	'Swiss franc',	'CHF'),
('CK',	'Cook Islands',	'New Zealand dollar',	'NZD'),
('CL',	'Chile',	'Chilean peso',	'CLP'),
('CM',	'Cameroon',	'Central African CFA ',	'XAF'),
('CN',	'China',	'Chinese yuan',	'CNY'),
('CO',	'Colombia',	'Colombian peso',	'COP'),
('CR',	'Costa Rica',	'Costa Rican colón',	'CRC'),
('CU',	'Cuba',	'Cuban convertible pe',	'CUC'),
('CV',	'Cape Verde',	'Cape Verdean escudo',	'CVE'),
('CY',	'Cyprus',	'Euro',	'EUR'),
('CZ',	'Czech Republic',	'Czech koruna',	'CZK'),
('DE',	'Germany',	'Euro',	'EUR'),
('DJ',	'Djibouti',	'Djiboutian franc',	'DJF'),
('DK',	'Denmark',	'Danish krone',	'DKK'),
('DM',	'Dominica',	'East Caribbean dolla',	'XCD'),
('DO',	'Dominican Republic',	'Dominican peso',	'DOP'),
('DZ',	'Algeria',	'Algerian dinar',	'DZD'),
('EC',	'Ecuador',	'United States dollar',	'USD'),
('EE',	'Estonia',	'Euro',	'EUR'),
('EG',	'Egypt',	'Egyptian pound',	'EGP'),
('ER',	'Eritrea',	'Eritrean nakfa',	'ERN'),
('ES',	'Spain',	'Euro',	'EUR'),
('ET',	'Ethiopia',	'Ethiopian birr',	'ETB'),
('FI',	'Finland',	'Euro',	'EUR'),
('FK',	'Falkland Islands',	'Falkland Islands pou',	'FKP'),
('FR',	'France',	'Euro',	'EUR'),
('GA',	'Gabon',	'Central African CFA ',	'XAF'),
('GB',	'United Kingdom',	'British pound',	'GBP'),
('GD',	'Grenada',	'East Caribbean dolla',	'XCD'),
('GE',	'Georgia',	'Georgian lari',	'GEL'),
('GH',	'Ghana',	'Ghana cedi',	'GHS'),
('GI',	'Gibraltar',	'Gibraltar pound',	'GIP'),
('GN',	'Guinea',	'Guinean franc',	'GNF'),
('GQ',	'Equatorial Guinea',	'Central African CFA ',	'XAF'),
('GR',	'Greece',	'Euro',	'EUR'),
('GT',	'Guatemala',	'Guatemalan quetzal',	'GTQ'),
('GW',	'Guinea-Bissau',	'West African CFA fra',	'XOF'),
('HN',	'Honduras',	'Honduran lempira',	'HNL'),
('HR',	'Croatia (Hrvatska)',	'Croatian Kuna',	'HRK'),
('HT',	'Haiti',	'Haitian gourde',	'HTG'),
('HU',	'Hungary',	'Hungarian forint',	'HUF'),
('ID',	'Indonesia',	'Indonesian rupiah',	'IDR'),
('IE',	'Ireland',	'Euro',	'EUR'),
('IL',	'Israel',	'Israeli new shekel',	'ILS'),
('IN',	'India',	'Indian rupee',	'INR'),
('IO',	'British Indian Ocean Territory',	'United States dollar',	'USD'),
('IQ',	'Iraq',	'Iraqi dinar',	'IQD'),
('IR',	'Iran',	'Iranian rial',	'IRR'),
('IS',	'Iceland',	'Icelandic króna',	'ISK'),
('IT',	'Italy',	'Euro',	'EUR'),
('JO',	'Jordan',	'Jordanian dinar',	'JOD'),
('JP',	'Japan',	'Japanese yen',	'JPY'),
('KE',	'Kenya',	'Kenyan shilling',	'KES'),
('KG',	'Kyrgyzstan',	'Kyrgyzstani som',	'KGS'),
('KH',	'Cambodia',	'Cambodian riel',	'KHR'),
('KM',	'Comoros',	'Comorian franc',	'KMF'),
('KN',	'Saint Kitts And Nevis',	'East Caribbean dolla',	'XCD'),
('KP',	'North Korea',	'',	''),
('KR',	'South Korea',	'South Korean Won',	'KRW'),
('KY',	'Cayman Islands',	'Cayman Islands dolla',	'KYD'),
('KZ',	'Kazakhstan',	'Kazakhstani tenge',	'KZT'),
('LA',	'Laos',	'Lao kip',	'LAK'),
('LB',	'Lebanon',	'Lebanese pound',	'LBP'),
('LI',	'Liechtenstein',	'Swiss franc',	'CHF'),
('LK',	'Sri Lanka',	'Sri Lankan rupee',	'LKR'),
('LR',	'Liberia',	'Liberian dollar',	'LRD'),
('LS',	'Lesotho',	'Lesotho loti',	'LSL'),
('LT',	'Lithuania',	'Euro',	'EUR'),
('LU',	'Luxembourg',	'Euro',	'EUR'),
('LV',	'Latvia',	'Euro',	'EUR'),
('LY',	'Libya',	'Libyan dinar',	'LYD'),
('MA',	'Morocco',	'Moroccan dirham',	'MAD'),
('MC',	'Monaco',	'Euro',	'EUR'),
('MD',	'Moldova',	'Moldovan leu',	'MDL'),
('MG',	'Madagascar',	'Malagasy ariary',	'MGA'),
('MH',	'Marshall Islands',	'United States dollar',	'USD'),
('ML',	'Mali',	'West African CFA fra',	'XOF'),
('MM',	'Myanmar',	'Burmese kyat',	'MMK'),
('MN',	'Mongolia',	'Mongolian tögrög',	'MNT'),
('MR',	'Mauritania',	'Mauritanian ouguiya',	'MRO'),
('MS',	'Montserrat',	'East Caribbean dolla',	'XCD'),
('MT',	'Malta',	'Euro',	'EUR'),
('MU',	'Mauritius',	'Mauritian rupee',	'MUR'),
('MV',	'Maldives',	'Maldivian rufiyaa',	'MVR'),
('MW',	'Malawi',	'Malawian kwacha',	'MWK'),
('MX',	'Mexico',	'Mexican peso',	'MXN'),
('MY',	'Malaysia',	'Malaysian ringgit',	'MYR'),
('MZ',	'Mozambique',	'Mozambican metical',	'MZN'),
('NA',	'Namibia',	'Namibian dollar',	'NAD'),
('NE',	'Niger',	'West African CFA fra',	'XOF'),
('NG',	'Nigeria',	'Nigerian naira',	'NGN'),
('NI',	'Nicaragua',	'Nicaraguan córdoba',	'NIO'),
('NL',	'Netherland',	'Euro',	'EUR'),
('NO',	'Norway',	'Norwegian krone',	'NOK'),
('NP',	'Nepal',	'Nepalese rupee',	'NPR'),
('NR',	'Nauru',	'Australian dollar',	'AUD'),
('NU',	'Niue',	'New Zealand dollar',	'NZD'),
('NZ',	'New Zealand',	'New Zealand dollar',	'NZD'),
('OM',	'Oman',	'Omani rial',	'OMR'),
('PA',	'Panama',	'Panamanian balboa',	'PAB'),
('PE',	'Peru',	'Peruvian nuevo sol',	'PEN'),
('PF',	'French Polynesia',	'CFP franc',	'XPF'),
('PG',	'Papua new Guinea',	'Papua New Guinean ki',	'PGK'),
('PH',	'Philippines',	'Philippine peso',	'PHP'),
('PK',	'Pakistan',	'Pakistani rupee',	'PKR'),
('PL',	'Poland',	'Polish złoty',	'PLN'),
('PT',	'Portugal',	'Euro',	'EUR'),
('PY',	'Paraguay',	'Paraguayan guaraní',	'PYG'),
('QA',	'Qatar',	'Qatari riyal',	'QAR'),
('RO',	'Romania',	'Romanian leu',	'RON'),
('RS',	'Serbia',	'Serbian dinar',	'RSD'),
('RU',	'Russia',	'Russian ruble',	'RUB'),
('RW',	'Rwanda',	'Rwandan franc',	'RWF'),
('SA',	'Saudi Arabia',	'Saudi riyal',	'SAR'),
('SB',	'Solomon Islands',	'Solomon Islands doll',	'SBD'),
('SC',	'Seychelles',	'Seychellois rupee',	'SCR'),
('SD',	'Sudan',	'Sudanese pound',	'SDG'),
('SE',	'Sweden',	'Swedish krona',	'SEK'),
('SG',	'Singapore',	'Brunei dollar',	'BND'),
('SI',	'Slovenia',	'Euro',	'EUR'),
('SK',	'Slovakia',	'Euro',	'EUR'),
('SL',	'Sierra Leone',	'Sierra Leonean leone',	'SLL'),
('SM',	'San Marino',	'Euro',	'EUR'),
('SN',	'Senegal',	'West African CFA fra',	'XOF'),
('SO',	'Somalia',	'Somali shilling',	'SOS'),
('SR',	'Suriname',	'Surinamese dollar',	'SRD'),
('SS',	'South Sudan',	'South Sudanese pound',	'SSP'),
('ST',	'Sao Tome and Principe',	'São Tomé and Príncip',	'STD'),
('SV',	'El Salvador',	'United States dollar',	'USD'),
('SY',	'Syria',	'Syrian pound',	'SYP'),
('TD',	'Chad',	'Central African CFA ',	'XAF'),
('TG',	'Togo',	'West African CFA fra',	'XOF'),
('TH',	'Thailand',	'Thai baht',	'THB'),
('TJ',	'Tajikistan',	'Tajikistani somoni',	'TJS'),
('TN',	'Tunisia',	'Tunisian dinar',	'TND'),
('TO',	'Tonga',	'Tongan paʻanga',	'TOP'),
('TP',	'East Timor',	'United States dollar',	'USD'),
('TR',	'Turkey',	'Turkish lira',	'TRY'),
('TT',	'Trinidad And Tobago',	'Trinidad and Tobago ',	'TTD'),
('TV',	'Tuvalu',	'Australian dollar',	'AUD'),
('TW',	'Taiwan',	'New Taiwan dollar',	'TWD'),
('TZ',	'Tanzania',	'Tanzanian shilling',	'TZS'),
('UA',	'Ukraine',	'Ukrainian hryvnia',	'UAH'),
('UG',	'Uganda',	'Ugandan shilling',	'UGX'),
('US',	'United States',	'United States dollar',	'USD'),
('UY',	'Uruguay',	'Uruguayan peso',	'UYU'),
('UZ',	'Uzbekistan',	'Uzbekistani som',	'UZS'),
('VE',	'Venezuela',	'Venezuelan bolívar',	'VEF'),
('VN',	'Vietnam',	'Vietnamese đồng',	'VND'),
('VU',	'Vanuatu',	'Vanuatu vatu',	'VUV'),
('WS',	'Samoa',	'Samoan tālā',	'WST'),
('XJ',	'Jersey',	'British pound',	'GBP'),
('YE',	'Yemen',	'Yemeni rial',	'YER'),
('ZW',	'Zimbabwe',	'Botswana pula',	'BWP');

DROP TABLE IF EXISTS `data_user`;
CREATE TABLE `data_user` (
  `user_id` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT 'Unknown',
  `surname` varchar(50) NOT NULL DEFAULT 'Unknown',
  `gender` enum('m','f') NOT NULL DEFAULT 'm',
  `country_id` varchar(3) DEFAULT NULL,
  `home_university` varchar(125) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `esn_card` varchar(25) DEFAULT NULL,
  `phone_number` varchar(25) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `facebook_url` varchar(250) DEFAULT NULL,
  `twitter_url` varchar(250) DEFAULT NULL,
  `instagram_url` varchar(250) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`user_id`),
  KEY `faculty` (`faculty_id`),
  KEY `country_id` (`country_id`),
  CONSTRAINT `data_user_ibfk_10` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `data_user_ibfk_12` FOREIGN KEY (`country_id`) REFERENCES `country` (`code_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `data_user_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `data_user` (`user_id`, `name`, `surname`, `gender`, `country_id`, `home_university`, `birthday`, `esn_card`, `phone_number`, `faculty_id`, `facebook_url`, `twitter_url`, `instagram_url`, `description`) VALUES
('admin@mendel.cz',	'Admin',	'Mendel',	'f',	NULL,	NULL,	NULL,	NULL,	NULL,	22,	NULL,	NULL,	NULL,	NULL),
('admin@muni.cz',	'Admin',	'Muni',	'm',	NULL,	NULL,	NULL,	NULL,	NULL,	13,	NULL,	NULL,	NULL,	NULL),
('admin@vutbr.cz',	'Admin',	'BUT',	'm',	'CZ',	'',	'1970-01-01',	'',	'',	8,	'',	'',	'',	''),
('editor@mendel.cz',	'Editor',	'Mendel',	'f',	NULL,	NULL,	NULL,	NULL,	NULL,	24,	NULL,	NULL,	NULL,	NULL),
('editor@muni.cz',	'Editor',	'Muni',	'f',	'SK',	NULL,	'1970-01-01',	'',	'',	14,	'',	'',	'',	''),
('editor@vutbr.cz',	'Editor',	'BUT',	'm',	NULL,	NULL,	NULL,	NULL,	NULL,	4,	NULL,	NULL,	NULL,	NULL),
('international@mendel.cz',	'International',	'Mendel',	'f',	NULL,	NULL,	NULL,	NULL,	NULL,	21,	NULL,	NULL,	NULL,	NULL),
('international@muni.cz',	'International',	'Muni',	'm',	NULL,	NULL,	NULL,	NULL,	NULL,	13,	NULL,	NULL,	NULL,	NULL),
('international@vutbr.cz',	'International',	'BUT',	'f',	NULL,	NULL,	NULL,	NULL,	NULL,	4,	NULL,	NULL,	NULL,	NULL),
('member@mendel.cz',	'Member',	'Mendel',	'm',	NULL,	NULL,	NULL,	NULL,	NULL,	20,	NULL,	NULL,	NULL,	NULL),
('member@muni.cz',	'Member',	'Muni',	'f',	NULL,	NULL,	NULL,	NULL,	NULL,	13,	NULL,	NULL,	NULL,	NULL),
('member@vutbr.cz',	'Member',	'BUT',	'm',	NULL,	NULL,	NULL,	NULL,	NULL,	7,	NULL,	NULL,	NULL,	NULL);

DROP TABLE IF EXISTS `event`;
CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `location` varchar(250) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `capacity` int(64) NOT NULL,
  `event_date` datetime NOT NULL,
  `price_with_esn` int(128) NOT NULL,
  `price_without_esn` int(128) NOT NULL,
  `registration_start` date NOT NULL,
  `registration_end` enum('yes','no') NOT NULL DEFAULT 'no',
  `description` text CHARACTER SET utf8 COLLATE utf8_czech_ci,
  `university` varchar(25) NOT NULL,
  `last_edit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `university` (`university`),
  CONSTRAINT `event_ibfk_10` FOREIGN KEY (`university`) REFERENCES `university` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_ibfk_11` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `event_list`;
CREATE TABLE `event_list` (
  `event` int(11) NOT NULL,
  `data_user` varchar(100) NOT NULL,
  `status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  PRIMARY KEY (`event`,`data_user`),
  KEY `data_user` (`data_user`),
  CONSTRAINT `event_list_ibfk_1` FOREIGN KEY (`event`) REFERENCES `event` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_list_ibfk_2` FOREIGN KEY (`data_user`) REFERENCES `data_user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `faculty`;
CREATE TABLE `faculty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `university_id` varchar(25) NOT NULL,
  `faculty` varchar(100) NOT NULL,
  `faculty_shortcut` varchar(25) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `university_faculty` (`university_id`,`faculty`),
  KEY `faculty` (`faculty`),
  CONSTRAINT `faculty_ibfk_2` FOREIGN KEY (`university_id`) REFERENCES `university` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `faculty` (`id`, `university_id`, `faculty`, `faculty_shortcut`) VALUES
(1,	'BUT',	'Central European Institute of Technology BUT',	'CEITEC'),
(2,	'BUT',	'Faculty of Architecture',	'FA'),
(3,	'BUT',	'Faculty of Business and Management',	'FBM'),
(4,	'BUT',	'Faculty of Chemistry',	'FCH'),
(5,	'BUT',	'Faculty of Civil Engineering',	'FCE'),
(6,	'BUT',	'Faculty of Electrical Engineering and Communication',	'FEEC'),
(7,	'BUT',	'Faculty of Fine Arts',	'FFA'),
(8,	'BUT',	'Faculty of Information Technology',	'FIT'),
(9,	'BUT',	'Faculty of Mechanical Engineering',	'FME'),
(10,	'BUT',	'Institute of Forensic Engineering',	'IFE'),
(11,	'MUNI',	'Faculty of Arts',	'PHIL'),
(12,	'MUNI',	'Faculty of Economics and Administration',	'ECON'),
(13,	'MUNI',	'Faculty of Education',	'PED'),
(14,	'MUNI',	'Faculty of Informatics',	'FI'),
(15,	'MUNI',	'Faculty of Law',	'LAW'),
(16,	'MUNI',	'Faculty of Medicine',	'MED'),
(17,	'MUNI',	'Faculty of Science',	'SCI'),
(18,	'MUNI',	'Faculty of Social Studies',	'FSS'),
(19,	'MUNI',	'Faculty of Sports Studies',	'FSPS'),
(20,	'MENDELU',	'Faculty of AgriScience',	'AF'),
(21,	'MENDELU',	'Faculty of Forestry and Wood Technology',	'IPM'),
(22,	'MENDELU',	'Faculty of Business and Economics',	'PEF'),
(23,	'MENDELU',	'Faculty of Horticulture',	'ZF'),
(24,	'MENDELU',	'Faculty of Regional Development and International Studies',	'FRRMS'),
(25,	'MENDELU',	'Institute of lifelong learning',	'ICV');

DROP TABLE IF EXISTS `module`;
CREATE TABLE `module` (
  `presenter_id` varchar(25) NOT NULL,
  `order` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `icon` varchar(25) NOT NULL,
  `description` text,
  `visibility` tinyint(4) DEFAULT '1',
  `author` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`presenter_id`),
  UNIQUE KEY `order` (`order`),
  KEY `author` (`author`),
  CONSTRAINT `module_ibfk_3` FOREIGN KEY (`author`) REFERENCES `data_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `module` (`presenter_id`, `order`, `name`, `icon`, `description`, `visibility`, `author`) VALUES
('BuddyManagement',	1,	'Buddy Management',	'zmdi-face',	'Buddy Manager is a powerful plugin with all of the most important features for easier manage all of internationals.',	1,	'thanh.dolong@gmail.com'),
('Dictionary',	5,	'ESN dictionary',	'zmdi-translate',	'Ever wondered what all those abbreviations and phrases used in ESN mean? Explore the ESNdictionary — All the ESN slang in one place.',	1,	'thanh.dolong@gmail.com'),
('EventManager',	3,	'Event Manager',	'zmdi-calendar',	'Events Manager provides a simple way for easier manage all of your events. It is a carefully crafted with love.',	1,	'thanh.dolong@gmail.com'),
('HRmanager',	4,	'HR Manager',	'zmdi-assignment-account',	'Now you can easily manage HR records. It will be helpful for managing processes or viewing all the requests on one page.',	0,	'thanh.dolong@gmail.com'),
('PickupManagement',	2,	'Pick Up Management',	'zmdi-pin-account',	'PickUp Manager is a powerful plugin with all of the most important features for easier manage all of internationals.',	1,	'thanh.dolong@gmail.com');

DROP TABLE IF EXISTS `module_assignment`;
CREATE TABLE `module_assignment` (
  `module` varchar(25) NOT NULL,
  `university` varchar(25) NOT NULL,
  PRIMARY KEY (`university`,`module`),
  KEY `university` (`university`),
  KEY `module` (`module`),
  CONSTRAINT `module_assignment_ibfk_4` FOREIGN KEY (`university`) REFERENCES `university` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `module_assignment_ibfk_6` FOREIGN KEY (`module`) REFERENCES `module` (`presenter_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `module_submenu`;
CREATE TABLE `module_submenu` (
  `module_id` varchar(25) NOT NULL,
  `action` varchar(25) NOT NULL DEFAULT 'default',
  `order` int(11) NOT NULL,
  `title` varchar(25) NOT NULL,
  PRIMARY KEY (`module_id`,`action`),
  CONSTRAINT `module_submenu_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `module` (`presenter_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `module_submenu` (`module_id`, `action`, `order`, `title`) VALUES
('BuddyManagement',	'create',	0,	'Create request'),
('BuddyManagement',	'default',	2,	'See Connections'),
('BuddyManagement',	'request',	1,	'Buddy system'),
('BuddyManagement',	'settings',	3,	'Settings'),
('EventManager',	'create',	1,	'Create Event'),
('EventManager',	'default',	2,	'View Event'),
('PickupManagement',	'create',	0,	'Create request'),
('PickupManagement',	'default',	2,	'See Connections'),
('PickupManagement',	'request',	1,	'Pick up system'),
('PickupManagement',	'settings',	3,	'Settings');

DROP TABLE IF EXISTS `pickup_match`;
CREATE TABLE `pickup_match` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `international` varchar(100) NOT NULL,
  `university` varchar(25) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_match_id_university` (`member`,`international`,`university`),
  UNIQUE KEY `international_university` (`international`,`university`),
  KEY `university` (`university`),
  CONSTRAINT `pickup_match_ibfk_1` FOREIGN KEY (`member`) REFERENCES `data_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pickup_match_ibfk_2` FOREIGN KEY (`international`) REFERENCES `data_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pickup_match_ibfk_3` FOREIGN KEY (`university`) REFERENCES `university` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pickup_request`;
CREATE TABLE `pickup_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_user` varchar(100) NOT NULL,
  `date_arrival` timestamp NULL DEFAULT NULL,
  `place_arrival` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `take` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`data_user`),
  UNIQUE KEY `id` (`id`),
  KEY `data_user_take` (`data_user`,`take`),
  CONSTRAINT `pickup_request_ibfk_11` FOREIGN KEY (`data_user`) REFERENCES `data_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pickup_settings`;
CREATE TABLE `pickup_settings` (
  `university_id` varchar(25) NOT NULL,
  `show_manual` tinyint(4) NOT NULL DEFAULT '0',
  `show_image` tinyint(4) NOT NULL DEFAULT '0',
  `show_university` tinyint(4) NOT NULL DEFAULT '0',
  `show_state` tinyint(4) NOT NULL DEFAULT '0',
  `show_faculty` tinyint(4) NOT NULL DEFAULT '0',
  `show_gender` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`university_id`),
  CONSTRAINT `pickup_settings_ibfk_2` FOREIGN KEY (`university_id`) REFERENCES `university` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `role_assignment`;
CREATE TABLE `role_assignment` (
  `data_user` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  PRIMARY KEY (`data_user`,`role`),
  KEY `user_id` (`data_user`),
  CONSTRAINT `role_assignment_ibfk_2` FOREIGN KEY (`data_user`) REFERENCES `data_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `role_assignment` (`data_user`, `role`) VALUES
('admin@mendel.cz',	'admin'),
('admin@mendel.cz',	'member'),
('admin@muni.cz',	'admin'),
('admin@muni.cz',	'member'),
('admin@vutbr.cz',	'admin'),
('admin@vutbr.cz',	'member'),
('editor@mendel.cz',	'editor'),
('editor@mendel.cz',	'member'),
('editor@muni.cz',	'editor'),
('editor@muni.cz',	'member'),
('editor@vutbr.cz',	'editor'),
('editor@vutbr.cz',	'member'),
('international@mendel.cz',	'international'),
('international@muni.cz',	'international'),
('international@vutbr.cz',	'international'),
('member@mendel.cz',	'member'),
('member@muni.cz',	'member'),
('member@vutbr.cz',	'member');

DROP TABLE IF EXISTS `suggested_feature`;
CREATE TABLE `suggested_feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` tinytext NOT NULL,
  `status` enum('open','working','complete') NOT NULL DEFAULT 'open',
  `role` enum('member','mentee') NOT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `id` (`id`),
  CONSTRAINT `suggested_feature_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `university`;
CREATE TABLE `university` (
  `id` varchar(25) NOT NULL,
  `name` varchar(50) NOT NULL,
  `section_short` varchar(50) NOT NULL,
  `section_long` varchar(50) NOT NULL,
  `dashboard` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `university` (`id`, `name`, `section_short`, `section_long`, `dashboard`) VALUES
('BUT',	'Brno University of Technology',	'ESN VUT Brno',	'Erasmus Student Network VUT Brno',	'Lorem ipsum dolor amet blue bottle four loko deep v seitan pabst post-ironic, snackwave squid hell of sustainable. PBR&B offal kogi, narwhal trust fund VHS pok pok neutra pitchfork literally cray enamel pin flexitarian tote bag fingerstache. Trust fund swag blue bottle, organic vinyl shabby chic readymade. Kombucha yr asymmetrical artisan XOXO. Locavore everyday carry neutra hell of fingerstache mustache. Bespoke church-key humblebrag, literally narwhal wolf leggings tacos keytar lomo cliche waistcoat.'),
('MENDELU',	'Mendel University in Brno',	'ISC MENDELU Brno',	'International Students Club Mendelu Brno',	'Authentic retro post-ironic aesthetic, pop-up cardigan tote bag viral hashtag whatever migas gentrify. Woke XOXO intelligentsia, echo park vape schlitz truffaut subway tile etsy gochujang fanny pack. Skateboard next level vegan hoodie thundercats master cleanse meggings fam retro pitchfork blog. Farm-to-table kogi authentic tattooed air plant cronut helvetica wayfarers. Jean shorts biodiesel adaptogen offal. Cliche actually fixie banjo.'),
('MUNI',	'Masaryk University',	'ESN MUNI Brno',	'Erasmus Student Network MUNI Brno',	'Tousled vaporware lyft drinking vinegar paleo tacos. Yuccie austin man braid tofu mixtape pork belly sustainable pickled activated charcoal synth literally yr single-origin coffee retro umami. Glossier sriracha keytar YOLO, wolf pork belly food truck vice. Trust fund fanny pack leggings craft beer. Activated charcoal skateboard live-edge tousled bushwick disrupt. Bushwick venmo knausgaard, mlkshk hoodie coloring book actually sustainable four dollar toast echo park.');

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` varchar(100) NOT NULL,
  `status` enum('active','pending','enabled','uncompleted','banned') NOT NULL DEFAULT 'uncompleted',
  `password` varchar(100) NOT NULL,
  `university` varchar(25) NOT NULL,
  `signature` varchar(10) NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `valid` timestamp NULL DEFAULT NULL,
  `token` varchar(100) DEFAULT '',
  `tokenExpire` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `esn_section_id` (`university`),
  CONSTRAINT `user_ibfk_2` FOREIGN KEY (`university`) REFERENCES `university` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user` (`user_id`, `status`, `password`, `university`, `signature`, `last_login`, `valid`, `token`, `tokenExpire`) VALUES
('admin@mendel.cz',	'active',	'$2y$10$SUHNPwChGOrXGGLJivbdkeebEtklRxqntVuUBuXjAyIZlgA9.wQx2',	'MENDELU',	'wcr6bg0rq6',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('admin@muni.cz',	'active',	'$2y$10$riTEqooyTYF3tA2996K9f.oGrXt42SmOCaJbP5Rs4G8.FF8DnoxFO',	'MUNI',	'smcnjwu1je',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('admin@vutbr.cz',	'active',	'$2y$10$Bbo5ah/l9vW4qVWOeBkD4ecFZPXuD8LWgiu7fbwmvlkz7zWzN.v4i',	'BUT',	'lwwnf33e3x',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('editor@mendel.cz',	'pending',	'$2y$10$2cbqGxmJQ7JIZF.zO24qEuV/RNSBzcjBoxPoWAT6pX/bvmn/2S7Xi',	'MENDELU',	'9no2k6rz6j',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('editor@muni.cz',	'active',	'$2y$10$kKszUeShf/rQka2YipRZRucUrDDmPs8v5.v6bjSVYA9D2/sZ2mV6.',	'MUNI',	'0ty7qyodgs',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('editor@vutbr.cz',	'active',	'$2y$10$6FxFFnWcUwdD4tlXOVJo5.F0mKwFVH3ImQU/YZXO0akLNyO.f/P.u',	'BUT',	'ev5kbwjsm4',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('international@mendel.cz',	'active',	'$2y$10$CC6QjT013BrzIM9xcDbwt.8cp4dsyUCpPk6hAMGzJ2mALKebp7K9K',	'MENDELU',	'o1d44k5kbn',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('international@muni.cz',	'active',	'$2y$10$EpDnWnk0CBzt9ZCvc2P/euzM6AZOd7yrWdS2DjB6Hdz8kZaZQOpzy',	'MUNI',	'qrm1liktvx',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('international@vutbr.cz',	'active',	'$2y$10$nETiPrXmkBlIC9wOvO74IOcQxO/YfaHnIs31s2z5Nn5bj6ZDiUuY2',	'BUT',	'shr3ny2yh7',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('member@mendel.cz',	'pending',	'$2y$10$frdyXjgCvZWC2QNPo4sFdupHgV0m3TK.qkECY3Z.F76WDlvAtLwJO',	'MENDELU',	'fh3st5v512',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('member@muni.cz',	'active',	'$2y$10$0qcURhXBOfYmf7TG4pN99uhdbubAfZhXNQ1jleLziknNUbi4hf6RW',	'MUNI',	'8z4ku20jvy',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL),
('member@vutbr.cz',	'active',	'$2y$10$.rIa7sGoGCI5/q4EQHuAMeCDKCNjNDbLu8lbT9jqDvNZeNu5wZ58m',	'BUT',	'sv05c7rr9j',	'2018-10-11 00:00:00',	'2030-01-01 00:00:00',	'',	NULL);

DROP TABLE IF EXISTS `vote`;
CREATE TABLE `vote` (
  `feature_id` int(11) NOT NULL,
  `user` varchar(100) NOT NULL DEFAULT '0',
  PRIMARY KEY (`feature_id`,`user`),
  KEY `user` (`user`),
  CONSTRAINT `vote_ibfk_3` FOREIGN KEY (`feature_id`) REFERENCES `suggested_feature` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `vote_ibfk_5` FOREIGN KEY (`user`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `links`;
CREATE TABLE IF NOT EXISTS `links` (
  `university` varchar(25) NOT NULL DEFAULT '',
  `url` varchar(256) NOT NULL DEFAULT '',
  `title` varchar(128) NOT NULL DEFAULT '',
  KEY `links_1` (`university`),
  CONSTRAINT `links_1` FOREIGN KEY (`university`) REFERENCES `university` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;