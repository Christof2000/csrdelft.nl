<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexMigratie extends AbstractMigration {
	public function up() {
		$this->query('ALTER TABLE accounts DROP INDEX username, ADD UNIQUE INDEX UNIQ_CAC89EACF85E0677 (username)');
		$this->query('ALTER TABLE activiteit_deelnemers DROP INDEX door_uid, ADD INDEX IDX_7F206BAB97983E4 (door_uid)');
		$this->query('ALTER TABLE activiteit_deelnemers DROP INDEX uid, ADD INDEX IDX_7F206BAB539B0606 (uid)');
		$this->query('ALTER TABLE activiteiten DROP INDEX maker_uid, ADD INDEX IDX_1C50895F3A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE besturen DROP INDEX maker_uid, ADD INDEX IDX_16921DA03A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE bestuurs_leden DROP INDEX door_uid, ADD INDEX IDX_7BE62A7897983E4 (door_uid)');
		$this->query('ALTER TABLE bestuurs_leden DROP INDEX uid, ADD INDEX IDX_7BE62A78539B0606 (uid)');
		$this->query('ALTER TABLE bewoners DROP INDEX door_uid, ADD INDEX IDX_62C7740197983E4 (door_uid)');
		$this->query('ALTER TABLE bewoners DROP INDEX uid, ADD INDEX IDX_62C77401539B0606 (uid)');
		$this->query('ALTER TABLE biebbeschrijving DROP INDEX boek_id, ADD INDEX IDX_38C782345C12AB20 (boek_id)');
		$this->query('ALTER TABLE biebbeschrijving DROP INDEX schrijver_uid, ADD INDEX IDX_38C7823495E149D3 (schrijver_uid)');
		$this->query('ALTER TABLE biebboek DROP INDEX categorie_id, ADD INDEX IDX_42475FFABCF5E72D (categorie_id)');
		$this->query('ALTER TABLE biebcategorie DROP INDEX p_id, ADD INDEX IDX_8FC553A3D37B63A2 (p_id)');
		$this->query('ALTER TABLE biebexemplaar DROP INDEX boek_id, ADD INDEX IDX_27CEE3A45C12AB20 (boek_id)');
		$this->query('ALTER TABLE biebexemplaar DROP INDEX eigenaar_uid, ADD INDEX IDX_27CEE3A452DCF440 (eigenaar_uid)');
		$this->query('ALTER TABLE biebexemplaar DROP INDEX uitgeleend_uid, ADD INDEX IDX_27CEE3A4C5564237 (uitgeleend_uid)');
		$this->query('ALTER TABLE CiviBestellingInhoud DROP INDEX FK_CBI_product, ADD INDEX IDX_30A7C75C4584665A (product_id)');
		$this->query('ALTER TABLE CiviPrijs DROP INDEX FK_CP_product, ADD INDEX IDX_86CCDFA74584665A (product_id)');
		$this->query('ALTER TABLE commissie_leden DROP INDEX door_uid, ADD INDEX IDX_18A4E69E97983E4 (door_uid)');
		$this->query('ALTER TABLE commissie_leden DROP INDEX uid, ADD INDEX IDX_18A4E69E539B0606 (uid)');
		$this->query('ALTER TABLE commissies DROP INDEX maker_uid, ADD INDEX IDX_858C850B3A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE courant DROP INDEX courant_ibfk_1, ADD INDEX IDX_CB97613A4F817B88 (verzender)');
		$this->query('ALTER TABLE courantbericht DROP INDEX uid, ADD INDEX IDX_A14C826A539B0606 (uid)');
		$this->query('ALTER TABLE crv_kwalificaties DROP INDEX functie_id, ADD INDEX IDX_A78376046B113FD2 (functie_id)');
		$this->query('ALTER TABLE crv_repetities DROP INDEX functie_id, ADD INDEX IDX_3C44567B6B113FD2 (functie_id)');
		$this->query('ALTER TABLE crv_taken DROP INDEX functie_id, ADD INDEX IDX_663841046B113FD2 (functie_id)');
		$this->query('ALTER TABLE crv_voorkeuren DROP INDEX crv_voorkeuren_ibfk_1, ADD INDEX IDX_5BDAE73AC0ADB06C (crv_repetitie_id)');
		$this->query('ALTER TABLE Document DROP INDEX  catID, ADD INDEX IDX_211FE820BCF5E72D (categorie_id)');
		$this->query('ALTER TABLE Document DROP INDEX eigenaar, ADD INDEX IDX_211FE820F725D48E (eigenaar)');
		$this->query('ALTER TABLE forum_delen DROP INDEX categorie_id, ADD INDEX IDX_4D51668FBCF5E72D (categorie_id)');
		$this->query('ALTER TABLE forum_draden DROP INDEX forum_id, ADD INDEX IDX_C7DE4CB629CCBAD0 (forum_id)');
		$this->query('ALTER TABLE forum_draden_gelezen DROP INDEX draad_id, ADD INDEX IDX_A4E3F87ECA4E5E (draad_id)');
		$this->query('ALTER TABLE forum_draden_gelezen DROP INDEX lid_id, ADD INDEX IDX_A4E3F87E539B0606 (uid)');
		$this->query('ALTER TABLE forum_posts DROP INDEX draad_id, ADD INDEX IDX_90291C2DCA4E5E (draad_id)');
		$this->query('ALTER TABLE fotos DROP INDEX owner, ADD INDEX IDX_CB8405C7CF60E67C (owner)');
		$this->query('ALTER TABLE groep_leden DROP INDEX door_uid, ADD INDEX IDX_47D1C06D97983E4 (door_uid)');
		$this->query('ALTER TABLE groep_leden DROP INDEX uid, ADD INDEX IDX_47D1C06D539B0606 (uid)');
		$this->query('ALTER TABLE groepen DROP INDEX maker_uid, ADD INDEX IDX_647C5D9F3A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE ketzer_deelnemers DROP INDEX door_uid, ADD INDEX IDX_E29708B297983E4 (door_uid)');
		$this->query('ALTER TABLE ketzer_deelnemers DROP INDEX uid, ADD INDEX IDX_E29708B2539B0606 (uid)');
		$this->query('ALTER TABLE ketzers DROP INDEX maker_uid, ADD INDEX IDX_DE2D6D463A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE kring_leden DROP INDEX door_uid, ADD INDEX IDX_C1C2953697983E4 (door_uid)');
		$this->query('ALTER TABLE kring_leden DROP INDEX uid, ADD INDEX IDX_C1C29536539B0606 (uid)');
		$this->query('ALTER TABLE kringen DROP INDEX maker_uid, ADD INDEX IDX_E2F4F0BF3A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE lichting_leden DROP INDEX door_uid, ADD INDEX IDX_9F803B1197983E4 (door_uid)');
		$this->query('ALTER TABLE lichting_leden DROP INDEX uid, ADD INDEX IDX_9F803B11539B0606 (uid)');
		$this->query('ALTER TABLE lichtingen DROP INDEX maker_uid, ADD INDEX IDX_1EDCCA6E3A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE lidinstellingen DROP INDEX uid, ADD INDEX IDX_2059B4DF539B0606 (uid)');
		$this->query('ALTER TABLE lidtoestemmingen DROP INDEX uid, ADD INDEX IDX_141F309D539B0606 (uid)');
		$this->query('ALTER TABLE login_sessions DROP INDEX uid, ADD INDEX IDX_B4C4BD8C539B0606 (uid)');
		$this->query('ALTER TABLE menus DROP INDEX pid, ADD INDEX IDX_727508CF727ACA70 (parent_id)');
		$this->query('ALTER TABLE mlt_aanmeldingen DROP INDEX door_lid, ADD INDEX IDX_B156D32F97983E4 (door_uid)');
		$this->query('ALTER TABLE mlt_maaltijden DROP INDEX FK_mlt_product, ADD INDEX IDX_20DD17274584665A (product_id)');
		$this->query('ALTER TABLE mlt_maaltijden DROP INDEX mlt_repetitie_id, ADD INDEX IDX_20DD1727A8500550 (mlt_repetitie_id)');
		$this->query('ALTER TABLE mlt_repetities DROP INDEX FK_mltrep_product, ADD INDEX IDX_5505370E4584665A (product_id)');
		$this->query('ALTER TABLE ondervereniging_leden DROP INDEX door_uid, ADD INDEX IDX_7EC7AEBA97983E4 (door_uid)');
		$this->query('ALTER TABLE ondervereniging_leden DROP INDEX uid, ADD INDEX IDX_7EC7AEBA539B0606 (uid)');
		$this->query('ALTER TABLE onderverenigingen DROP INDEX maker_uid, ADD INDEX IDX_F1E76283A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE peiling_optie DROP INDEX peilingid, ADD INDEX IDX_C919E5D7B68DF022 (peiling_id)');
		$this->query('ALTER TABLE peiling_stemmen DROP INDEX uid, ADD INDEX IDX_A5E8105539B0606 (uid)');
		$this->query('ALTER TABLE pin_transactie_match DROP INDEX bestelling_id, ADD INDEX IDX_457B997DA2E63037 (bestelling_id)');
		$this->query('ALTER TABLE pin_transactie_match DROP INDEX transactie_id, ADD INDEX IDX_457B997D1D647A75 (transactie_id)');
		$this->query('ALTER TABLE verticale_leden DROP INDEX door_uid, ADD INDEX IDX_52C85C3897983E4 (door_uid)');
		$this->query('ALTER TABLE verticale_leden DROP INDEX uid, ADD INDEX IDX_52C85C38539B0606 (uid)');
		$this->query('ALTER TABLE verticalen DROP INDEX maker_uid, ADD INDEX IDX_79C519073A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE voorkeurCommissie DROP INDEX categorie_id, ADD INDEX IDX_6567316BCF5E72D (categorie_id)');
		$this->query('ALTER TABLE voorkeurVoorkeur DROP INDEX cid, ADD INDEX IDX_1A129E324B30D9C4 (cid)');
		$this->query('ALTER TABLE werkgroep_deelnemers DROP INDEX door_uid, ADD INDEX IDX_38E14A6B97983E4 (door_uid)');
		$this->query('ALTER TABLE werkgroep_deelnemers DROP INDEX uid, ADD INDEX IDX_38E14A6B539B0606 (uid)');
		$this->query('ALTER TABLE werkgroepen DROP INDEX maker_uid, ADD INDEX IDX_2194ECF73A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE woonoorden DROP INDEX maker_uid, ADD INDEX IDX_782FB8083A4A27C1 (maker_uid)');
		$this->query('ALTER TABLE CiviBestelling DROP INDEX CiviBestelling_ibfk_1, ADD INDEX IDX_290D88AC539B0606 (uid)');
		$this->query('ALTER TABLE crv_repetities DROP INDEX mlt_repetitie_id, ADD INDEX IDX_3C44567BA8500550 (mlt_repetitie_id)');
		$this->query('ALTER TABLE fotoalbums DROP INDEX owner, ADD INDEX IDX_AC6D17BECF60E67C (owner)');
		$this->query('ALTER TABLE CiviProduct DROP INDEX FK_CP_categorie, ADD INDEX IDX_C4590238BCF5E72D (categorie_id)');
		$this->query('ALTER TABLE crv_taken DROP INDEX crv_taken_ibfk_4, ADD INDEX IDX_66384104539B0606 (uid)');
		$this->query('ALTER TABLE crv_taken DROP INDEX crv_repetitie_id, ADD INDEX IDX_66384104C0ADB06C (crv_repetitie_id)');
		$this->query('ALTER TABLE crv_taken DROP INDEX maaltijd_id, ADD INDEX IDX_66384104CBA49CBE (maaltijd_id)');
		$this->query('ALTER TABLE lichtingen DROP INDEX lidjaar, ADD UNIQUE INDEX UNIQ_1EDCCA6E4D508A76 (lidjaar)');
		$this->query('ALTER TABLE verticalen DROP INDEX letter, ADD UNIQUE INDEX UNIQ_79C519078E02EE0A (letter)');
		$this->query('ALTER TABLE verticalen DROP INDEX naam, ADD UNIQUE INDEX UNIQ_79C51907FC4DB938 (naam)');
		$this->query('ALTER TABLE mlt_aanmeldingen DROP INDEX door_abonnement, ADD INDEX IDX_B156D32F19F42197 (door_abonnement)');
		$this->query('ALTER TABLE biebboek DROP INDEX auteur_id, ADD INDEX IDX_42475FFA60BB6FE6 (auteur_id)');

		$this->query('ALTER TABLE voorkeurVoorkeur ADD INDEX IDX_1A129E32539B0606 (uid)');
		$this->query('ALTER TABLE CiviBestellingInhoud ADD INDEX IDX_30A7C75CA2E63037 (bestelling_id)');
		$this->query('ALTER TABLE login_remember ADD INDEX IDX_BD5B5182539B0606 (uid)');
		$this->query('ALTER TABLE profielen ADD INDEX IDX_301B6229742C4A98 (patroon)');
		$this->query('ALTER TABLE mlt_aanmeldingen ADD INDEX IDX_B156D32F539B0606 (uid)');
		$this->query('ALTER TABLE eetplan ADD INDEX IDX_EC97E0BBF0C31BC7 (woonoord_id)');
		$this->query('ALTER TABLE eetplan ADD INDEX IDX_EC97E0BB539B0606 (uid)');
		$this->query('ALTER TABLE forum_delen_meldingen ADD INDEX IDX_7525C01529CCBAD0 (forum_id)');
		$this->query('ALTER TABLE eetplan_bekenden ADD INDEX IDX_17EC81326AECD184 (uid1)');
		$this->query('ALTER TABLE eetplan_bekenden ADD INDEX IDX_17EC8132F3E5803E (uid2)');
		$this->query('ALTER TABLE forumplaatjes ADD INDEX IDX_1781803FC6197FB4 (maker)');
		$this->query('ALTER TABLE forum_draden_volgen ADD INDEX IDX_E660A9C9CA4E5E (draad_id)');
		$this->query('ALTER TABLE forum_draden ADD INDEX IDX_C7DE4CB6EAAB93BA (gedeeld_met)');
		$this->query('ALTER TABLE forum_draden_verbergen ADD INDEX IDX_40739E14CA4E5E (draad_id)');
		$this->query('ALTER TABLE peiling ADD INDEX IDX_A3418E46F725D48E (eigenaar)');
		$this->query('ALTER TABLE forum_draden ADD UNIQUE INDEX UNIQ_C7DE4CB6E196D02C (laatste_post_id)');

		$this->query('ALTER TABLE profielen ADD CONSTRAINT FK_301B6229742C4A98 FOREIGN KEY (patroon) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE login_remember ADD CONSTRAINT FK_BD5B5182539B0606 FOREIGN KEY (uid) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE Document ADD CONSTRAINT FK_211FE820BCF5E72D FOREIGN KEY (categorie_id) REFERENCES DocumentCategorie (id)');
		$this->query('ALTER TABLE Document ADD CONSTRAINT FK_211FE820F725D48E FOREIGN KEY (eigenaar) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE eetplan ADD CONSTRAINT FK_EC97E0BBF0C31BC7 FOREIGN KEY (woonoord_id) REFERENCES woonoorden (id)');
		$this->query('ALTER TABLE eetplan ADD CONSTRAINT FK_EC97E0BB539B0606 FOREIGN KEY (uid) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE eetplan_bekenden ADD CONSTRAINT FK_17EC81326AECD184 FOREIGN KEY (uid1) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE eetplan_bekenden ADD CONSTRAINT FK_17EC8132F3E5803E FOREIGN KEY (uid2) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE forum_delen_meldingen ADD CONSTRAINT FK_7525C01529CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum_delen (forum_id)');
		$this->query('ALTER TABLE forum_draden ADD CONSTRAINT FK_C7DE4CB629CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum_delen (forum_id)');
		$this->query('ALTER TABLE forum_draden ADD CONSTRAINT FK_C7DE4CB6EAAB93BA FOREIGN KEY (gedeeld_met) REFERENCES forum_delen (forum_id)');
		$this->query('ALTER TABLE forum_draden_gelezen ADD CONSTRAINT FK_A4E3F87E539B0606 FOREIGN KEY (uid) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE forum_draden_gelezen ADD CONSTRAINT FK_A4E3F87ECA4E5E FOREIGN KEY (draad_id) REFERENCES forum_draden (draad_id)');
		$this->query('ALTER TABLE forum_draden_volgen ADD CONSTRAINT FK_E660A9C9CA4E5E FOREIGN KEY (draad_id) REFERENCES forum_draden (draad_id)');
		$this->query('ALTER TABLE forum_draden_verbergen ADD CONSTRAINT FK_40739E14CA4E5E FOREIGN KEY (draad_id) REFERENCES forum_draden (draad_id)');
		$this->query('ALTER TABLE forum_posts ADD CONSTRAINT FK_90291C2DCA4E5E FOREIGN KEY (draad_id) REFERENCES forum_draden (draad_id)');
		$this->query('ALTER TABLE forumplaatjes ADD CONSTRAINT FK_1781803FC6197FB4 FOREIGN KEY (maker) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE courantbericht ADD CONSTRAINT FK_A14C826A539B0606 FOREIGN KEY (uid) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE mlt_aanmeldingen ADD CONSTRAINT FK_B156D32F539B0606 FOREIGN KEY (uid) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE mlt_aanmeldingen ADD CONSTRAINT FK_B156D32F97983E4 FOREIGN KEY (door_uid) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE peiling ADD CONSTRAINT FK_A3418E46F725D48E FOREIGN KEY (eigenaar) REFERENCES profielen (uid)');
		$this->query('ALTER TABLE forum_draden ADD CONSTRAINT FK_C7DE4CB6E196D02C FOREIGN KEY (laatste_post_id) REFERENCES forum_posts (post_id)');


		$this->query('DROP INDEX voornaam ON profielen');
		$this->query('DROP INDEX nickname ON profielen');
		$this->query('DROP INDEX achternaam ON profielen');
		$this->query('CREATE INDEX voornaam ON profielen (voornaam)');
		$this->query('CREATE INDEX nickname ON profielen (nickname)');
		$this->query('CREATE INDEX achternaam ON profielen (achternaam)');
		$this->query('DROP INDEX belangrijk ON forum_draden');
		$this->query('CREATE INDEX belangrijk ON forum_draden (belangrijk)');
		$this->query('DROP INDEX optie ON peiling_optie');
		$this->query('CREATE INDEX optie ON peiling_optie (titel)');
		$this->query('ALTER TABLE kringen DROP FOREIGN KEY kringen_ibfk_2');

		$this->query('ALTER TABLE pin_transacties CHANGE datetime datetime DATETIME NOT NULL');
	}

	public function down() {
		$this->query('ALTER TABLE accounts DROP INDEX UNIQ_CAC89EACF85E0677, ADD UNIQUE INDEX username (username)');
		$this->query('ALTER TABLE activiteit_deelnemers DROP INDEX IDX_7F206BAB539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE activiteit_deelnemers DROP INDEX IDX_7F206BAB97983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE activiteiten DROP INDEX IDX_1C50895F3A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE besturen DROP INDEX IDX_16921DA03A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE bestuurs_leden DROP INDEX IDX_7BE62A78539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE bestuurs_leden DROP INDEX IDX_7BE62A7897983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE bewoners DROP INDEX IDX_62C77401539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE bewoners DROP INDEX IDX_62C7740197983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE biebbeschrijving DROP INDEX IDX_38C782345C12AB20, ADD INDEX boek_id (boek_id)');
		$this->query('ALTER TABLE biebbeschrijving DROP INDEX IDX_38C7823495E149D3, ADD INDEX schrijver_uid (schrijver_uid)');
		$this->query('ALTER TABLE biebboek DROP INDEX IDX_42475FFABCF5E72D, ADD INDEX categorie_id (categorie_id)');
		$this->query('ALTER TABLE biebcategorie DROP INDEX IDX_8FC553A3D37B63A2, ADD INDEX p_id (p_id)');
		$this->query('ALTER TABLE biebexemplaar DROP INDEX IDX_27CEE3A452DCF440, ADD INDEX eigenaar_uid (eigenaar_uid)');
		$this->query('ALTER TABLE biebexemplaar DROP INDEX IDX_27CEE3A45C12AB20, ADD INDEX boek_id (boek_id)');
		$this->query('ALTER TABLE biebexemplaar DROP INDEX IDX_27CEE3A4C5564237, ADD INDEX uitgeleend_uid (uitgeleend_uid)');
		$this->query('ALTER TABLE CiviBestellingInhoud DROP INDEX IDX_30A7C75C4584665A, ADD INDEX FK_CBI_product (product_id)');
		$this->query('ALTER TABLE CiviPrijs DROP INDEX IDX_86CCDFA74584665A, ADD INDEX FK_CP_product (product_id)');
		$this->query('ALTER TABLE commissie_leden DROP INDEX IDX_18A4E69E539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE commissie_leden DROP INDEX IDX_18A4E69E97983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE commissies DROP INDEX IDX_858C850B3A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE courant DROP INDEX IDX_CB97613A4F817B88, ADD INDEX courant_ibfk_1 (verzender)');
		$this->query('ALTER TABLE courantbericht DROP INDEX IDX_A14C826A539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE crv_kwalificaties DROP INDEX IDX_A78376046B113FD2, ADD INDEX functie_id (functie_id)');
		$this->query('ALTER TABLE crv_repetities DROP INDEX IDX_3C44567B6B113FD2, ADD INDEX functie_id (functie_id)');
		$this->query('ALTER TABLE crv_taken DROP INDEX IDX_663841046B113FD2, ADD INDEX functie_id (functie_id)');
		$this->query('ALTER TABLE crv_voorkeuren DROP INDEX IDX_5BDAE73AC0ADB06C, ADD INDEX crv_voorkeuren_ibfk_1 (crv_repetitie_id)');
		$this->query('ALTER TABLE Document DROP INDEX IDX_211FE820BCF5E72D, ADD INDEX catID (categorie_id)');
		$this->query('ALTER TABLE Document DROP INDEX IDX_211FE820F725D48E, ADD INDEX eigenaar (eigenaar)');
		$this->query('ALTER TABLE forum_delen DROP INDEX IDX_4D51668FBCF5E72D, ADD INDEX categorie_id (categorie_id)');
		$this->query('ALTER TABLE forum_draden DROP INDEX IDX_C7DE4CB629CCBAD0, ADD INDEX forum_id (forum_id)');
		$this->query('ALTER TABLE forum_draden_gelezen DROP INDEX IDX_A4E3F87E539B0606, ADD INDEX lid_id (uid)');
		$this->query('ALTER TABLE forum_draden_gelezen DROP INDEX IDX_A4E3F87ECA4E5E, ADD INDEX draad_id (draad_id)');
		$this->query('ALTER TABLE forum_posts DROP INDEX IDX_90291C2DCA4E5E, ADD INDEX draad_id (draad_id)');
		$this->query('ALTER TABLE fotos DROP INDEX IDX_CB8405C7CF60E67C, ADD INDEX owner (owner)');
		$this->query('ALTER TABLE groep_leden DROP INDEX IDX_47D1C06D539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE groep_leden DROP INDEX IDX_47D1C06D97983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE groepen DROP INDEX IDX_647C5D9F3A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE ketzer_deelnemers DROP INDEX IDX_E29708B2539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE ketzer_deelnemers DROP INDEX IDX_E29708B297983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE ketzers DROP INDEX IDX_DE2D6D463A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE kring_leden DROP INDEX IDX_C1C29536539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE kring_leden DROP INDEX IDX_C1C2953697983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE kringen DROP INDEX IDX_E2F4F0BF3A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE lichting_leden DROP INDEX IDX_9F803B11539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE lichting_leden DROP INDEX IDX_9F803B1197983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE lichtingen DROP INDEX IDX_1EDCCA6E3A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE lidinstellingen DROP INDEX IDX_2059B4DF539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE lidtoestemmingen DROP INDEX IDX_141F309D539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE login_sessions DROP INDEX IDX_B4C4BD8C539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE menus DROP INDEX IDX_727508CF727ACA70, ADD INDEX pid (parent_id)');
		$this->query('ALTER TABLE mlt_aanmeldingen DROP INDEX IDX_B156D32F97983E4, ADD INDEX door_lid (door_uid)');
		$this->query('ALTER TABLE mlt_maaltijden DROP INDEX IDX_20DD17274584665A, ADD INDEX FK_mlt_product (product_id)');
		$this->query('ALTER TABLE mlt_maaltijden DROP INDEX IDX_20DD1727A8500550, ADD INDEX mlt_repetitie_id (mlt_repetitie_id)');
		$this->query('ALTER TABLE mlt_repetities DROP INDEX IDX_5505370E4584665A, ADD INDEX FK_mltrep_product (product_id)');
		$this->query('ALTER TABLE ondervereniging_leden DROP INDEX IDX_7EC7AEBA539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE ondervereniging_leden DROP INDEX IDX_7EC7AEBA97983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE onderverenigingen DROP INDEX IDX_F1E76283A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE peiling_optie DROP INDEX IDX_C919E5D7B68DF022, ADD INDEX peilingid (peiling_id)');
		$this->query('ALTER TABLE peiling_stemmen DROP INDEX IDX_A5E8105539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE pin_transactie_match DROP INDEX IDX_457B997D1D647A75, ADD INDEX transactie_id (transactie_id)');
		$this->query('ALTER TABLE pin_transactie_match DROP INDEX IDX_457B997DA2E63037, ADD INDEX bestelling_id (bestelling_id)');
		$this->query('ALTER TABLE verticale_leden DROP INDEX IDX_52C85C38539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE verticale_leden DROP INDEX IDX_52C85C3897983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE verticalen DROP INDEX IDX_79C519073A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE voorkeurCommissie DROP INDEX IDX_6567316BCF5E72D, ADD INDEX categorie_id (categorie_id)');
		$this->query('ALTER TABLE voorkeurVoorkeur DROP INDEX IDX_1A129E324B30D9C4, ADD INDEX cid (cid)');
		$this->query('ALTER TABLE werkgroep_deelnemers DROP INDEX IDX_38E14A6B539B0606, ADD INDEX uid (uid)');
		$this->query('ALTER TABLE werkgroep_deelnemers DROP INDEX IDX_38E14A6B97983E4, ADD INDEX door_uid (door_uid)');
		$this->query('ALTER TABLE werkgroepen DROP INDEX IDX_2194ECF73A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE woonoorden DROP INDEX IDX_782FB8083A4A27C1, ADD INDEX maker_uid (maker_uid)');
		$this->query('ALTER TABLE civibestelling DROP INDEX IDX_290D88AC539B0606, ADD INDEX CiviBestelling_ibfk_1 (uid)');
		$this->query('ALTER TABLE crv_repetities DROP INDEX IDX_3C44567BA8500550, ADD INDEX mlt_repetitie_id (mlt_repetitie_id)');
		$this->query('ALTER TABLE fotoalbums DROP INDEX IDX_AC6D17BECF60E67C, ADD INDEX owner (owner)');
		$this->query('ALTER TABLE CiviProduct DROP INDEX IDX_C4590238BCF5E72D, ADD INDEX FK_CP_categorie (categorie_id)');
		$this->query('ALTER TABLE crv_taken DROP INDEX IDX_66384104539B0606, ADD INDEX crv_taken_ibfk_4 (uid)');
		$this->query('ALTER TABLE crv_taken DROP INDEX IDX_66384104C0ADB06C, ADD INDEX crv_repetitie_id (crv_repetitie_id)');
		$this->query('ALTER TABLE crv_taken DROP INDEX IDX_66384104CBA49CBE, ADD INDEX maaltijd_id (maaltijd_id)');
		$this->query('ALTER TABLE lichtingen DROP INDEX UNIQ_1EDCCA6E4D508A76, ADD UNIQUE INDEX lidjaar (lidjaar)');
		$this->query('ALTER TABLE verticalen DROP INDEX UNIQ_79C519078E02EE0A, ADD UNIQUE INDEX letter (letter)');
		$this->query('ALTER TABLE verticalen DROP INDEX UNIQ_79C51907FC4DB938, ADD UNIQUE INDEX naam (naam)');
		$this->query('ALTER TABLE mlt_aanmeldingen DROP INDEX IDX_B156D32F19F42197, ADD INDEX door_abonnement (door_abonnement)');
		$this->query('ALTER TABLE biebboek DROP INDEX IDX_42475FFA60BB6FE6, ADD INDEX auteur_id (auteur_id)');

		$this->query('ALTER TABLE profielen DROP FOREIGN KEY FK_301B6229742C4A98');
		$this->query('ALTER TABLE login_remember DROP FOREIGN KEY FK_BD5B5182539B0606');
		$this->query('ALTER TABLE Document DROP FOREIGN KEY FK_211FE820BCF5E72D');
		$this->query('ALTER TABLE Document DROP FOREIGN KEY FK_211FE820F725D48E');
		$this->query('ALTER TABLE eetplan DROP FOREIGN KEY FK_EC97E0BBF0C31BC7');
		$this->query('ALTER TABLE eetplan DROP FOREIGN KEY FK_EC97E0BB539B0606');
		$this->query('ALTER TABLE eetplan_bekenden DROP FOREIGN KEY FK_17EC81326AECD184');
		$this->query('ALTER TABLE eetplan_bekenden DROP FOREIGN KEY FK_17EC8132F3E5803E');
		$this->query('ALTER TABLE forum_delen_meldingen DROP FOREIGN KEY FK_7525C01529CCBAD0');
		$this->query('ALTER TABLE forum_draden DROP FOREIGN KEY FK_C7DE4CB629CCBAD0');
		$this->query('ALTER TABLE forum_draden DROP FOREIGN KEY FK_C7DE4CB6EAAB93BA');
		$this->query('ALTER TABLE forum_draden_gelezen DROP FOREIGN KEY FK_A4E3F87E539B0606');
		$this->query('ALTER TABLE forum_draden_gelezen DROP FOREIGN KEY FK_A4E3F87ECA4E5E');
		$this->query('ALTER TABLE forum_draden_verbergen DROP FOREIGN KEY FK_40739E14CA4E5E');
		$this->query('ALTER TABLE forum_draden_volgen DROP FOREIGN KEY FK_E660A9C9CA4E5E');
		$this->query('ALTER TABLE forum_posts DROP FOREIGN KEY FK_90291C2DCA4E5E');
		$this->query('ALTER TABLE forumplaatjes DROP FOREIGN KEY FK_1781803FC6197FB4');
		$this->query('ALTER TABLE courantbericht DROP FOREIGN KEY FK_A14C826A539B0606');
		$this->query('ALTER TABLE mlt_aanmeldingen DROP FOREIGN KEY FK_B156D32F539B0606');
		$this->query('ALTER TABLE mlt_aanmeldingen DROP FOREIGN KEY FK_B156D32F97983E4');
		$this->query('ALTER TABLE peiling DROP FOREIGN KEY FK_A3418E46F725D48E');
		$this->query('ALTER TABLE forum_draden DROP FOREIGN KEY FK_C7DE4CB6E196D02C');

		$this->query('ALTER TABLE voorkeurVoorkeur DROP INDEX IDX_1A129E32539B0606');
		$this->query('ALTER TABLE CiviBestellingInhoud DROP INDEX IDX_30A7C75CA2E63037');
		$this->query('ALTER TABLE login_remember DROP INDEX IDX_BD5B5182539B0606');
		$this->query('ALTER TABLE profielen DROP INDEX IDX_301B6229742C4A98');
		$this->query('ALTER TABLE mlt_aanmeldingen DROP INDEX IDX_B156D32F539B0606');
		$this->query('ALTER TABLE eetplan DROP INDEX IDX_EC97E0BBF0C31BC7');
		$this->query('ALTER TABLE eetplan DROP INDEX IDX_EC97E0BB539B0606');
		$this->query('ALTER TABLE forum_delen_meldingen DROP INDEX IDX_7525C01529CCBAD0');
		$this->query('ALTER TABLE eetplan_bekenden DROP INDEX IDX_17EC81326AECD184');
		$this->query('ALTER TABLE eetplan_bekenden DROP INDEX IDX_17EC8132F3E5803E');
		$this->query('ALTER TABLE forumplaatjes DROP INDEX IDX_1781803FC6197FB4');
		$this->query('ALTER TABLE forum_draden_volgen DROP INDEX IDX_E660A9C9CA4E5E');
		$this->query('ALTER TABLE forum_draden DROP INDEX IDX_C7DE4CB6EAAB93BA');
		$this->query('ALTER TABLE forum_draden_verbergen DROP INDEX IDX_40739E14CA4E5E');
		$this->query('ALTER TABLE peiling DROP INDEX IDX_A3418E46F725D48E');
		$this->query('ALTER TABLE forum_draden DROP INDEX UNIQ_C7DE4CB6E196D02C');

		$this->query('DROP INDEX voornaam ON profielen');
		$this->query('DROP INDEX achternaam ON profielen');
		$this->query('DROP INDEX nickname ON profielen');
		$this->query('CREATE INDEX voornaam ON profielen (voornaam(191))');
		$this->query('CREATE INDEX achternaam ON profielen (achternaam(191))');
		$this->query('CREATE INDEX nickname ON profielen (nickname(191))');
		$this->query('DROP INDEX belangrijk ON forum_draden');
		$this->query('CREATE INDEX belangrijk ON forum_draden (belangrijk(191))');
		$this->query('DROP INDEX optie ON peiling_optie');
		$this->query('CREATE INDEX optie ON peiling_optie (titel(191))');
		$this->query('ALTER TABLE kringen ADD CONSTRAINT kringen_ibfk_2 FOREIGN KEY (verticale) REFERENCES verticalen (letter)');

		$this->query('ALTER TABLE pin_transacties CHANGE datetime datetime VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`');
	}
}