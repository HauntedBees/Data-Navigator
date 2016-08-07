CREATE TABLE test_pokemontypes (
	cID BIGINT(20) NOT NULL AUTO_INCREMENT,
	sName VARCHAR(20) NOT NULL,
	UNIQUE KEY cID (cID)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 AUTO_INCREMENT=6;
INSERT INTO test_pokemontypes VALUES (1, 'Grass'), (2, 'Poison'), (3, 'Fire'), (4, 'Water'), (5, 'Electric');

CREATE TABLE test_pokemonegggroups (
	cID BIGINT(20) NOT NULL AUTO_INCREMENT,
	sName VARCHAR(20) NOT NULL,
	UNIQUE KEY cID (cID)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 AUTO_INCREMENT=7;
INSERT INTO test_pokemonegggroups VALUES (1, 'Monster'), (2, 'Grass'), (3, 'Water 1'), (4, 'Dragon'), (5, 'Field');

CREATE TABLE test_pokemon (
	cId BIGINT(20) NOT NULL AUTO_INCREMENT,
	sName VARCHAR(100) NOT NULL,
	iGeneration INT(11) NOT NULL,
	iStage INT(11) NOT NULL,
	cEvolvesFrom BIGINT(20) DEFAULT NULL,
	cEvolvesInto BIGINT(20) DEFAULT NULL,
	iBodyType INT(11) NOT NULL,
	iBaseExperience INT(11) NOT NULL,
	iEVhp INT(11) NOT NULL,
	iEVatk INT(11) NOT NULL,
	iEVdef INT(11) NOT NULL,
	iEVspatk INT(11) NOT NULL,
	iEVspdef INT(11) NOT NULL,
	iEVspd INT(11) NOT NULL,
	iBaseFriendship INT(11) NOT NULL,
	sDesc text NOT NULL,
	PRIMARY KEY (cId)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;
INSERT INTO test_pokemon VALUES (1, 'Bulbasaur', 1, 1, NULL, 2, 5, 64, 0, 0, 0, 1, 0, 0, 70, 'Bulbasaur can be seen napping in bright sunlight. There is a seed on its back. By soaking up the sun%E2%80%99s rays, the seed grows progressively larger.'), 
								(2, 'Ivysaur', 1, 2, 1, 3, 5, 142, 0, 0, 0, 1, 1, 0, 70, 'There is a bud on this Pok%C3%A9mon%E2%80%99s back. To support its weight, Ivysaur%E2%80%99s legs and trunk grow thick and strong. If it starts spending more time lying in the sunlight, it%E2%80%99s a sign that the bud will bloom into a large flower soon.'), 
								(3, 'Venusaur', 1, 3, 2, NULL, 5, 236, 0, 0, 0, 2, 1, 0, 70, 'There is a large flower on Venusaur%E2%80%99s back. The flower is said to take on vivid colors if it gets plenty of nutrition and sunlight. The flower%E2%80%99s aroma soothes the emotions of people.'), 
								(4, 'Squirtle', 1, 1, NULL, 5, 10, 63, 0, 0, 1, 0, 0, 0, 70, 'Squirtle%E2%80%99s shell is not merely used for protection. The shell%E2%80%99s rounded shape and the grooves on its surface help minimize resistance in water, enabling this Pok%C3%A9mon to swim at high speeds.'), 
								(5, 'Wartortle', 1, 2, 4, 6, 10, 142, 0, 0, 1, 0, 1, 0, 70, 'Its tail is large and covered with a rich, thick fur. The tail becomes increasingly deeper in color as Wartortle ages. The scratches on its shell are evidence of this Pok%C3%A9mon%E2%80%99s toughness as a battler.'), 
								(6, 'Blastoise', 1, 3, 5, NULL, 10, 239, 0, 0, 0, 0, 3, 0, 70, 'Blastoise has water spouts that protrude from its shell. The water spouts are very accurate. They can shoot bullets of water with enough accuracy to strike empty cans from a distance of over 160 feet.'), 
								(7, 'Charmander', 1, 1, NULL, 8, 10, 62, 0, 0, 0, 0, 0, 1, 70, 'The flame that burns at the tip of its tail is an indication of its emotions. The flame wavers when Charmander is enjoying itself. If the Pok%C3%A9mon becomes enraged, the flame burns fiercely.'), 
								(8, 'Charmeleon', 1, 2, 7, 9, 10, 142, 0, 0, 0, 1, 0, 1, 70, 'Charmeleon mercilessly destroys its foes using its sharp claws. If it encounters a strong foe, it turns aggressive. In this excited state, the flame at the tip of its tail flares with a bluish white color.'), 
								(9, 'Charizard', 1, 3, 8, NULL, 10, 240, 0, 0, 0, 3, 0, 0, 70, 'Charizard flies around the sky in search of powerful opponents. It breathes fire of such great heat that it melts anything. However, it never turns its fiery breath on any opponent weaker than itself.'), 
								(10, 'Pikachu', 1, 1, 12, 11, 5, 105, 0, 0, 0, 0, 0, 2, 70, 'Whenever Pikachu comes across something new, it blasts it with a jolt of electricity. If you come across a blackened berry, it%E2%80%99s evidence that this Pok%C3%A9mon mistook the intensity of its charge.'), 
								(11, 'Raichu', 1, 2, 10, NULL, 10, 214, 0, 0, 0, 0, 0, 3, 70, 'If the electrical sacs become excessively charged, Raichu plants its tail in the ground and discharges. Scorched patches of ground will be found near this Pok%C3%A9mon%E2%80%99s nest.'), 
								(12, 'Pichu', 2, 0, NULL, 10, 5, 41, 0, 0, 0, 0, 0, 1, 70, 'Pichu charges itself with electricity more easily on days with thunderclouds or when the air is very dry. You can hear the crackling of static electricity coming off this Pok%C3%A9mon.');

CREATE TABLE test_pokemoneggxref (
	cPokemon BIGINT(20) NOT NULL,
	cEgg BIGINT(20) NOT NULL,
	KEY cPokemon (cPokemon), KEY cEgg (cEgg)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO test_pokemoneggxref VALUES (1, 1), (1, 2), (2, 1), (2, 2), (3, 1), (3, 2), (4, 1), (4, 3), (5, 1), (5, 3), (6, 1), (6, 3), (7, 1), (7, 4), (8, 1), (8, 4), (9, 1), (9, 4), (10, 5), (10, 6), (11, 5), (11, 6), (12, 5), (12, 6);

CREATE TABLE test_pokemontypexref (
	cPokemon BIGINT(20) NOT NULL,
	cType BIGINT(20) NOT NULL,
	KEY cPokemon (cPokemon), KEY cType (cType)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO test_pokemontypexref VALUES (1, 1), VALUES (1, 2), VALUES (2, 1), VALUES (2, 2), VALUES (3, 1), VALUES (3, 2), VALUES (4, 4), VALUES (5, 4), VALUES (6, 4), VALUES (7, 3), VALUES (8, 3), VALUES (9, 3), VALUES (10, 5), VALUES (11, 5), VALUES (12, 5);