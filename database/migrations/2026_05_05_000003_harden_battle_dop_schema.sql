ALTER TABLE battle_dop
  ADD PRIMARY KEY (id),
  ADD KEY idx_battle_dop_battle_poke (battleid, pokeid);
